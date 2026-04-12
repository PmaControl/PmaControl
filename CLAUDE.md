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

## CLI usage

The framework supports CLI invocation:

```bash
php App/Webroot/index.php <controller> <action> [params...]
```

MySQL is accessible without password from localhost (`mysql pmacontrol -e "..."`).

## Commits

Short, imperative descriptions: `add schema history`, `fix ProxySQL IPv6`. Group related changes per commit. Never commit credentials or `configuration/` files.

## Security

Never commit secrets from `config_sample/`. Treat `data/`, `tmp/`, backups as ephemeral. Validate permissions in `bin/` scripts.
