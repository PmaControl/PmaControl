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