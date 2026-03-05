# Listener — Documentation fonctionnelle et technique

Cette documentation décrit le fonctionnement du **Listener** (post-traitements déclenchés
après l’intégration des fichiers temporisés) et les points d’extension principaux.

Fichiers concernés :

- `App/Controller/Listener.php`
- `App/model/IdentifierPmacontrol/listener_main.php`
- Tables : `ts_max_date`, `ts_date_by_server`, `ts_file`, `listener_main`

---

## 1) Objectif

Le Listener exécute des **post-traitements** quand de nouvelles données sont intégrées
dans un fichier temporisé (`ts_file`). Il compare `ts_max_date.date` à
`ts_max_date.last_date_listener` afin de détecter les mises à jour, puis déclenche
les méthodes métiers associées (ex: rafraîchissement des bases, variables, aliases DNS).

---

## 2) Flux global

### 2.1 Chargement des listeners (`Listener::load` / `Listener::init`)

`Listener::load()` déclare la liste des traitements à exécuter pour chaque `ts_file`.
`Listener::init()` synchronise ensuite la table `listener_main` (une ligne par couple
`class` / `method`) pour que le scheduler sache quels fichiers surveiller.

Extraits (simplifiés) :

```php
// Listener::load()
self::$load_listener['mysql_schemata']['mysql_database'] = "Listerner::updateDatabase";
self::$load_listener['mysql_global_variable']['variables'] = "Listerner::afterUpdateVariable";
self::$load_listener['performance_schema']['performance_schema'] = "Digest::integrate";
self::$load_listener['ssh_hardware']['ssh_hardware'] = "Alias::updateAlias";
```

---

### 2.2 Détection des mises à jour (`Listener::check`)

Le listener recherche les fichiers dont la date a changé :

```sql
SELECT id_ts_file, id_mysql_server
FROM ts_max_date
WHERE last_date_listener != date
  AND id_ts_file IN (SELECT DISTINCT id_ts_file FROM listener_main);
```

Pour chaque couple `(id_mysql_server, id_ts_file)`, le listener prépare la plage à traiter.

---

### 2.3 Détermination des dates à traiter (`Listener::getUpdateTodo`)

Cette étape identifie la nouvelle fenêtre à traiter via `ts_date_by_server` :

```sql
SELECT MIN(date) AS min_date, MAX(date) AS max_date
FROM ts_date_by_server
WHERE id_mysql_server = ?
  AND id_ts_file = ?
  AND date > (SELECT last_date_listener FROM ts_max_date ...)
```

Le résultat est ensuite transmis à `dispatch()`.

---

### 2.4 Routage des traitements (`Listener::dispatch`)

Le dispatch appelle la méthode métier selon le `ts_file` :

```php
switch ($arr['ts_file']) {
    case EngineV4::FILE_MYSQL_DATABASE:
        $this->updateDatabase($arr);
        break;

    case EngineV4::FILE_MYSQL_VARIABLE:
        $this->afterUpdateVariable($arr);
        break;

    case "performance_schema":
        Digest::integrate([$arr['id_mysql_server'], $arr['min_date']]);
        break;

    case "ssh_hardware":
        $alias = new Alias();
        $alias->updateAlias($arr);
        break;
}
```

---

### 2.5 Mise à jour des marqueurs (`Listener::updateListener`)

Après exécution, la date `last_date_listener` est avancée afin de ne pas retraiter
les mêmes données :

```sql
UPDATE ts_date_by_server SET is_listened=1
WHERE id_mysql_server = ? AND id_ts_file = ? AND date = ?;

UPDATE ts_max_date SET last_date_listener = ?
WHERE id_mysql_server = ? AND id_ts_file = ?;
```

---

## 3) Cas métier : SSH Hardware → Alias DNS

Lorsqu’un fichier **`ssh_hardware`** change de date dans `ts_max_date`,
le Listener déclenche automatiquement :

```php
Alias->updateAlias($arr)
```

Cette étape permet de synchroniser les alias DNS basés sur
`ssh_hardware::ips` (voir `Alias::addAliasFromSshIps`).

---

## 4) Ajouter un nouveau listener

1. **Déclarer le listener** dans `Listener::load()` :
   ```php
   self::$load_listener['<ts_file>']['<from>'] = "Classe::methode";
   ```
2. **Ajouter le routage** dans `Listener::dispatch()`.
3. **S’assurer que `ts_file` existe** (sinon il sera créé lors de l’intégration).
4. (Optionnel) Ajouter la doc correspondante.

---

## 5) Points d’attention

- `listener_main` contient les couples `class/method` et l’`id_ts_file` associé.
- `Listener::init()` doit être exécuté pour insérer les nouveaux listeners dans `listener_main`.
- Le listener ne traite **que les fichiers dont `last_date_listener != date`**.
