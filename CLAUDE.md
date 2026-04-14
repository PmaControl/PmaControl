# CLAUDE.md

## Project overview

PmaControl is a MySQL/MariaDB supervision and operations platform (PHP 8.2). Sources live under `App/` (Controller, view, Library, Webroot), the shared framework is in `/srv/www/glial/Glial/`. Config templates are in `config_sample/`; actual config goes in `configuration/` (never committed). Runtime caches (`tmp/`, `data/`) are ephemeral and gitignored.

## Build & test

```bash
composer install --no-interaction
./vendor/bin/phpunit                       # full suite (phpunit.xml)
php -S 0.0.0.0:8080 -t App/Webroot        # quick local UI
```

## ACL cache — IMPORTANT

After creating or renaming a **controller**, a **public action**, or a **view**, always delete the routing/ACL cache:

```bash
rm -f /srv/www/pmacontrol/tmp/acl/acl.ser
```

Without this the new route returns a 404 or permission error until the cache rebuilds.

## Database conventions

See [`doc/database_conventions.md`](doc/database_conventions.md) for full naming rules. Key points:
- FK columns: `id_{table}` (single ref) or `id_{table}__{role}` (double underscore for role)
- Join tables: `link__a__b`
- Booleans: `is_{flag}` (int 0/1)
- No FK constraints on partitioned or system-versioned tables (MariaDB limitation)

## AJAX endpoints (Glial framework)

- URLs must include `/ajax:true` in the path so the Router sets `$_GET['ajax'] = 'true'`.
- In the controller, skip the layout with: `$this->layout_name = false;`
- The `>` separator (e.g. `/ajax>true`) sets the framework's `IS_AJAX` constant but is older style; prefer `ajax:true`.
- jQuery `$.get()` / `$.load()` is the standard client-side pattern (see `App/Webroot/js/` examples).
- AJAX views must include their own `<script>` tags since the layout JS block is not rendered.

## Routing

The Router (`App/Webroot/Router.php`) parses URLs as: `/{lang}/{controller}/{action}/{param1}/{param2}/key:value/...`

- Key-value pairs with `:` populate `$_GET` (e.g. `/ajax:true` sets `$_GET['ajax'] = 'true'`).
- Positional params are passed as `$param` array to the controller action.

## Coding conventions

- PSR-12, 4-space indent, strict types where possible.
- Namespaces: `App\Controller`, `App\Library`, etc.
- POST-Redirect-GET for any state-mutating form.
- `$this->di['js']->addJavascript(array(...))` to load JS libraries.
- `$this->di['js']->code_javascript('...')` for inline JS (rendered by the layout footer, NOT available in AJAX mode).
- Views live in `App/view/{Controller}/{action}.view.php`.

## Chart.js

The project uses **Chart.js 4.5.1** (`App/Webroot/js/chart-4.5.1.umd.min.js`). For time-scale charts, also load `moment.js` and `chartjs-adapter-moment.min.js`. Use Chart.js v4 API:

- `scales.x` / `scales.y` (not `xAxes[]` / `yAxes[]`)
- `plugins.title` (not top-level `title`)
- `tension` (not `lineTension`)

Legacy `Chart.bundle.js` (v2) still exists but should not be used for new code.

## Extraction (time-series data)

`App\Library\Extraction::extract($vars, $servers, $dateRange, $range, $graph)` queries time-series data. With `$graph = true` and `Extraction::setOption('groupbyday', true)`, each result row contains a `graph` field (comma-separated `{x:new Date(...),y:...}` JS literals) and `day`, `min`, `max`, `avg`, `std` fields.

## GeoIP

Country/city lookups are served from `data_geoip` and `data_geoip_city`, which mirror the full GeoLite2 IPv4 network ranges in MySQL. Lookups are range queries on `varbinary(16)` bounds (`INET6_ATON`), so no `.mmdb` access at request time.

- **Tables**: `data_geoip` (~650k rows) and `data_geoip_city` (~3.7M rows). Both use `network_start`/`network_end` varbinary(16) + `country_iso`, `country_name`; the city table also has `region_*`, `city`, `postal`, `latitude`, `longitude`, `time_zone`.
- **Populate**:
  ```bash
  php App/Webroot/index.php server loadGeoip       # country, ~seconds
  php App/Webroot/index.php server loadGeoipCity   # city, much longer
  ```
  Both actions `TRUNCATE` then iterate the IPv4 space via `MaxMind\Db\Reader::getWithPrefixLen()` and batch-insert CIDR blocks.
- **Range lookup** (one IP):
  ```sql
  SELECT country_iso FROM data_geoip
  WHERE network_start <= INET6_ATON('89.30.104.134')
    AND network_end   >= INET6_ATON('89.30.104.134')
  LIMIT 1;
  ```
  `Server::main()` runs this per unique server IP and populates `$data['geoip'][ip] = country_iso`. The view calls `isoToFlag($iso)` to render the emoji flag.
- **Schemas**: `sql/incremental_v2/data_geoip.sql`, `sql/incremental_v2/data_geoip_city.sql`. Full notes in `doc/data_geoip.md`.
- Private IPs (10.x, 172.16-31.x, 192.168.x, 127.x) have no GeoLite2 record → empty country, expected.
- Re-run `loadGeoip` after a new `.mmdb` drop (MaxMind updates weekly); no need to re-run when adding servers.

## Group Replication (InnoDB Cluster)

PmaControl detects and displays MySQL Group Replication clusters via the `InnoDBCluster` controller. Page accessible at `/InnoDBCluster/index/` (menu: Architecture > Group Replication).

### Role detection

The PRIMARY/SECONDARY role is determined in priority order:
1. **`gr_member_role`** — collected by the Aspirateur from `performance_schema.replication_group_members WHERE MEMBER_ID = @@server_uuid`. Most reliable source. Values: `PRIMARY`, `SECONDARY`.
2. **Fallback** — `super_read_only` / `read_only`: if `gr_member_role` is empty (not yet collected, or server offline), a node with `super_read_only = OFF` is considered PRIMARY.

### State detection

Same pattern: `gr_member_state` from `performance_schema` (values: `ONLINE`, `RECOVERING`, `ERROR`, `OFFLINE`, `UNREACHABLE`), with fallback to `mysql_available`.

### Dot3 topology

Dot3 renders GR clusters as a subgraph with two sub-groups:
- **Primary** (green `#e8f5e9`, border `#1b5e20`)
- **Replica** (blue `#e3f2fd`, border `#1565c0`)

Each server node shows two extra rows: `GR Role` and `GR State`, colored by status (green=PRIMARY, blue=SECONDARY, yellow=RECOVERING, red=ERROR).

### MySQL vs MariaDB compatibility

All replication actions (`activateGtid`, `deactivateGtid`, `startSlave`, `stopSlave`, `skipCounter`, `setupSource`, `setParallelThreads`) detect the fork via `$db->getServerType()`:
- **MariaDB**: `STOP SLAVE 'conn'`, `CHANGE MASTER ... TO MASTER_USE_GTID`, `slave_parallel_threads`
- **MySQL 8+**: `STOP REPLICA FOR CHANNEL 'conn'`, `CHANGE REPLICATION SOURCE TO SOURCE_AUTO_POSITION`, `replica_parallel_workers`, `SET PERSIST` for durability

### GTID activation (MySQL)

MySQL requires a 4-step `gtid_mode` migration (`OFF → OFF_PERMISSIVE → ON_PERMISSIVE → ON`) plus `enforce_gtid_consistency = ON` before `SOURCE_AUTO_POSITION = 1`. `activateGtid()` enables GTID on the master first (found via `Mysql::getMaster()`), then the slave. Uses `SET PERSIST` so changes survive restart without editing `my.cnf`.

### Skip counter with GTID

`sql_slave_skip_counter` doesn't work with `gtid_mode = ON` on MySQL. `skipCounter()` detects this and injects an empty transaction: `SET GTID_NEXT = 'uuid:N'; BEGIN; COMMIT; SET GTID_NEXT = 'AUTOMATIC'`. The GTID to skip is found via `GTID_SUBTRACT()` from `performance_schema`, with fallback to parsing `Executed_Gtid_Set`.

## Time-series tables (`ts_*`) — IMPORTANT

Never run queries directly against `ts_*` tables (`ts_variable`, `ts_value_general_*`, `ts_value_slave_*`, `ts_value_digest_*`, `ts_max_date`, `ts_file`, etc.) without first checking the table exists via `information_schema.tables`:

```sql
SELECT 1 FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'ts_value_general_json';
```

These tables are created dynamically and may not exist on every installation. A missing table will cause a fatal SQL error in the controller or library.

## CLI usage

The framework supports CLI invocation:

```bash
php App/Webroot/index.php <controller> <action> [params...]
```

MySQL is accessible without password from localhost (`mysql pmacontrol -e "..."`).

## Commits

Short, imperative descriptions: `add schema history`, `fix ProxySQL IPv6`. Group related changes per commit. Never commit credentials or `configuration/` files. **Do NOT add `Co-Authored-By` trailers.**

## AJAX endpoints — both flags required

For JSON/API endpoints that return no HTML, set **both** flags:

```php
$this->layout_name = false;  // skip HTML layout wrapper
$this->view = false;          // skip .view.php require
```

Using only one of the two will either wrap the JSON in HTML (layout) or crash with a missing `.view.php` file.

## grep on mysqlbinlog output

Always use `grep -a` (text mode) when grepping mysqlbinlog output. Without `-a`, grep detects NUL bytes in base64 data and treats the stream as binary, silently returning only the first match.

## Security

Never commit secrets from `config_sample/`. Treat `data/`, `tmp/`, backups as ephemeral. Validate permissions in `bin/` scripts.
