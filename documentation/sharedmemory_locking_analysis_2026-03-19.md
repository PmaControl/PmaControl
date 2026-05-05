# Analyse complete du verrouillage `SharedMemory` / `StorageFile`

Date: 2026-03-19

## Objet

Verifier si `Fuz\Component\SharedMemory\Storage\StorageFile` et `Fuz\Component\SharedMemory\SharedMemory`:

- attendent correctement un verrou avant ecriture,
- peuvent ecraser les donnees faute d'attente,
- ou si le vrai probleme vient d'une contention excessive sur un meme fichier pivot.

Le focus est le chemin utilise par PmaControl dans:

- `App/Controller/Aspirateur.php`
- `App/Controller/Integrate.php`
- `vendor/ninsuo/php-shared-memory/src/Fuz/Component/SharedMemory/SharedMemory.php`
- `vendor/ninsuo/php-shared-memory/src/Fuz/Component/SharedMemory/Storage/StorageFile.php`

## Resume executif

Conclusion courte:

1. `StorageFile` attend deja le verrou d'ecriture.
2. Il n'y a pas de `LOCK_NB`, donc `flock(..., LOCK_EX)` est bloquant par defaut.
3. Le probleme n'est donc pas "on ecrase parce qu'on n'attend pas".
4. Le vrai risque est la contention: beaucoup de processus serialisent leurs ecritures sur le meme fichier pivot.
5. Chaque ecriture relit et reecrit l'objet serialise complet, ce qui augmente le temps de detention du verrou a mesure que le fichier grossit.
6. `SharedMemory::lock()` existe bien, mais ce n'est pas ce mecanisme que PmaControl utilise ici. PmaControl s'appuie surtout sur le verrou OS de `flock`.

En pratique:

- oui, il y a deja un mecanisme d'attente,
- non, il ne protege pas d'une surcharge structurelle du fichier,
- oui, l'architecture actuelle peut devenir lente ou produire du decalage temporel si trop de workers ciblent le meme fichier.

## Ce que fait exactement `StorageFile`

### Lecture

`StorageFile::openReader()`:

- ouvre le fichier en `c+`
- prend un verrou partage `LOCK_SH`

Code cle:

```php
$fd = fopen($this->file, 'c+');
flock($fd, LOCK_SH);
```

### Ecriture

`StorageFile::openWriter()`:

- ouvre le fichier en `c+`
- prend un verrou exclusif `LOCK_EX`

Code cle:

```php
$fd = fopen($this->file, 'c+');
flock($fd, LOCK_EX);
```

Point important:

- il n'y a pas de `LOCK_NB`
- donc l'appel bloque jusqu'a obtention du verrou

Autrement dit, le processus attend deja.

### Reecriture complete du contenu

`StorageFile::setObject()`:

```php
rewind($this->fd);
ftruncate($this->fd, 0);
fwrite($this->fd, serialize($object));
```

Donc chaque ecriture:

1. reprend l'objet courant,
2. tronque le fichier,
3. reecrit l'objet entier serialize.

Il ne s'agit pas d'un append.

## Ce que fait exactement `SharedMemory`

### Ecriture d'une propriete

`SharedMemory::set()` fait:

1. `openWriter()`
2. `getObjectSafely()`
3. lecture de l'objet courant
4. modification d'une propriete
5. `setObject()`
6. `close()`

Code cle:

```php
$this->storage->openWriter();
$object = $this->getObjectSafely('openWriter');
$data = $object->getData();
$data->{$property} = $value;
$object->setData($data);
$this->storage->setObject($object);
$this->storage->close();
```

Implication:

- tant que plusieurs processus ecrivent sur des proprietes differentes du meme objet, la fusion fonctionne correctement,
- a condition qu'ils passent tous par ce meme chemin verrouille.

## Le verrou "metier" `SharedMemory::lock()`

La librairie a un second mecanisme:

- `lock($timeout, $interval)`
- `unlock()`

Ce verrou n'est pas le verrou OS `flock`.
Il ajoute un etat `locked=true` dans l'objet serialize, puis les autres processus bouclent avec `usleep()` tant que cet etat est actif.

Code cle:

```php
while ($object->isLocked()) {
    $this->storage->close();
    usleep($object->getInterval());
    $this->storage->{$openCallback}();
    $object = $this->getObject();
}
```

Ce mecanisme sert a proteger des sequences de type:

- lire plusieurs champs,
- faire un calcul,
- reecrire ensuite.

Il n'est utile que si l'appelant l'utilise explicitement.

## Ce que PmaControl utilise reellement

Dans `Aspirateur::allocate_shared_storage()`:

```php
$shared_file = EngineV4::PATH_PIVOT_FILE.time().$separator.$name;
$storage = new StorageFile($shared_file);
$SHARED_MEMORY = new SharedMemory($storage);
```

Dans `Aspirateur::exportData()`:

```php
$tmp[$ts][$id_mysql_server] = $data;
$memory = $this->allocate_shared_storage($ts_file, $separator);
$memory->{$id_mysql_server} = $tmp;
```

Donc PmaControl:

- ouvre un fichier pivot par `time()` et par `ts_file`,
- ecrit une propriete nommee par `id_mysql_server`,
- s'appuie sur `SharedMemory::set()`,
- mais n'appelle pas `lock()` / `unlock()`.

Cela signifie:

- oui, chaque ecriture attend bien le `LOCK_EX`,
- non, il n'y a pas de mutex metier supplementaire autour d'un lot de mises a jour.

## Tests realises

### Test 1: `LOCK_EX` est bien bloquant

Test local execute:

- un process tient un `LOCK_EX` pendant environ `0.7s`
- un second process tente d'ecrire via `SharedMemory`

Resultat:

- l'ecriture attend bien la liberation du verrou
- mesure observee: `elapsed=0.701`

Conclusion:

- il n'y a pas d'ecriture "sans attendre"
- le verrou fichier fonctionne comme attendu

### Test 2: deux processus ecrivant deux proprietes differentes fusionnent correctement

Test local execute:

- processus A ecrit la propriete `88`
- processus B ecrit la propriete `96`
- les deux ciblent le meme fichier partage

Resultat final:

```text
88,96
```

Conclusion:

- avec ce chemin d'acces, les ecritures concurrentes sur des proprietes differentes ne s'ecrasent pas entre elles

### Test 3: le verrou metier `lock()` fonctionne, mais il est distinct du verrou fichier

Test local execute:

- un premier `SharedMemory` pose `lock(1, 5000)`
- un second tente de lire

Resultat:

```text
Can't access shared object, it is still locked after 1 second(s).
elapsed=1.03
```

Conclusion:

- `SharedMemory::lock()` met bien en place une attente active,
- mais ce mecanisme n'est pas utilise par `Aspirateur::exportData()`

## Reponse a la question "on devrait patienter et non surcharge le fichier"

La reponse precise est:

- patienter: c'est deja le cas au niveau du verrou OS,
- surcharger le fichier: oui, c'est toujours possible structurellement.

Le verrou ne supprime pas la surcharge, il la serialize.

Si 20 ou 30 workers visent le meme fichier:

1. chacun attend son tour,
2. chacun relit l'objet complet,
3. chacun reecrit l'objet complet,
4. le temps total augmente en file d'attente.

Donc le systeme ne perd pas forcement la coherence du fichier, mais il peut perdre en debit.

## Le vrai point faible de l'architecture actuelle

Le point faible n'est pas l'absence d'attente.
Le point faible est le couple:

- fichier pivot partage par seconde,
- read/modify/write de l'objet complet a chaque set.

Effets directs:

1. Un seul writer a la fois par fichier.
2. Plus le payload grossit, plus chaque `serialize()` / `fwrite()` coute cher.
3. Tous les workers qui visent le meme `time()::mysql_server` passent en file.
4. La latence peut devenir tres differente d'un serveur a l'autre selon l'instant exact ou son worker arrive dans cette file.

## Impact probable sur `mysql_available`

Sur `mysql_available`, le chemin est:

- `tryMysqlConnection()`
- `setService(..., 'mysql')`
- `exportData($id_mysql_server, 'mysql_server', ...)`

Donc toute la cadence de `mysql_available` passe par le fichier pivot `...::mysql_server`.

Si beaucoup de workers ecrivent en meme temps:

- les ecritures ne s'ecrasent pas necessairement,
- mais elles peuvent se decaler,
- et la cadence reelle observee dans l'historique peut devenir irreguliere.

Autrement dit:

- l'attente existe deja,
- mais elle transforme la concurrence en goulot d'etranglement.

## Pourquoi ajouter `SharedMemory::lock()` n'est probablement pas la bonne correction

Ce serait meme souvent pire.

Pourquoi:

1. `set()` prend deja un `LOCK_EX`.
2. `lock()` ajoute une seconde logique de blocage en boucle.
3. Cela allongerait encore la duree de detention logique.
4. Cela ne reduit pas le volume de donnees reecrit.
5. Cela ne supprime pas le hot spot sur `time()::mysql_server`.

Donc:

- oui, `lock()` est utile pour des transactions multi-etapes,
- non, ce n'est pas le bon outil pour des ecritures unitaires tres frequentes.

## Proposition de resolution

### Option A: shard du fichier pivot par serveur

Exemple:

- aujourd'hui: `tmp_file/<timestamp>::mysql_server`
- propose: `tmp_file/<timestamp>::mysql_server::<id_mysql_server>`

Avantages:

- quasi plus de contention inter-serveurs,
- fichiers plus petits,
- serialize/fwrite beaucoup moins couteux,
- pas besoin de mutex metier supplementaire

Inconvenient:

- `Integrate` doit lire plus de fichiers

C'est le meilleur compromis si on veut garder le mecanisme actuel.

### Option B: shard par worker

Exemple:

- `tmp_file/<timestamp>::mysql_server::worker_<pid>`

Avantages:

- reduction nette de contention

Inconvenient:

- toujours des objets multi-serveurs assez gros

Moins propre que le shard par serveur.

### Option C: format append-only

Au lieu de reecrire un objet serialize complet:

- ajouter une ligne par evenement,
- puis laisser `Integrate` rejouer le journal

Avantages:

- moins de read-modify-write,
- moins de cout par ecriture

Inconvenients:

- demande une refonte plus profonde,
- gestion du compactage a prevoir

### Option D: batch local avant flush

Chaque worker accumule localement puis flush toutes les X secondes.

Avantage:

- moins d'ecritures disque

Inconvenient:

- plus de complexite,
- risque de perte du batch en cas de crash

## Recommandation

Recommandation principale:

1. Ne pas modifier `StorageFile` pour "attendre plus".
2. Garder `flock(LOCK_EX)` bloquant tel quel.
3. Reduire la contention en changeant la granularite du fichier pivot.

Ordre recommande:

1. shard par serveur sur `tmp_file/<timestamp>::mysql_server::<id_mysql_server>`
2. adapter `Integrate` pour fusionner ces fichiers
3. ajouter des mesures de temps d'attente sur verrou et de taille de payload

## Instrumentation recommandee

Ajouter dans `Aspirateur::exportData()`:

- duree d'attente avant obtention du verrou
- taille du payload serialize
- temps total de `setObject()`
- nom du fichier pivot cible

Ajouter dans `Integrate`:

- temps de lecture par fichier
- nombre d'entrees integrees par fichier

Cela permettra de distinguer clairement:

- probleme de lock,
- probleme de volume,
- probleme de cadence worker.

## Conclusion finale

Le mecanisme d'attente existe deja.

Le probleme n'est pas l'absence de verrou bloquant.
Le probleme est que le verrou bloque correctement, mais sur un fichier trop partage et reecrit integralement a chaque mise a jour.

Donc:

- inutile de "faire patienter davantage" dans `StorageFile`,
- utile de reduire la contention,
- utile de sharder les fichiers pivots ou changer le format d'ecriture.

