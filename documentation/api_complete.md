# Documentation API Complete PmaControl

## 1. Perimetre

L'API actuelle de PmaControl est exposee par `App/Controller/Api.php` et cible les ressources de configuration modifiables depuis l'interface web. Elle suit le routeur historique Glial et herite donc du prefixe langue.

Base route:

- `/fr/api/config/{resource}`
- `/fr/api/config/{resource}/{id}`
- `/fr/api/openApi`

## 2. Philosophie de l'API

Cette API ne cherche pas encore a couvrir tout le monolithe. Elle se concentre sur les ressources de configuration a faible ambiguite metier:

- tags
- clients
- environments
- aliases
- storage-areas
- servers

Le controleur traduit directement les operations CRUD de l'UI legacy, avec normalisation des payloads, valeurs par defaut, cast bool/int et chiffrement du mot de passe serveur.

## 3. Contrat HTTP

### GET

- `GET /fr/api/config/{resource}`: liste les enregistrements
- `GET /fr/api/config/{resource}/{id}`: lit un enregistrement

### POST

- `POST /fr/api/config/{resource}`: cree un enregistrement a partir d'un JSON

### PUT / PATCH

- `PUT /fr/api/config/{resource}/{id}`: remplace ou met a jour l'enregistrement cible
- `PATCH /fr/api/config/{resource}/{id}`: mise a jour partielle

### DELETE

- `DELETE /fr/api/config/{resource}/{id}`: suppression ou soft delete selon la ressource

### OpenAPI-like

- `GET /fr/api/openApi`: retourne un document JSON simplifie des paths disponibles

## 4. Ressources et regles metier

### 4.1 tags

Table: `tag`

Champs:

- `id`
- `name`
- `color`
- `background`

Regles:

- creation exige `name`, `color`, `background`
- couleurs par defaut si non precisees lors de certaines creations guidees
- suppression hard delete

### 4.2 clients

Table: `client`

Champs:

- `id`
- `libelle`
- `logo`
- `date`
- `is_monitored`

Regles:

- `libelle` est obligatoire a la creation
- `is_monitored` est cast en entier 0/1
- `date` est alimentee a `now()` cote API
- le client `99` est protege en suppression

### 4.3 environments

Table: `environment`

Champs:

- `id`
- `libelle`
- `key`
- `class`
- `letter`

Regles:

- tous les champs fonctionnels sont obligatoires a la creation
- seuls les environnements `id > 6` sont supprimables via l'API

### 4.4 aliases

Table: `alias_dns`

Champs:

- `id`
- `id_mysql_server`
- `dns`
- `port`

Regles:

- creation exige un rattachement serveur, un DNS et un port
- suppression hard delete
- l'API reste une couche de configuration; la resolution intelligente continue de vivre dans `Alias` et `Aspirateur`

### 4.5 storage-areas

Table: `backup_storage_area`

Champs:

- `id_ssh_key`
- `id_geolocalisation_city`
- `id_geolocalisation_country`
- `ip`
- `port`
- `path`
- `libelle`

Regles:

- port par defaut `22`
- la validation d'atteignabilite SSH metier existe surtout dans le controleur web `StorageArea`; l'API fournit aujourd'hui le CRUD structurel

### 4.6 servers

Table: `mysql_server`

Champs principaux:

- identite: `id_client`, `id_environment`, `name`, `display_name`, `ip`, `hostname`, `port`
- acces: `login`, `passwd`, `database`, `is_ssl`
- SSH: `ssh_nat`, `ssh_port`, `ssh_login`, `is_sudo`, `is_root`
- exploitation: `is_monitored`, `is_proxy`, `is_vip`, `is_acknowledged`

Regles:

- le mot de passe est chiffre via `Chiffrement::encrypt`
- `port`, `ssh_port` et flags sont castes
- `hostname`, `ssh_nat`, `ssh_login` ont des valeurs par defaut vides
- suppression par soft delete (`is_deleted = 1`)

## 5. Normalisation des payloads

Le controleur applique trois transformations centrales:

1. **defaults**: completent les champs attendus
2. **casts**: booleens et entiers sont ramenes au format BD
3. **secrets**: `passwd` est chiffre avant persistance

Cela fait de l'API un point de normalisation plus robuste que plusieurs ecrans legacy qui interpolent directement les entrees.

## 6. Exemples de payloads

### Create tag

```json
{
  "name": "critical",
  "color": "#ffffff",
  "background": "#d9534f"
}
```

### Create client

```json
{
  "libelle": "ACME",
  "logo": "",
  "is_monitored": true
}
```

### Create environment

```json
{
  "libelle": "production",
  "key": "prod",
  "class": "danger",
  "letter": "P"
}
```

### Create alias

```json
{
  "id_mysql_server": 42,
  "dns": "db01.example.net",
  "port": 3306
}
```

### Create storage area

```json
{
  "id_ssh_key": 1,
  "id_geolocalisation_city": 10,
  "id_geolocalisation_country": 76,
  "ip": "10.10.10.10",
  "port": 22,
  "path": "/var/backups/mysql",
  "libelle": "Primary SFTP vault"
}
```

### Create server

```json
{
  "id_client": 1,
  "id_environment": 2,
  "name": "db-prod-01",
  "display_name": "db-prod-01",
  "ip": "10.0.0.15",
  "hostname": "db-prod-01.internal",
  "login": "root",
  "passwd": "secret",
  "database": "mysql",
  "port": 3306,
  "is_ssl": false,
  "ssh_nat": "",
  "ssh_port": 22,
  "ssh_login": "",
  "is_sudo": false,
  "is_root": true,
  "is_monitored": true,
  "is_proxy": false,
  "is_vip": false
}
```

## 7. Erreurs et comportements

- ressource inconnue -> `400`
- methode non supportee -> `405`
- create/update impossible -> `400` ou `500` selon l'origine de l'erreur
- identifiant absent sur update/delete -> rejet explicite

## 8. Limites actuelles

- couverture API encore partielle vis-a-vis de tout le monolithe
- peu de validation metier profonde sur certaines ressources techniques
- absence de couche repository dediee
- politique d'authentification a formaliser pour une exposition plus large

## 9. Evolution recommandee

- ajouter auth forte et audit trail
- exposer progressivement backup, tags de topologie et diagnostics lectures seule
- versionner l'API
- brancher une validation de schema JSON ou DTO stricts
- separer clairement lecture, ecriture et actions operateur
