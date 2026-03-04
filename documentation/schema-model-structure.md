# Export de schémas — Nouvelle arborescence

Cette documentation décrit la **nouvelle structure** d’export des schémas SQL et la migration
depuis l’ancien format.

---

## 1) Racine

Les exports sont stockés par serveur MySQL :

```
pmacontrol/data/model/<id_mysql_server>/
```

La nouvelle arborescence introduit un niveau `databases/` :

```
pmacontrol/data/model/<id_mysql_server>/databases/<db_name>/
```

---

## 2) Structure cible par base

```
pmacontrol/data/model/<id_mysql_server>/databases/<db_name>/
  00-pre/
    000-create-db.sql        # optionnel
    010-session.sql          # optionnel
    020-variables.sql        # optionnel
  schema/
    tables/
      <table>.sql            # CREATE TABLE (sans data)
    views/
      <view>.sql             # CREATE OR REPLACE VIEW
  routines/
    procedures/
      <proc>.sql             # DROP + CREATE PROCEDURE
    functions/
      <func>.sql             # DROP + CREATE FUNCTION
  events/
    <event>.sql              # DROP EVENT IF EXISTS + CREATE EVENT
  triggers/
    <table>__<trigger>.sql   # DROP TRIGGER IF EXISTS + CREATE TRIGGER
  data/
    <table>.sql              # INSERTs (optionnel)
  99-post/
    800-roles.sql            # optionnel
    900-grants.sql           # optionnel
    990-checks.sql           # optionnel
```

---

## 3) Export automatique (Schema.php)

Dans `App/Controller/Schema.php` :

- la méthode `export()` écrit maintenant les tables dans :
  - `schema/tables/<table>.sql`
- la structure complète est créée via `ensureSchemaDirectoryStructure()`.
- les routines (procédures / fonctions) sont exportées dans :
  - `routines/procedures/<proc>.sql`
  - `routines/functions/<func>.sql`
- les triggers sont exportés dans :
  - `triggers/<table>__<trigger>.sql`
- les events sont exportés dans :
  - `events/<event>.sql`
- chaque sous-dossier dispose de son **dépôt git propre** :
  - `00-pre/`, `schema/`, `routines/`, `triggers/`, `events/`, `data/`, `99-post/`.

### One-shot : migration du repo racine vers `schema/`

Si un dépôt git existe encore à la racine d’une base (`databases/<db>/.git`),
une commande permet de le migrer dans `schema/.git` :

```
./glial schema migrateSchemaRepo <id_mysql_server> <database>
```

### One-shot (batch) : migration pour toutes les bases

Pour migrer en masse tous les dépôts racine vers `schema/.git` :

```
./glial schema migrateSchemaReposAll [id_mysql_server]
```

### Liste des bases “skipped”

Pour récupérer les bases sautées (pas de `.git` ou déjà migrées) :

```
./glial schema listSchemaRepoSkips [id_mysql_server]
```

La commande affiche également un résumé **par serveur** (nombre de bases manquantes ou déjà migrées)
pour identifier rapidement les serveurs problématiques.

### Routines (procédures / fonctions)

Une méthode utilitaire est disponible pour récupérer la liste des routines d’un serveur
et générer les requêtes `SHOW CREATE` associées :

```php
use App\Library\Mysql;

// Procédures
$queries = Mysql::getRoutineShowCreateQueries([12, 'PROCEDURE']);

// Fonctions
$queries = Mysql::getRoutineShowCreateQueries([12, 'FUNCTION']);
```

Cette méthode exécute la requête suivante (triée par schéma et nom) :

```sql
SELECT CONCAT(
  'SHOW CREATE PROCEDURE `', ROUTINE_SCHEMA, '`.`', ROUTINE_NAME, '`;'
)
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_TYPE='PROCEDURE';
```

Pour les fonctions, `ROUTINE_TYPE` est remplacé par `FUNCTION` et `SHOW CREATE FUNCTION`.

---

## 4) Migration one-shot

Une méthode est disponible :

```
./glial schema migration [id_mysql_server]
```

### Ce que fait la migration

- Crée `databases/` si nécessaire.
- Crée la nouvelle structure de sous-dossiers.
- Déplace les fichiers `*.sql` legacy vers `schema/tables/`.
- Déplace le `.git` s’il existait dans l’ancien dossier de base.
- Nettoie les répertoires legacy vides.

### Conflits

Si un fichier existe déjà dans `schema/tables/` :

- si le contenu est identique → suppression de l’ancien fichier.
- sinon → renommage en `*.legacy.sql`.

---

## 5) Format legacy (ancien)

```
pmacontrol/data/model/<id_mysql_server>/<db_name>/<table>.sql
```

Cette structure est remplacée par le format décrit ci‑dessus.

---

> Documentation liée à `Schema.php` uniquement.