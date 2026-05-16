# MySQL log import (`/MysqlServer/logs/<id>/<type>/`)

How PmaControl tails the **error log**, **slow query log**, **general log**,
**MariaDB sql_error_log** plugin output, and **OOM kills from journalctl**
of every monitored MySQL/MariaDB server, parses them, and feeds the
charts + line viewer under `/MysqlServer/logs/<id>/<type>/`.

This document describes the end-to-end pipeline so that an operator can
turn it on, verify it's healthy, and reason about what to do when a
cursor stops moving. The companion `docs/logs.md` is unrelated — it
covers PmaControl's *own* application logs in `tmp/log/`.

## Log types

| `LOG_TYPE_*` constant | Source variable on target | Storage dir | UI tab |
|---|---|---|---|
| `error_log` | `log_error` | `data/logs/<id>/log_error/` | Error log |
| `slow_query` | `slow_query_log_file` (always; `slow_query_log` controls activity) | `data/logs/<id>/log_slow_query/` | Slow query |
| `general_log` | `general_log_file` (only if `general_log = ON`) | `data/logs/<id>/general_log/` | General log |
| `sql_error` | `sql_error_log_filename` (only if MariaDB `SQL_ERROR_LOG` plugin enabled) | `data/logs/<id>/sql_error_log/` | SQL error |
| `oom_killer` | `journalctl -k`, lines matching `*mariadbd*Killed`/`*mysqld*Killed` | `data/logs/<id>/journalctl/` | OOM killer |

Source resolution lives in `MysqlLogCollector::resolveLogSources()` and
reads the cached server variables (no per-collection query — values come
from the variable Aspirateur snapshot that runs anyway).

## Pipeline at a glance

```
                              ┌─ daemon_main #37 (worker_mysql_log) ─┐
                              │  refresh_time=15 s                   │
                              │  Worker::addToQueue 6                │
                              │  one SysV-queue job per monitored    │
                              │  mysql_server                        │
                              └────────────────┬─────────────────────┘
                                               │
                                               ▼
                       ┌──────────────────────────────────────────────┐
                       │ Aspirateur::tryMysqlLogCollection            │
                       │ (App/Controller/Aspirateur.php:1596)         │
                       │                                              │
                       │ - SSH connect to mysql_server (ssh_login /   │
                       │   ssh_port from mysql_server)                │
                       │ - getMysqlLogVariables() reads the cached    │
                       │   variables (log_error, slow_query_log_file, │
                       │   general_log{,_file}, sql_error_log_*)      │
                       │ - MysqlLogCollector::resolveLogSources()     │
                       │ - For each source:                           │
                       │     readRemoteFileLogSource (tail by inode + │
                       │     offset, reading only the delta) OR       │
                       │     readRemoteJournalLogSource (journalctl   │
                       │     --since=<cursor>)                        │
                       │ - persistMysqlLogPayloadChunks → writes      │
                       │   data/logs/<id>/<type>/<YYYY-MM-DD>/*.part.N│
                       │   + .meta.json sidecar                       │
                       │ - upsertMysqlLogCursors → ssh_log_mysql_cursor│
                       └──────────────────────┬───────────────────────┘
                                              │
                                              ▼
                              ┌─ daemon_main #38 (Integrate MySQL logs) ─┐
                              │  refresh_time=15 s                       │
                              │  IntegrateLog::integrateAll              │
                              └──────────────────┬───────────────────────┘
                                                 │
                                                 ▼
                       ┌─────────────────────────────────────────────┐
                       │ IntegrateLog::evaluate                      │
                       │ (App/Controller/IntegrateLog.php:37)        │
                       │                                             │
                       │ - glob data/logs/*/*/*/*.part.* (excl.      │
                       │   .done marker files)                       │
                       │ - MysqlLogCollector::buildFileEvents()      │
                       │   - parser per log_type                     │
                       │   - emits {event_time, level, message,…}    │
                       │ - insertRawEvents → ssh_log_mysql_line      │
                       │ - updateAggregates → ssh_log_mysql_agg_*    │
                       │ - touch .part.N.done marker                 │
                       │   (idempotent re-runs)                      │
                       └──────────────────────┬──────────────────────┘
                                              │
                                              ▼
                       ┌─────────────────────────────────────────────┐
                       │ /MysqlServer/logs/<id>/<type>/              │
                       │                                             │
                       │ - chart: data/logs/<id>/<type>/<day>/       │
                       │   chart.{day,hour,minute}.json (pre-built)  │
                       │ - lines: loadMysqlLogWindowLines reads      │
                       │   ssh_log_mysql_line, paginated 100/page    │
                       │ - AJAX endpoints:                           │
                       │     /MysqlServer/logsChartData/<id>/<type>/ │
                       │       <granularity>/<key>/                  │
                       │     /MysqlServer/logsLinesData/<id>/<type>/ │
                       │       <scope>/<key>/                        │
                       └─────────────────────────────────────────────┘
```

## State tables

| Table | Role |
|---|---|
| `ssh_log_mysql_cursor` | One row per `(id_mysql_server, log_type, source_kind, source_name)`; tracks `inode`, `last_offset`, `last_event_time`. Cursor reset (e.g. logrotate on target) detected by inode change → restart at offset 0 |
| `ssh_log_mysql_line` | One row per parsed event. `event_time` indexed; combined with `id_mysql_server` for the line viewer |
| `ssh_log_mysql_agg_day` / `_agg_hour` / `_agg_minute` | Pre-computed count buckets keyed `(id_mysql_server, log_type, bucket_start)` for the histogram |

`ssh_log_mysql_line` is the heavy table. It grows linearly — 443k rows
in the current installation after roughly one month of pre-pause data.
Retention belongs in `cleaner_main` (cf. *Operational notes* below).

## Filesystem layout (`data/logs/`)

```
data/logs/<id_mysql_server>/
    <storage_dir>/                  ← e.g. log_error, log_slow_query, sql_error_log,
                                      general_log, journalctl
        <YYYY-MM-DD>/
            *.part.N                 ← raw bytes from the source, chunked
            .*.part.N.meta.json      ← {inode, offset_start, offset_end,
                                       source_kind, source_name, log_path}
            .*.part.N.done           ← integrator's "already parsed" marker
            chart.day.json           ← pre-aggregated buckets for the chart
            chart.hour.json
            chart.minute.json
```

`MysqlLogCollector::ensureLocalStorageDirectories()` creates the per-day
directory on first contact. `MysqlLogCollector::getStorageDirectoryName()`
maps `LOG_TYPE_*` → storage dir (the dir names match the historical
`mysql --log-error` filename conventions on Debian/Ubuntu).

## Daemon registry

```sql
SELECT id, name, class, method, params, refresh_time, max_delay, is_enabled
FROM daemon_main
WHERE name LIKE '%log%' OR class IN ('Aspirateur','IntegrateLog')
ORDER BY id;
```

| `id` | `name` | `class::method params` | `refresh_time` | `is_enabled` |
|---|---|---|---|---|
| 37 | Aspirateur MySQL logs | `Worker::addToQueue 6` | 15 s | **OFF by default** |
| 38 | Integrate MySQL logs   | `IntegrateLog::integrateAll logs` | 15 s | **OFF by default** |

Both rows are shipped disabled. To turn the pipeline on:

```sql
UPDATE daemon_main SET is_enabled = 1 WHERE id IN (37, 38);
```

The `./glial agent check_daemon` cron (every minute) reads `daemon_main`
and (re)spawns each worker whose `is_enabled = 1`. There's nothing else
to deploy — no systemd unit, no extra container.

## Worker / queue layout

- `daemon_main #37` enqueues one SysV-queue job per `mysql_server` row
  into queue `#6` (`Worker::addToQueue 6`).
- A long-running `Worker` process drains queue `#6` and invokes
  `Aspirateur::tryMysqlLogCollection` with the `id_mysql_server`. See
  `docs/worker_architecture.md` for the queue / pool model.
- `daemon_main #38` is a plain ticker — `./glial IntegrateLog
  integrateAll logs` every 15 s, no queue, no fan-out. It greps
  `data/logs/*/*/*/*.part.*` and processes up to `MAX_FILE_AT_ONCE = 10`
  files per tick.

## Health checks

A healthy pipeline looks like:

```sql
SELECT MAX(updated_at) FROM ssh_log_mysql_cursor;          -- within seconds of NOW()
SELECT MAX(event_time) FROM ssh_log_mysql_line;            -- within minutes of NOW()
SELECT COUNT(*) FROM aspirateur_attempt
 WHERE kind='mysql_log' AND result=0
   AND started_at > NOW() - INTERVAL 1 HOUR;               -- 0 (or close to it)
```

Per-server lag:

```sql
SELECT m.id, m.display_name,
       c.log_type, c.source_name,
       c.last_event_time,
       TIMESTAMPDIFF(SECOND, c.last_event_time, NOW()) AS lag_s
FROM mysql_server m
LEFT JOIN ssh_log_mysql_cursor c ON c.id_mysql_server = m.id
WHERE m.is_monitored = 1 AND m.is_deleted = 0
ORDER BY lag_s DESC NULLS LAST;
```

A cursor with `lag_s > 1800` (30 min) is the canonical "this agent is
stuck" signal — feed it back into the alerting card on `/Home/index`.

## Failure modes

| Symptom | Likely cause | Where to look |
|---|---|---|
| Cursor frozen, no error in `aspirateur_attempt` | SSH succeeded but the log path returned 0 new bytes (replay paused on the target?) | manually `ssh … tail` on the target |
| `aspirateur_attempt.kind='mysql_log', result=0` rows pile up for one server | SSH connect failure, bad key, sudo prompt | `error_message` column carries the exception class + message |
| Lines visible in `ssh_log_mysql_line` but the chart stays empty | `chart.day.json` is regenerated by `buildMysqlLogsChartPayload()` on every page load — usually a stale `data/logs/<id>/<type>/<day>/` permission issue | `ls -la` that directory; should be writeable by the daemon user |
| `data/logs/` grows without bound | nothing rotates it today — extend `Log::rotate` (worker #19) or add a `cleaner_main` rule | see *Operational notes* below |
| Wrong inode reported, double ingestion | logrotate on the target moved the file but the daemon caught it mid-rotate | clear the cursor row → next run restarts at offset 0; `.done` markers prevent re-ingestion of already-parsed chunks |
| `event_time` is NULL on a cursor | parser regex didn't match the very first line of a fresh log — harmless, fills in on the next event | none |

## Operational notes

- **Retention.** `data/logs/` is currently unbounded. `Log::rotate`
  (daemon #19) only rotates files under `tmp/log/`. Either extend it or
  add a dedicated cleaner. `ssh_log_mysql_line` itself should be
  partitioned by month (`RANGE` on `event_time`) so old months drop
  without an expensive `DELETE`.
- **Concurrency.** Queue `#6` is shared by all `mysql_server` rows; if
  fifty servers are enqueued at the same tick they will be processed in
  whatever order the worker pool drains. Spike protection should live
  upstream in the queue producer, not in the collector.
- **SSH credentials.** Same `mysql_server.ssh_login` / `ssh_port` /
  `ssh_key` rows the rest of Aspirateur uses; if one of them works for
  variable collection and processlist, it works here.
- **journalctl.** Only `oom_killer` uses it today (`journalctl -k`
  filtered for mariadbd/mysqld kills). Pure-journal `error_log`
  (`log_error = stderr` redirected by systemd) is **not** wired —
  `resolveLogSources()` skips `stderr` paths. Extend
  `resolveLogSources` to fall back to a journal source when
  `log_error` is `stderr` and `slow_query_log_file` is empty.

## Activation cheatsheet

```sql
-- 1. Turn the pipeline on
UPDATE daemon_main SET is_enabled = 1 WHERE id IN (37, 38);

-- 2. Wait ~1 minute for the check_daemon cron to (re)spawn both
SELECT id, name, FROM_UNIXTIME(date) AS last_heartbeat, pid
FROM daemon_main WHERE id IN (37, 38);

-- 3. Confirm cursors are advancing
SELECT id_mysql_server, log_type, last_event_time, updated_at
FROM ssh_log_mysql_cursor ORDER BY updated_at DESC LIMIT 10;

-- 4. Confirm new lines are landing
SELECT MAX(event_time), COUNT(*) FROM ssh_log_mysql_line
WHERE event_time > NOW() - INTERVAL 1 HOUR;
```

## Code map

| Concern | File |
|---|---|
| Source resolution, parsers, OOM split, chart aggregation | `App/Library/MysqlLogCollector.php` |
| SSH transport, cursor I/O, per-server orchestration | `App/Controller/Aspirateur.php` (`tryMysqlLogCollection`, `readRemoteFileLogSource`, `readRemoteJournalLogSource`) |
| `.part.N` integrator | `App/Controller/IntegrateLog.php` |
| `/MysqlServer/logs/` page + AJAX | `App/Controller/MysqlServer.php` (`logs`, `logsChartData`, `logsLinesData`, `buildMysqlLogsChartPayload`, `loadMysqlLogWindowLines`) |
| Worker / queue scheduler | `App/Controller/Worker.php` (`addToQueue`) — see `docs/worker_architecture.md` |
| Attempt audit | `aspirateur_attempt` table, `App/Library/Kpi/AspirateurAttemptLogger.php` |
