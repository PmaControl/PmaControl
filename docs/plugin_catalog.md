# Plugin & storage-engine matrix (`/MysqlServer/plugins/<id>/`)

PmaControl ships a per-server **Plugins** tab between *Info* and *CVE* that surfaces three things side-by-side:

1. **Storage engines** — live `SHOW ENGINES` rows pulled from the Aspirateur cache (`information_schema::engines`).
2. **Installed plugins** — `SELECT * FROM information_schema.PLUGINS`, also from the Aspirateur cache (`information_schema::plugins`).
3. **Plugin catalog matrix** — `App\Library\PluginCatalog` cross-referenced against the live data; the only place with Install / Uninstall buttons.

The page is open to every authenticated user. Install and Uninstall actions are restricted to the **Super administrator** group (`user_main.id_group = 4`).

## Where the data comes from

| Card | Source key | Aspirateur collector |
|---|---|---|
| Storage engines | `information_schema::engines` (ts_variable id 5227) | `SELECT * FROM information_schema.ENGINES` |
| Installed plugins | `information_schema::plugins` (ts_variable id 4479) | `SELECT * FROM information_schema.PLUGINS ORDER BY PLUGIN_TYPE, PLUGIN_NAME` |
| Catalog matrix | `App/Library/PluginCatalog.php` (static knowledge base) | n/a |

`Extraction2::display(['information_schema::engines','information_schema::plugins'], [$id])` returns the most recent row keyed under `$row['engines']` and `$row['plugins']` directly (see `Extraction2::appendDisplayRow` — non-`slave`/`digest` radicals are flattened: `$table[$id][$metricName] = $value`). Both JSON columns are already decoded by `normalizeDisplayValue`, but the controller's `pluginsTabDecodeRows()` helper accepts both shapes (some collector paths leave the value as a JSON string).

## Family detection

`PluginCatalog::detectFamily($version . ' ' . $version_comment)`:

- `mariadb` if the string contains `mariadb` (case-insensitive).
- `mysql` if it contains `mysql` OR matches `^\d+\.\d+\.\d+` (Percona Server / Oracle MySQL).
- `unknown` otherwise — in that case the matrix shows all rows un-filtered.

The matrix view calls `PluginCatalog::forFamily($family)` to drop every row where the family's `availability` is `na`. On a MariaDB 11.x host, the MySQL-only rows (`validate_password`, `clone`, `group_replication`, `audit_log`) disappear.

## Availability levels

Each catalog row carries one of three availability tags per family:

| Tag | What it means | UI |
|---|---|---|
| `core` | `mariadb-server` (or `mysql-server`) ships the `.so` already → `INSTALL SONAME` works hot, no restart. | Green `hot` badge. |
| `package` | Requires `apt install <pkg>` on the host **first**, then a service restart, **then** `INSTALL SONAME`. | Orange `apt + restart` badge + the package name. |
| `na` | The plugin does not exist on this family. | Row filtered out by `forFamily()`. |

Note: when the package is already on disk (we infer this by the `.so` being present), `INSTALL SONAME` will succeed hot — the `apt + restart` badge is a worst-case warning. The button still tries the SQL; the failure mode is `errno 1126 — can't open shared library` which the UI surfaces verbatim.

## The Install / Uninstall split

Action lives in **two separate columns** to keep misclicks unlikely:

| Column | Renders when | What it does |
|---|---|---|
| `Install` (green background) | Plugin not currently loaded **and** the user is SuperAdmin | `INSTALL SONAME '<soname>'`, fallback `INSTALL PLUGIN <name> SONAME '<soname>.so'` for older MySQL builds |
| `Uninstall` (red background) | Plugin currently loaded **and** `PLUGIN_LIBRARY` is non-empty **and** the user is SuperAdmin | `UNINSTALL SONAME '<library>'`, fallback `UNINSTALL PLUGIN <name>` for libraries shared across plugins |

A plugin with an empty `PLUGIN_LIBRARY` is **statically compiled** into the server binary (`InnoDB`, `MyISAM`, `MEMORY`, `CSV`, `binlog`, `partition`, etc.). UNINSTALL would fail, so the matrix renders the grey **built-in** label instead of a button.

Non-admins see the matrix in read-only mode — both action columns show "SuperAdmin only" instead of buttons.

## Endpoints

| Method | Route | Purpose |
|---|---|---|
| `GET`  | `/MysqlServer/plugins/<id>/<name>/` | Render the three cards |
| `POST` | `/MysqlServer/installPlugin/<id>/ajax:true/` | SuperAdmin + CSRF (`mysqlserver.plugins.install`); body `plugin=<NAME>`. JSON `{ ok, support, sql }` or `{ error, package? }`. |
| `POST` | `/MysqlServer/uninstallPlugin/<id>/ajax:true/` | SuperAdmin + CSRF (`mysqlserver.plugins.uninstall`); body `plugin=<NAME>`. JSON `{ ok, sql }` or `{ error }`. Refuses statically-compiled plugins (no `PLUGIN_LIBRARY`). |

Both AJAX endpoints follow PmaControl's two contracts (#1219, #1220):
- The URL ends in `/ajax:true/` so `Router.php` sets `$_GET['ajax']='true'` and `Bootstrap.php` skips the DEBUG footer that would corrupt the JSON.
- The fetch sends `X-Requested-With: XMLHttpRequest` so `PersistentAuthSession::detectAjax()` returns true and the persistent-auth cookie does **not** rotate on every call.

## The catalog (`App/Library/PluginCatalog.php`)

The catalog is intentionally hand-curated to match the Debian 12 MariaDB 11.x package set + the few MySQL feature plugins that have no MariaDB equivalent. It's the only place to add a new plugin / engine to the matrix — the view and the endpoints read from it directly.

Row shape:

```php
[
    'name'        => 'BLACKHOLE',
    'kind'        => PluginCatalog::KIND_ENGINE,   // or KIND_PLUGIN
    'description' => 'Discards every write; …',
    'mariadb'     => [
        'availability' => PluginCatalog::AVAILABILITY_CORE,   // _CORE / _PACKAGE / _NA
        'soname'       => 'ha_blackhole',
        'package'      => 'mariadb-server',
        'install_hint' => '…',                                // optional, surfaced as small grey text
    ],
    'mysql'       => [ same shape ],
]
```

Adding a new plugin to the catalog is a four-line append in `PluginCatalog::all()`; no other code change required.

## Current coverage (34 rows)

### Engines

| Engine | MariaDB | MySQL |
|---|---|---|
| BLACKHOLE | core | core |
| ARCHIVE | core | core |
| FEDERATED | core (ha_federatedx) | core (ha_federated) |
| SPHINX | core | — |
| HANDLERSOCKET | core | — |
| ROCKSDB | `mariadb-plugin-rocksdb` | — (use Percona Server) |
| SPIDER | `mariadb-plugin-spider` | — |
| COLUMNSTORE | `mariadb-plugin-columnstore` | — |
| CONNECT | `mariadb-plugin-connect` | — |
| MROONGA | `mariadb-plugin-mroonga` | `mysql-mroonga` |
| OQGRAPH | `mariadb-plugin-oqgraph` | — |
| S3 | `mariadb-plugin-s3` | — |

### Plugins

| Plugin | MariaDB | MySQL |
|---|---|---|
| auth_ed25519 | core | — |
| auth_pam | core | — |
| auth_gssapi | `mariadb-plugin-gssapi-server` | — |
| simple_password_check | core | — |
| password_reuse_check | core | — |
| cracklib_password_check | `mariadb-plugin-cracklib-password-check` | — |
| file_key_management | core | — |
| hashicorp_key_management | `mariadb-plugin-hashicorp-key-management` | — |
| server_audit | core | — |
| audit_log | — | `percona-audit-log-plugin` |
| query_response_time | core | — |
| disks | core | — |
| locales | core | — |
| metadata_lock_info | core | — |
| query_cache_info | core | — |
| wsrep_info | core | — |
| sql_errlog | core | — |
| type_mysql_json | core | — |
| provider_bzip2 / lz4 / lzma / lzo / snappy | one row each | — |
| validate_password | — | core |
| group_replication | — | core |
| clone | — | core |

## ACL cache

Adding the `plugins`, `installPlugin`, or `uninstallPlugin` action triggered an ACL re-discovery — `tmp/acl/acl.ser` was removed after the corresponding commits. Any further new public method on `App\Controller\MysqlServer` requires the same step.

## Related

- Epic [#1212](https://git.istosia.com/pmacontrol/pmacontrol/issues/1212) — BLACKHOLE binlog relay, which exposed the Aspirateur-cache path used by this page.
- Issue [#1219](https://git.istosia.com/pmacontrol/pmacontrol/issues/1219) — the `FAILED: Array` debugging that taught us `_error()` vs `sql_error()` (re-used in the install / uninstall error paths).
- Issue [#1220](https://git.istosia.com/pmacontrol/pmacontrol/issues/1220) — `X-Requested-With` requirement that this page already honors on every fetch.
