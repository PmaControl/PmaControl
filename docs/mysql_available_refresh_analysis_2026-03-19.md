# Analyse `mysql_available` trop lent sur certains serveurs

Date: 2026-03-19

## Contexte

Le daemon métier attendu est:

- `daemon_main.id = 20`
- nom: `Aspirateur MySQL`
- `refresh_time = 5`

La promesse fonctionnelle est donc:

- 1 mesure `mysql_available` toutes les 5 secondes par serveur monitoré
- soit `3600 / 5 = 720` points par heure et par serveur quand tout va bien

Le problème signalé est une fréquence très inégale selon les serveurs.

Exemples fournis:

- serveur `88`: trop lent
- serveur `96`: cadence normale

## Périmètre analysé

Chaîne technique inspectée:

1. `Worker::addToQueue()` dans [App/Controller/Worker.php](/srv/www/pmacontrol/App/Controller/Worker.php)
2. `Worker::run()` dans [App/Controller/Worker.php](/srv/www/pmacontrol/App/Controller/Worker.php)
3. `Aspirateur::tryMysqlConnection()` dans [App/Controller/Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php)
4. `Aspirateur::setService()` / `exportData()` / `allocate_shared_storage()` dans [App/Controller/Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php)
5. `Integrate::evaluate()` dans [App/Controller/Integrate.php](/srv/www/pmacontrol/App/Controller/Integrate.php)
6. tables `worker_execution`, `ts_value_general_int`, `ts_date_by_server`

## Mesures réalisées

### Configuration réelle

- serveurs MySQL monitorés actifs: `85`
- workers MySQL actifs (`worker_queue.id = 1`): `27`
- refresh daemon MySQL: `5s`
- intégration: `daemon_main.id = 7`, `refresh_time = 1s`

### Résultat observé sur 1 heure

Nombre de points `mysql_available` (`ts_variable.id = 3905`):

- serveur `88` (`Slave_10-3`): `252`
- serveur `96` (`mariadb-10-8`): `720`

Statistiques d’écart entre deux points sur 1 heure:

- serveur `88`
  - `avg_gap = 15.08s`
  - `max_gap = 70s`
  - `118` gaps `> 10s`
  - `17` gaps `> 30s`
- serveur `96`
  - `avg_gap = 5.00s`
  - `max_gap = 6s`
  - `0` gap `> 10s`

### Vérification du scheduler Worker

Sur `worker_execution`, en filtrant bien la file MySQL (`worker_queue.id = 1`):

- serveur `88`: `720` exécutions sur 1 heure
- serveur `96`: `720` exécutions sur 1 heure

Temps d’exécution moyen:

- serveur `88`: `9.0 ms`
- serveur `96`: `15.6 ms`

Conclusion:

- le problème n’est pas dans le scheduling du worker
- le problème n’est pas une lenteur réseau MySQL sur `88`
- les deux serveurs sont bien exécutés toutes les 5 secondes

### Test direct `tryMysqlConnection`

Commande lancée:

- `./glial Aspirateur tryMysqlConnection server_6887c76cbbdce 88 5 --debug`
- `./glial Aspirateur tryMysqlConnection server_68b042acc7857 96 5 --debug`

Résultats de connexion observés:

- serveur `88`: `mysql_ping ~ 0.00418 s`
- serveur `96`: `mysql_ping ~ 0.00138 s`

Les deux connexions passent.

## Fait déterminant

Le point critique est ici:

- [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php): `allocate_shared_storage()`

Code actuel:

```php
$shared_file = EngineV4::PATH_PIVOT_FILE.time().$separator.$name;
```

Le nom du fichier pivot n’a qu’une précision à la seconde.

Or `daemon 20` lance des workers toutes les 5 secondes, avec `27` workers pour `85` serveurs.
Donc pendant une même seconde, plusieurs workers écrivent dans le même fichier:

- `tmp/tmp_file/<timestamp>::mysql_server`

### Preuve directe

Lecture d’un fichier brut récent:

- `tmp/tmp_file/1773889128::mysql_server`

Contenu observé: seulement `4` serveurs dans le fichier.

Ce fichier devrait contenir beaucoup plus de serveurs sur la seconde en cours.

### Preuve côté intégration

Sur la dernière heure, pour `ts_date_by_server` sur `mysql_server`:

- `39927` lignes
- `2207` timestamps distincts
- moyenne: `18.02` serveurs survivants par timestamp
- min: `1`
- max: `27`

Alors que le parc monitoré contient `85` serveurs.

Taux de survie moyen par timestamp:

- `18.02 / 85 = 21.2%`

### Répartition inégale

Exemples de serveurs très mal servis:

- `164` (`SingleStore`): `48`
- `118` (`APP-1`): `55`
- `183` (`prodCluster-router-vip-rw`): `62`
- `88` (`Slave_10-3`): `252`

Exemples de serveurs favorisés:

- `96` (`mariadb-10-8`): `720`
- `97` (`mariadb-10-9`): `718`
- `165` (`FRDC1-P-MAR01L`): `718`

Le problème est donc bien non uniforme, ce qui correspond exactement à une course d’écriture concurrente.

## Cause racine

La cause racine est une collision de workers sur le même fichier pivot.

Séquence:

1. le worker exécute bien `tryMysqlConnection()` toutes les 5 secondes
2. `setService()` appelle `exportData(..., false)`
3. `exportData()` écrit dans un fichier pivot nommé avec `time()`
4. plusieurs workers de la même seconde partagent le même fichier `::mysql_server`
5. les écritures ne sont pas fusionnées avec un verrou global fiable
6. le contenu du fichier partagé est partiellement écrasé
7. `Integrate::evaluate()` n’intègre que les survivants
8. certains serveurs perdent beaucoup plus de points que d’autres

Le problème est donc situé entre:

- [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php)
- [Integrate.php](/srv/www/pmacontrol/App/Controller/Integrate.php)

et non dans la connectivité MySQL du serveur `88`.

## Pourquoi `88` est plus touché que `96`

`88` et `96` sont bien tous les deux exécutés 720 fois.

La différence vient de l’ordre et du timing d’écriture dans la seconde:

- certains serveurs écrivent tôt puis se font écraser
- d’autres écrivent tard et survivent davantage jusqu’à l’intégration

Le serveur `96` semble tomber dans une position favorable du cycle.
Le serveur `88` tombe beaucoup plus souvent dans une position défavorable.

Donc:

- `96` n’est pas “plus sain” côté worker
- `88` n’est pas “plus lent” côté connectivité
- `96` survit mieux à une race condition d’écriture

## Proposition de correction

### Correction recommandée

Ne plus partager le fichier pivot entre plusieurs workers de la même seconde.

Option la plus simple et la plus robuste:

- générer un fichier unique par export
- conserver le `ts_file` à la fin du nom
- conserver un timestamp en première partie pour rester compatible avec l’intégrateur

Exemple de nom de fichier:

```text
<time()>::<pid ou uniqid>::mysql_server
```

Exemple:

```text
1773889128::2109809::mysql_server
```

Avantages:

- pas de collision entre workers
- pas de besoin de fusion concurrente
- correction ciblée
- compatible avec le `glob("*::mysql_server")` actuel

### Ajustement nécessaire côté intégration

`Integrate::evaluate()` fait aujourd’hui:

```php
$_elems    = explode(EngineV4::SEPERATOR, $file_name);
$ts_file   = end($_elems);
$timestamp = $_elems[0];
```

Ce parsing reste compatible si on ajoute un segment intermédiaire.

En revanche il faut vérifier et garder cette logique:

- premier segment = timestamp seconde
- dernier segment = `ts_file`

Donc la compatibilité est bonne si on ajoute juste un segment au milieu.

### Alternative possible mais moins bonne

Garder un fichier partagé par seconde et ajouter un verrou global de fusion.

Je ne recommande pas cette option:

- plus complexe
- plus fragile
- plus coûteuse
- plus difficile à déboguer

## Plan de remédiation recommandé

### Niveau 1

Modifier [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php):

- `allocate_shared_storage()`

pour créer un fichier pivot unique par export, par exemple:

```php
$shared_file = EngineV4::PATH_PIVOT_FILE
    . time()
    . $separator
    . getmypid()
    . '-' . uniqid('', true)
    . $separator
    . $name;
```

### Niveau 2

Ajouter un test d’intégration ou un test unitaire ciblé qui simule:

- deux exports concurrents
- même `ts_file`
- même seconde
- deux serveurs différents

et vérifie que les deux arrivent bien en base après `Integrate::evaluate()`.

### Niveau 3

Ajouter un écran ou un contrôle de santé technique avec:

- nombre de points `mysql_available` par serveur sur 1 heure
- seuil d’alerte si `< 90%` de la cadence attendue

Exemple de seuil:

- attendu: `720`
- alerte si `< 650`

## Conclusion

Le problème n’est pas dans le daemon `20` ni dans `tryMysqlConnection()` pour le serveur `88`.

Le problème réel est une collision d’écriture dans les fichiers pivots `tmp/tmp_file/*::mysql_server`:

- précision au niveau seconde seulement
- plusieurs workers partagent le même fichier
- certains serveurs sont écrasés avant intégration

Conséquence:

- `96` peut rester à `720`
- `88` peut tomber à `252`
- d’autres serveurs tombent encore plus bas

La correction recommandée est:

- un fichier pivot unique par export
- sans mutualisation concurrente par seconde

C’est la solution la plus simple, la plus robuste, et la plus cohérente avec les preuves mesurées.
