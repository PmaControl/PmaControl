# PmaControl REST API

## Scope

The REST API documents and exposes the configuration I/O that is currently editable from the UI for these resources:

- `tags`
- `clients`
- `environments`
- `aliases`
- `storage-areas`
- `servers`
- `ssh-keys`

Base route: `/fr/api/config/{resource}`

Supported verbs:

- `GET /fr/api/config/{resource}`: list all items.
- `GET /fr/api/config/{resource}/{id}`: read one item.
- `POST /fr/api/config/{resource}`: create one item from a JSON body.
- `PUT /fr/api/config/{resource}/{id}`: update one item from a JSON body.
- `PATCH /fr/api/config/{resource}/{id}`: partial update.
- `DELETE /fr/api/config/{resource}/{id}`: delete or soft-delete depending on the resource policy.

OpenAPI-like JSON export: `/fr/api/openApi`

## Resource payloads

### `tags`

```json
{
  "name": "critical",
  "color": "#ffffff",
  "background": "#d9534f"
}
```

### `clients`

```json
{
  "libelle": "ACME",
  "logo": "",
  "is_monitored": true
}
```

### `environments`

```json
{
  "libelle": "production",
  "key": "prod",
  "class": "danger",
  "letter": "P"
}
```

### `aliases`

```json
{
  "id_mysql_server": 42,
  "dns": "db01.example.net",
  "port": 3306
}
```

### `storage-areas`

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

### `servers`

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

### `ssh-keys`

```json
{
  "name": "VPG Pmacontrol (MASTER KEY)",
  "added_on": "2025-05-28 12:26:39",
  "fingerprint": "1C40621E1D34ABB2EC9BAB9CEE261D5193E099E0",
  "user": "vpg",
  "public_key": "ssh-ed25519 AAAA...",
  "private_key": "-----BEGIN OPENSSH PRIVATE KEY-----\n...\n-----END OPENSSH PRIVATE KEY-----",
  "type": "ED25519",
  "bit": 256,
  "comment": "PmaControl"
}
```

## Delete policies

- `tags`, `aliases`, `storage-areas`: hard delete.
- `clients`: hard delete with a guard blocking client `99`.
- `environments`: hard delete only for records with `id > 6`.
- `servers`: soft delete through `is_deleted = 1`.
