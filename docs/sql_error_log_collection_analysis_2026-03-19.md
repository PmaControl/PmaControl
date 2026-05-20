# SQL Error Log And OOM Collection Design Analysis

Date: 2026-03-19

## Goal

Add a new SSH-side collector family in `Aspirateur` able to:

- discover the active MySQL/MariaDB log file paths on each server
- read only the new lines since the previous check
- persist raw events efficiently
- maintain lightweight aggregates for drill-down charts
- expose the selected-range log lines below the chart

Target files / sources:

- `sql_error_log_filename` only if plugin `SQL_ERROR_LOG` is enabled
- `log_slow_query_file`
- `log_error`
- `general_log_file`
  - but `general_log` should stay disabled by default
- kernel / service journal for OOM events
  - `journalctl -k`
  - `journalctl -k | grep -i mariadbd`
  - `journalctl -u mariadb | grep -Ei 'oom|killed|memory'`

Additional required case:

- `rocksdb:high` must be handled, not only `mariadbd`, because the OOM killer can hit internal MyRocks helper threads while the effective victim is still the MariaDB service cgroup
- MDEV-39107 must be handled defensively:
  - `sql_errors.log` entries can be concatenated / corrupted under load
  - a new entry prefix can appear inside the previous SQL text
  - the collector must therefore re-split entries on the log header pattern before parsing

Expected UI:

- rolling month: one bar per day
- click one day: one bar per hour over 24h
- click one hour: one bar per minute
- raw logs below the chart for the selected range

## Executive Summary

Do **not** store SQL error log lines or OOM journal events in `ts_value_general_json`.

That table is already used for heavy snapshot-style JSON payloads such as `ssh_stats::memory_detail_kb`, and SQL error logs are not snapshots. They are append-only event streams. Mixing them into generic JSON time series would make ingestion heavier, drill-down queries slower, retention harder, and dedup/rotation handling fragile.

The correct design is:

1. a **dedicated SSH collector** for SQL error logs
2. a **dedicated integration path** with dedicated tables
3. **pre-aggregated buckets** for minute/hour/day charts
4. a short-lived **query cache** on top of those aggregates

This is also how large log platforms work in practice: raw events and precomputed/query-oriented structures are separated.

## What Already Exists In PmaControl

### Existing metric definitions

The repository already knows the relevant log-related variables:

- `variables::log_error`
- `variables::sql_error_log_filename`
- `variables::general_log_file`
- `variables::slow_query_log_file`
- `variables::general_log`

Important constraints to apply:

- `sql_error_log_filename` is only meaningful if the `SQL_ERROR_LOG` plugin is actually enabled
- `general_log` must be considered disabled by default even if the filename variable exists
- `log_error` may point to a classic error log file, `stderr`, or be empty depending on the deployment

These are present in `sql/full/pmacontrol.sql` and `App/Library/Decoupage.php`.

### Existing SSH custom collection pattern

`Aspirateur::getStats()` already collects SSH-side machine data and exports it as `ssh_stats`. One example is:

- `ssh_stats::memory_detail_kb`

This metric is built from `ps -eo comm,rss --no-headers` and stored as JSON.

This proves two things:

- SSH-side custom collectors are already acceptable in `Aspirateur`
- `ts_value_general_json` is already used for large payloads and should not be overloaded further

### Existing evidence for OOM collection need

The operating system logs already expose the exact signals we need for OOM incidents. Typical commands:

- `journalctl -k`
- `journalctl -k | grep -i mariadbd`
- `journalctl -u mariadb | grep -Ei 'oom|killed|memory'`

Real examples observed:

- `mariadbd invoked oom-killer`
- `Memory cgroup out of memory: Killed process ... (mariadbd)`
- `rocksdb:high invoked oom-killer`

The `rocksdb:high` case is important because it proves that the initiating thread name can be:

- `mariadbd`
- `rocksdb:high`
- or another MariaDB-associated worker thread

while the real constrained cgroup is still:

- `/system.slice/mariadb.service`

So the collector must not key only on process name equality with `mariadbd`.

### Existing evidence for SQL_ERROR_LOG parsing issues

MariaDB issue:

- `MDEV-39107`
- `sql_errors.log entries can be concatenated/corrupted under load, causing one SQL error entry to appear inside another`

Observed impact:

- large JSON/TEXT payloads can cause one SQL error entry to begin inside the previous one
- the collector cannot trust simple line boundaries

Required workaround in PmaControl:

- before parsing `sql_errors.log`, insert a virtual split whenever a new header pattern appears:
  - `YYYY-MM-DD HH:MM:SS user[user] @ ...`

Source:

- `https://jira.mariadb.org/browse/MDEV-39107`

### Existing integration precedent for dedicated tables

`Integrate.php` already has a dedicated path for non-generic series:

- `insert_slave_value(..., "slave")`
- `insert_slave_value(..., "digest")`

So a dedicated `sql_error_log` integration path fits the current architecture better than forcing everything through `ts_value_general_*`.

## Why `ts_value_general_json` Is The Wrong Place

Storing raw SQL error log lines in `ts_value_general_json` would cause multiple issues:

### 1. Wrong data model

`ts_value_general_json` is snapshot-oriented: one metric value at one timestamp.

SQL error logs are event-oriented:

- variable number of lines between checks
- each line has its own timestamp and content
- no natural “single JSON value per poll” model

### 2. Poor queryability

The target UI needs:

- day counts over one month
- hour counts over one day
- minute counts over one hour
- raw lines by selected range

If raw lines are embedded in JSON blobs, every drill-down query becomes a JSON-scan problem instead of an indexed range query.

### 3. Hard rotation / dedup handling

With generic JSON snapshots, it becomes much harder to track:

- inode changes
- truncation
- resume offsets
- duplicate rereads after reconnect

### 4. Retention mismatch

Aggregates can be kept much longer than raw lines. A dedicated schema lets us:

- keep raw lines for 30-90 days
- keep aggregates for much longer

That is not clean with generic JSON blobs.

## What Large Log Platforms Usually Do

### Elastic / Kibana

Elastic stores raw log events in append-only indices/data streams backed by time-based indices, then query-time or transform-based aggregations are used for analytics. The raw event store and the aggregated views are conceptually separate.

Relevant official docs:

- Elastic data streams and backing indices
- Elastic transforms / rollup-style aggregation flows

Source:

- https://www.elastic.co/docs/manage-data/data-store/data-streams
- https://www.elastic.co/docs/explore-analyze/transforms/transform-overview

### Grafana Loki

Loki separates:

- raw log chunks
- index metadata / references
- query result cache and chunk cache

The important point here is that logs are stored as raw chunks, while query acceleration uses dedicated caches rather than reusing the raw storage format for everything.

Source:

- https://grafana.com/docs/loki/latest/operations/caching/

### ClickHouse observability patterns

ClickHouse commonly uses:

- raw append tables for log events
- materialized views into aggregate tables for faster drill-down

This is very close to what PmaControl should do here.

Source:

- https://clickhouse.com/docs/materialized-view/incremental-materialized-view

### Splunk

Splunk separates raw event data and index metadata structures inside buckets. Again, raw log storage and fast search structures are not the same thing.

Source:

- https://docs.splunk.com/Documentation/Splunk/latest/Indexer/HowSplunkstoresindexes

### Design takeaway

The common pattern is consistent:

- raw events stored in a dedicated event store
- derived aggregates / caches stored separately
- drill-down charts query aggregates first, raw events second

That is exactly the model to apply here.

## Recommended PmaControl Design

## 1. Dedicated collectors in `Aspirateur`

Add dedicated collectors instead of extending generic `ssh_stats`.

Example shape:

- `Aspirateur::collectSqlErrorLog($param)`
- `Aspirateur::collectMysqlLogFiles($param)`
- `Aspirateur::collectMysqlOomEvents($param)`

These collectors should:

1. resolve the active log sources
2. load the previous cursor state
3. read only appended bytes
4. parse lines
5. export raw events to a dedicated pivot file

### How to resolve the log file paths

The collector must resolve and store these sources explicitly:

- `log_error`
- `log_slow_query_file`
- `general_log_file`
- `sql_error_log_filename`

And these switches / constraints:

- `general_log`
  - disabled by default, do not collect by default
- `SQL_ERROR_LOG` plugin enabled state
  - only then is `sql_error_log_filename` relevant

Recommended behavior:

- `log_error`
  - collect if non-empty and file-backed
- `log_slow_query_file`
  - collect if slow log is enabled and file-backed
- `general_log_file`
  - do not collect unless explicitly enabled by configuration because volume can explode
- `sql_error_log_filename`
  - collect only if `SQL_ERROR_LOG` plugin is active and the resolved file exists

Priority order:

1. `variables::log_error`
2. fallback `variables::sql_error_log_filename`
3. verify file exists remotely

Special cases:

- empty value: disabled
- `stderr`: no file to tail directly
- journald-only setups: must be handled for OOM events, and optionally later for generic MariaDB errors

### How to collect OOM events

OOM events should not be collected from MySQL variables. They must come from the system journal.

Recommended source commands:

- `journalctl -k`
- `journalctl -k -o short-iso`
- `journalctl -u mariadb -o short-iso`

Recommended event filters:

- `oom-killer`
- `out of memory`
- `Killed process`
- `mariadbd`
- `rocksdb:high`
- `/system.slice/mariadb.service`

Important detection rule:

- match by cgroup and service context, not only by process name

Reason:

- the kernel can log `rocksdb:high invoked oom-killer`
- then later `Killed process ... (mariadbd)`
- both lines belong to the same MariaDB OOM incident

So the parser should build one logical incident from multiple journal lines that may mention:

- `mariadbd`
- `rocksdb:high`
- `mariadb.service`
- `Memory cgroup out of memory`
- `Killed process`

### How to track progress

Store per-server cursor state with at least:

- `id_mysql_server`
- `log_path`
- `inode`
- `last_offset`
- `last_check`

This state should be local to the collector, not inside `ts_value_general_json`.

Recommended storage:

- a small dedicated SQL table
- or a small dedicated local state file per server

A SQL table is better for observability and recovery.

Suggested table:

`ssh_log_sqlerror_cursor`

Columns:

- `id_mysql_server`
- `log_path`
- `inode`
- `last_offset`
- `last_checked_at`
- `updated_at`

For journald-based OOM collection, use a separate cursor:

- `last_journal_cursor`
- or `last_journal_ts`

Suggested separate table:

`ssh_log_journal_cursor`

Columns:

- `id_mysql_server`
- `stream_name`
- `journal_cursor`
- `last_checked_at`
- `updated_at`

### How to read only new content

Use SSH to retrieve:

- file existence
- inode
- current size

Then:

- if same inode and size >= offset: read `[offset, size)`
- if inode changed: rotation detected, reset to `0`
- if size < offset: truncation detected, reset to `0`

This must be byte-offset based, not `tail -n`, to stay fast and deterministic.

### How to parse lines

Store a parsed structure when possible:

- `event_time`
- `user`
- `server`
- `message`
- `raw_line`

If parsing fails:

- still store the raw line
- use insertion time as fallback timestamp only when necessary

Multiline handling can be deferred. First version can store one physical line per event.

Exception for `sql_errors.log`:

- because of `MDEV-39107`, one physical line can contain multiple logical SQL error entries
- so the collector must first re-split the payload on the SQL error log header pattern
- only then can it parse one logical event at a time

For OOM journal lines, parse at least:

- `event_time`
- `unit`
- `process_name`
- `pid`
- `message`
- `raw_line`
- `incident_key`

Recommended `incident_key` logic:

- hash of `(id_mysql_server, nearest oom start timestamp, service scope)`

This lets multiple journal lines collapse into one incident while keeping raw lines available.

## 2. Dedicated integration path

Do not route these log and OOM events through generic `ts_value_general_*`.

Add a dedicated integration path, similar in spirit to existing slave/digest specialization.

Example:

- `Integrate::insert_sql_error_log(...)`
- `IntegrateLog::insert_mysql_log_line(...)`
- `Integrate::insert_mysql_oom_event(...)`

This integration should:

- read pivot files produced by the collector
- insert raw events into the raw log table
- update minute/hour/day aggregate tables incrementally

The integration should stay specialized because:

- file-backed logs use inode/offset semantics
- journald-backed OOM events use cursor semantics
- both need dedup and aggregation, but not the same source-handling logic

## 3. Dedicated tables

### Raw event table

Suggested table:

`ssh_log_sqlerror_line`

Suggested columns:

- `id` bigint primary key
- `id_mysql_server` int
- `event_time` datetime(6) null
- `date_inserted` datetime(6) not null
- `log_path` varchar(1024)
- `inode` bigint null
- `offset_start` bigint null
- `offset_end` bigint null
- `user` varchar(255) null
- `server` varchar(255) null
- `message` text
- `raw_line` text
- `sha1_line` char(40) null

Recommended indexes:

- `(id_mysql_server, event_time)`
- `(id_mysql_server, date_inserted)`
- `(id_mysql_server, inode, offset_start)`

The offset index is useful for dedup and operational debugging.

### Additional raw log tables

Suggested parallel tables:

- `ssh_log_slowquery_line`
- `ssh_log_error_line`
- `ssh_log_general_line`

Depending on implementation preference, these may also be unified into:

- `ssh_log_mysql_line`

with:

- `log_type` in (`sql_error`, `slow_query`, `error`, `general`)

Recommended practical choice:

- one unified raw table for file-backed MySQL logs
- one dedicated table for OOM journal incidents

Suggested unified table:

`ssh_log_mysql_line`

Extra columns:

- `log_type`
- `source_kind` (`file`, `journal`)

### OOM incident table

Suggested table:

`ssh_log_mysql_oom_incident`

Suggested columns:

- `id`
- `id_mysql_server`
- `incident_time`
- `date_inserted`
- `unit_name`
- `trigger_process`
- `killed_process`
- `killed_pid`
- `memory_usage_kb`
- `memory_limit_kb`
- `swap_usage_kb`
- `cgroup_name`
- `message_summary`
- `raw_blob`
- `incident_hash`

This table is specifically justified by the observed MariaDB/MyRocks examples:

- `rocksdb:high invoked oom-killer`
- `Memory cgroup stats for /system.slice/mariadb.service`
- `Killed process ... (mariadbd)`

### Aggregate tables

Suggested tables:

- `ssh_log_sqlerror_agg_minute`
- `ssh_log_sqlerror_agg_hour`
- `ssh_log_sqlerror_agg_day`

Equivalent aggregate tables should exist for OOM incidents if we want charts:

- `ssh_log_mysql_oom_agg_minute`
- `ssh_log_mysql_oom_agg_hour`
- `ssh_log_mysql_oom_agg_day`

Columns:

- `id_mysql_server`
- `bucket_start`
- `count_total`

Optional future columns:

- `count_error`
- `count_warning`
- `count_note`

Recommended index:

- `(id_mysql_server, bucket_start)`

## 4. Cache strategy

The aggregate tables are already the main acceleration layer. Add a lightweight app cache on top only for repeated UI queries.

Suggested cache keys:

- `sqlerror:{server_id}:day:{month_window}`
- `sqlerror:{server_id}:hour:{day_window}`
- `sqlerror:{server_id}:minute:{hour_window}`
- `sqlerror:{server_id}:lines:{range}:{page}`

Suggested storage:

- file cache under `tmp/cache/sql-error-log/`

Suggested TTL:

- aggregates: 30-60 seconds
- raw log page: 10-30 seconds

Because the drill-down is range-based and the source is append-only, this cache is easy to invalidate conservatively.

## 5. UI design

Suggested screen:

- `Server/sqlErrorLog/{id_mysql_server}`

Suggested extension:

- tabbed view by source:
  - `SQL error`
  - `Slow query`
  - `Error log`
  - `General log`
  - `OOM killer`

### Top chart

Default:

- rolling last month
- one bar per day

Drill-down:

- click one day => last 24h of that day, one bar per hour
- click one hour => one bar per minute

Chart type:

- Chart.js bar chart

Data source:

- day chart => `agg_day`
- hour chart => `agg_hour`
- minute chart => `agg_minute`

For OOM killer:

- month view can remain one bar per day
- drill-down day => hour
- drill-down hour => minute
- raw incident lines / grouped incident detail below

### Raw logs below

Query the raw table for the selected range only.

Columns:

- datetime
- user
- server
- message

For OOM lines / incidents:

- datetime
- trigger process
- killed process
- cgroup
- summary

Rules:

- default `LIMIT 1000`
- pagination required
- stable ordering by `event_time desc, id desc`

Key point:

- charts must never query raw logs directly
- raw log panel must never scan a month if the user is looking at a day aggregate

## Performance And Scheduling

## Collector frequency

A dedicated collector is preferred.

Recommended schedule:

- every 15s or 30s initially

Recommended split:

- file-backed MySQL logs: every 15s or 30s
- OOM journal events: every 30s or 60s

Why not every 5s immediately:

- SQL error log activity is usually bursty, not constant
- byte-offset reading is cheap, but SSH setup still has a cost
- a dedicated collector avoids adding latency to the main `ssh_stats` cycle

If later needed, it can be moved to 5s for selected servers only.

General log note:

- `general_log` can produce extremely high volume
- it must stay disabled by default, both on the server and in the collector design

## Integration frequency

Keep integration separate and lightweight.

Recommended:

- collector writes raw event payloads frequently
- integrator processes them frequently but independently

This avoids coupling log event ingestion to the larger `ssh_stats` batch.

## Why a dedicated aspirateur helps

If SQL error log collection is mixed into the existing generic SSH batch:

- bigger payloads
- longer SSH tasks
- more chance to delay unrelated SSH metrics

A dedicated collector gives:

- isolated timeout/retry policy
- isolated failure mode
- easier tuning
- better observability

## Edge Cases To Handle

- log disabled
- `stderr` instead of file
- journald-only logging
- file rotation
- file truncation
- server unreachable
- malformed lines
- duplicate reread after network failure
- timezone normalization
- huge bursts of errors
- `SQL_ERROR_LOG` plugin installed but inactive
- `SQL_ERROR_LOG` filename set but file absent
- `general_log_file` present while `general_log=OFF`
- OOM triggered by `rocksdb:high` instead of `mariadbd`
- multiple journal lines for one OOM incident
- cgroup-level OOM lines without immediate kill line in the same poll

Recommended first-version behavior:

- support normal file logs
- support rotation/truncation
- support journald for OOM events from day one
- skip generic journald-only MySQL log ingestion explicitly with a status message
- store raw lines even if parsing is partial

## Recommended Implementation Plan

### Phase 1: collector + raw events

- add dedicated collectors in `Aspirateur`
- add file cursor table
- add journal cursor table
- add unified raw MySQL log table
- add raw OOM incident table
- read file deltas by inode/offset
- read journal deltas by journal cursor
- insert raw lines / incidents

### Phase 2: aggregates

- add minute/hour/day aggregate tables for MySQL logs
- add minute/hour/day aggregate tables for OOM incidents
- update them incrementally during integration

### Phase 3: UI

- month/day/hour bar chart drill-down
- raw log table with pagination
- OOM incident timeline and detail view

### Phase 4: cache and retention

- file cache for repeated queries
- purge policy for raw lines
- longer retention for aggregates

## Final Recommendation

The fastest and safest architecture is:

- **dedicated MySQL log collectors**
- **a dedicated OOM journal collector**
- **dedicated raw event tables**
- **minute/hour/day aggregate tables**
- a **small app cache**

Avoid:

- storing raw log lines in `ts_value_general_json`
- storing OOM journal incidents in `ts_value_general_json`
- querying raw lines directly for charts
- coupling SQL error log collection tightly with generic `ssh_stats`

This gives:

- fast drill-down queries
- cleaner retention
- proper handling of rotation and duplicates
- smaller impact on existing aspirateur workloads

## Sources

- Elastic data streams: https://www.elastic.co/docs/manage-data/data-store/data-streams
- Elastic transforms: https://www.elastic.co/docs/explore-analyze/transforms/transform-overview
- Grafana Loki caching: https://grafana.com/docs/loki/latest/operations/caching/
- ClickHouse incremental materialized views: https://clickhouse.com/docs/materialized-view/incremental-materialized-view
- Splunk index storage: https://docs.splunk.com/Documentation/Splunk/latest/Indexer/HowSplunkstoresindexes
