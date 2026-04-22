# Small gotchas

## `grep -a` on mysqlbinlog output

Always pass `-a` (text mode) when grepping mysqlbinlog output. Without it, grep detects NUL bytes inside base64 blobs, treats the stream as binary, and silently returns **only the first match**.

```bash
mysqlbinlog mysql-bin.000042 | grep -a 'some_pattern'
```

## Stale browser cache on legacy JS

When debugging an AJAX screen and the client logic looks out of date (e.g. a request to `Server/undefined`, or a pre-fix bug reappearing), suspect a cached legacy JS file. Bust it at the `<script>` tag:

```php
<script src="/pmacontrol/App/Webroot/js/Server/state.js?v=<?= filemtime(...) ?>"></script>
```

See [glial_framework.md](glial_framework.md) for the full AJAX contract.

## Private IPs have no GeoIP row

`10.x`, `172.16-31.x`, `192.168.x`, `127.x` are not in MaxMind's GeoLite2 — a range-query miss is **expected**, not a bug. See [data_geoip.md](data_geoip.md).

## Workers don't hot-reload code

Workers are long-running PHP processes. Editing `Aspirateur.php` (or any worker class) does **not** take effect until the worker restarts:

```bash
php App/Webroot/index.php daemon stopAll
php App/Webroot/index.php daemon startAll
```

`Worker::checkAll()` respawns workers within 7 seconds of `startAll`. Config files (`configuration/db.config.ini.php`) do hot-reload via `filemtime()` — see [worker_architecture.md](worker_architecture.md) "Hot-reload of DB config".
