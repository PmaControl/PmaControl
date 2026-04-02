# Time-Series Aggregation Study For `Control`

## Scope

This study defines a fast aggregation design for PmaControl metrics on the `Control` side, with three additional resolutions:

- `10 seconds`
- `1 minute`
- `1 hour`

And two aggregation behaviors:

- `last value` for monotonic/incrementing metrics and state-like metrics
- `average` for volatile gauge metrics such as `Threads_running`

This is intentionally a design study only. No implementation is proposed here.

Important constraint:

- the design must be based on the current `ts_*` schema and ingestion model already used by PmaControl
- it must not invent a disconnected second telemetry world

## Goal

The goal is to make reads extremely fast for dashboards and long-range graphs without scanning large raw time-series tables every time.

The design target is:

- write once into raw series
- roll up immediately into coarser buckets
- query the smallest sufficient table for the selected time range
- purge old data by retention policy, not by ad hoc deletes

## Existing PmaControl Reality

PmaControl already stores telemetry in `ts_*` structures such as:

- `ts_variable`
- `ts_file`
- `ts_date_by_server`
- raw value tables like:
  - `ts_value_general_int`
  - `ts_value_general_double`
  - `ts_value_general_text`
  - `ts_value_general_json`
  - `ts_value_slave_*`
  - `ts_value_digest_*`
  - `ts_value_calculated_*`

So the aggregate design must be an extension of the current model, not a replacement.

That means:

- aggregate rows should still key on existing `id_ts_variable`
- aggregate rows should still key on existing `id_mysql_server`
- aggregate logic should reuse the current metric catalog in `ts_variable`
- reads should be able to route from a metric definition already known by PmaControl

This is critical because it keeps:

- compatibility with current screens
- compatibility with current extraction logic
- compatibility with current metadata semantics

## Design Constraint From Existing `ts_*`

The aggregation layer should sit logically above the current raw value tables.

Recommended flow:

1. raw values continue landing in the existing `ts_value_*` tables
2. a rollup worker reads only the needed recent range from raw `ts_*`
3. it writes precomputed rows to aggregate tables keyed by:
   - `bucket_start`
   - `id_mysql_server`
   - `id_ts_variable`
4. dashboards query the aggregate tables by default

So the source of truth remains the current `ts_*` family.

## Why Standard Deviation Must Be Stored

You asked to take standard deviation into account as well, even for metrics that are displayed with `last` or `avg`.

That is a good requirement.

Why:

- `avg` alone hides instability
- `last` alone hides how noisy the interval was
- standard deviation allows quick “is this bucket stable or abnormal?” checks

Examples:

- `Threads_running`
  - average `12`
  - standard deviation `0.8`
  - stable

- `Threads_running`
  - average `12`
  - standard deviation `9.5`
  - unstable / spiky

- `memory_used`
  - same average on two days
  - very different standard deviation
  - one day may indicate churn or pressure, the other not

So even when the display value is:

- `last` for counters/states
- `avg` for gauges

the aggregate row should still store dispersion information.

## What Other Systems Do

### Prometheus

Prometheus keeps raw samples in its TSDB and uses recording rules to precompute commonly used derived series. Retention is time-based and/or size-based. It is explicitly not positioned as long-term clustered storage by itself. Official docs also expose `rule_query_offset`, which is relevant when data arrival can lag behind evaluation.

Relevant points:

- recording rules precompute expensive expressions
- retention defaults to `15d`
- time and size retention can both be configured
- block cleanup happens asynchronously
- local TSDB is not meant as the final long-term archive

Design lesson for PmaControl:

- precompute rollups instead of recalculating them at query time
- keep ingestion and read-optimized layers separate
- do not rely on one huge raw table for every graph

Sources:

- https://prometheus.io/docs/practices/rules/
- https://prometheus.io/docs/prometheus/latest/configuration/configuration/
- https://prometheus.io/docs/prometheus/2.55/storage/

### Graphite / Whisper

Graphite is the closest conceptual match to what you want.

It stores multiple retentions per metric and defines how lower-precision archives are computed from higher-precision ones. The key idea is exactly your need:

- one metric can be rolled up with `average`
- another with `sum`
- another with `last`
- and retention is declared upfront

It also exposes `xFilesFactor`, which defines how many underlying points must exist before a coarser point is considered valid.

Design lesson for PmaControl:

- keep aggregation method attached to metric semantics
- keep retention per archive level
- support sparse buckets explicitly
- never mix `sum` and `last` semantics blindly

Sources:

- https://graphite.readthedocs.io/en/1.1.8/config-carbon.html
- https://graphite.readthedocs.io/_/downloads/en/1.1.8/pdf/

### SingleStore

SingleStore recommends:

- hot operational time-series in rowstore while it fits in RAM
- older / larger time-series in columnstore
- key or sort on timestamp
- lifecycle management by aging out old rows

Its docs explicitly describe moving data through lifecycle stages and choosing rowstore vs columnstore based on workload.

Design lesson for PmaControl:

- hot recent aggregates should stay in normal InnoDB/RocksDB tables optimized for point/range reads
- if one day PmaControl needs long-range analytics at very large scale, a colder analytical layer can be considered separately
- timestamp-first access path matters

Sources:

- https://docs.singlestore.com/db/v8.9/developer-resources/functional-extensions/analyzing-time-series-data/
- https://docs.singlestore.com/db/v8.5/create-a-database/choosing-a-table-storage-type/
- https://docs.singlestore.com/db/v8.1/create-a-database/rowstore/
- https://docs.singlestore.com/db/v8.7/create-a-database/creating-a-columnstore-table/

### MariaDB ColumnStore

ColumnStore is strong for analytical partition pruning and lifecycle operations by partition. It is useful when data is truly large and queried analytically, but it is not the best fit for the hottest write path of PmaControl’s operational monitoring loop.

Design lesson for PmaControl:

- partitioning by date is useful
- retention should be implemented as partition lifecycle, not row-by-row cleanup
- do not put hot write-heavy, high-frequency operational aggregates into a design that is optimized first for analytical scans

Sources:

- https://mariadb.com/docs/columnstore/mariadb-columnstore/management/managing-columnstore-database-environment/columnstore-partition-management
- https://mariadb.com/docs/server/server-usage/partitioning-tables/partition-maintenance

## Recommended Model For PmaControl

### Core Decision

Use dedicated aggregate tables in MariaDB for each resolution, updated incrementally, with partitioned retention.

Do not use:

- system versioning
- one giant mixed-resolution table
- on-the-fly aggregation from raw data for normal dashboard reads

### Why

Because the fastest design for PmaControl is:

- append-oriented writes
- short indexed range scans
- deterministic bucket tables
- cheap partition drops for retention

This matches the operational UI better than a generic TSDB emulation.

## Aggregate Values To Store Per Bucket

To stay useful for both visualization and problem detection, each bucket should store more than one value.

Recommended per bucket:

- `sample_count`
- `value_last`
- `value_avg`
- `value_stddev`
- `value_min`
- `value_max`
- `first_ts`
- `last_ts`

### Why each field matters

`sample_count`

- detects sparse buckets
- helps qualify averages

`value_last`

- needed for counters, states, “latest in bucket”

`value_avg`

- needed for gauges and smoothing

`value_stddev`

- needed for anomaly and instability detection

`value_min`, `value_max`

- useful for spike-sensitive dashboards
- optional at UI level, but useful operationally

`first_ts`, `last_ts`

- useful to detect partial buckets and delayed ingestion

## Aggregation Semantics

### Family A: monotonic / incrementing metrics

Examples:

- counters that only grow
- bytes sent / received
- reads / writes
- handler counters
- pages read
- queries total

Rule:

- store the `last raw value` in each bucket
- also store `avg` and `stddev` if you want bucket-quality diagnostics, but the display value remains `last`

Why:

- the bucket value itself is the latest observed counter in that period
- rate / delta can then be computed safely between buckets
- averaging counters is meaningless
- summing raw cumulative counters inside a bucket is wrong

For counters, `stddev` is less about direct chart display and more about “was the bucket smooth or did the collector produce erratic jumps / resets?”

### Family B: volatile gauges

Examples:

- `Threads_running`
- queue depth
- current memory used
- current connections
- pending tasks

Rule:

- store the `average` over the bucket
- also store `last` and `stddev`

Why:

- a gauge represents a level, not an accumulation
- averaging gives a stable visual trend
- taking only the last sample can be too spiky for dashboards

For gauges, `stddev` is highly valuable because it immediately distinguishes:

- stable load
- bursty load
- pathological oscillation

### Family C: state / enum / booleans

Examples:

- `read_only`
- `mysql_available`
- role / mode flags

Rule:

- store `last`
- optionally also store `worst_state` if required by UI semantics

For state metrics, numeric `stddev` is often meaningless unless the state is encoded numerically on purpose. For these metrics:

- keep `last`
- optionally keep `state_bad_count`
- optionally keep `state_change_count`

Do not force a numeric standard deviation where the metric is not numeric.

Why:

- state visualizations often care about the final bucket state
- but some dashboards may want “if one bad sample exists in bucket => bucket bad”

This is exactly what `Server/state` already does conceptually for `mysql_available`.

### Family D: min/max-sensitive gauges

Examples:

- latency spikes
- stall duration
- replication lag peaks

Rule:

- if needed later, store `avg`, `min`, and `max`

But with your requirement for anomaly detection, the right baseline is:

- `avg`
- `stddev`
- `min`
- `max`

for numeric gauges.

## Recommended Schema Strategy

### Do Not Create One Table Per Metric

That would explode DDL count and maintenance overhead.

### Do Not Create One Giant Catch-All With No Semantic Type

That makes aggregation wrong.

### Recommended Shape

Keep one aggregate table per resolution, but add a metric aggregation policy.

Logical model:

- `ts_agg_10s`
- `ts_agg_1m`
- `ts_agg_1h`

Each row keyed by:

- `bucket_start`
- `id_mysql_server`
- `id_ts_variable`

Each row stores:

- `sample_count`
- `value_last`
- `value_avg`
- `value_stddev`
- `value_min` optional
- `value_max` optional
- `first_ts`
- `last_ts`

And each metric has a policy in metadata:

- `aggregation_policy = last | avg`

At query time:

- if metric policy is `last`, read `value_last`
- if metric policy is `avg`, read `value_avg`

For anomaly tooling:

- read `value_stddev`
- optionally compare current bucket stddev to historical baseline

This is fast and flexible.

## Why This Is Better Than Separate `last` And `avg` Tables

Because:

- fewer tables
- one write path
- one retention path
- one query pattern
- metric semantics stay in metadata

The extra columns are cheap compared to the cost of maintaining many alternate structures.

This is even more true once `stddev` enters the design: keeping everything in the same bucket row is much simpler than parallel aggregate stores.

## Resolution Cascade

### 10-second table

This is the hot near-real-time rollup.

Built directly from raw incoming values.

Used for:

- short dashboards
- latest 1h to 24h
- fast detail screens

### 1-minute table

Built from `10-second` aggregates, not from raw data.

Why:

- reduces compute cost
- stabilizes ingestion path
- keeps a single rollup chain

### 1-hour table

Built from `1-minute` aggregates.

Used for:

- weeks to years of history
- overview dashboards

## Retention Recommendation

These values are chosen for operational usefulness and cost balance.

### Recommended retention

- `10s`: `14 days`
- `1m`: `180 days`
- `1h`: `5 years`

### Why

`10s / 14d`

- enough for incident analysis
- still fine-grained for short debugging windows
- beyond that, dashboards rarely need 10-second precision

`1m / 180d`

- ideal for most operational history
- six months is usually enough for trend analysis and recurring incidents

`1h / 5y`

- supports long-term planning and seasonality
- storage cost stays low

### Alternative if you want more aggressive compaction

- `10s`: `7 days`
- `1m`: `90 days`
- `1h`: `3 years`

### Alternative if you want more forensic depth

- `10s`: `30 days`
- `1m`: `365 days`
- `1h`: `5 years`

## Partitioning Recommendation

Partition by time bucket, not by server and not by metric.

### Proposed partitioning

- `ts_agg_10s`: daily partitions
- `ts_agg_1m`: daily partitions
- `ts_agg_1h`: monthly partitions

### Why

`10s` and `1m`

- retention cleanup becomes cheap by day
- hot queries are almost always time-range bounded

`1h`

- hourly data is much smaller
- monthly partitions reduce partition count

### Important caution

MariaDB partitioning has overhead. Official docs remind that some operations open all partitions and there is a hard partition count limit.

So:

- do not over-partition
- keep daily/monthly only
- avoid per-server partitions

Source:

- https://mariadb.com/docs/server/server-usage/partitioning-tables/partition-maintenance

## Indexing Recommendation

For each aggregate table, primary access should be time-range per server and metric.

Recommended primary key order:

- `bucket_start`
- `id_mysql_server`
- `id_ts_variable`

Recommended secondary key:

- `id_mysql_server`
- `id_ts_variable`
- `bucket_start`

Why both:

- partition pruning works well on `bucket_start`
- dashboard reads often start with server + metric + range

## How To Compute Buckets

### 10-second bucket

Bucket start = floor timestamp to nearest 10 seconds.

Examples:

- `12:00:03` -> `12:00:00`
- `12:00:09` -> `12:00:00`
- `12:00:10` -> `12:00:10`

### 1-minute bucket

Bucket start = floor to minute.

### 1-hour bucket

Bucket start = floor to hour.

## Write Path Recommendation

### Best approach

Aggregate during integration, not during dashboard query.

Flow:

1. raw sample inserted
2. determine metric aggregation policy
3. update `10s` bucket
4. asynchronously promote completed `10s` buckets into `1m`
5. asynchronously promote completed `1m` buckets into `1h`

### Why this is fast

- reads hit small precomputed tables
- coarser rollups are cheap because they use already rolled-up data
- write amplification stays bounded and predictable

## Late Data Handling

This matters because PmaControl already has ingestion lag cases.

Recommended rule:

- keep the current bucket mutable
- keep the previous bucket mutable for a short grace period

Example:

- `10s` bucket can still be updated until `+20s`
- `1m` bucket can still be updated until `+2m`

This avoids grey/missing buckets from slightly delayed collectors.

It also preserves statistical quality:

- `avg`
- `stddev`
- `min`
- `max`

remain correct if the late sample lands inside the allowed grace window.

## Null / Sparse Bucket Policy

Take the Graphite idea seriously here.

Recommended:

- if no sample exists in bucket: store no row
- do not materialize artificial zeroes in storage
- let rendering choose whether missing = gap or zero

Exception:

- state dashboards may materialize missing explicitly if the screen semantics require it

For rollup from finer to coarser archives:

- only compute coarser point if at least one child bucket exists

This is conceptually similar to Graphite `xFilesFactor`, but simpler for PmaControl.

## Metric Policy Metadata

This design needs one metadata flag per metric.

Recommended new logical metadata:

- `aggregation_policy`
  - `last`
  - `avg`

- `statistics_policy`
  - `none`
  - `stddev`
  - `stddev_min_max`

Optional later:

- `display_policy`
  - `line`
  - `stack`
  - `state`
- `rate_policy`
  - `counter`
  - `gauge`

This should live near metric definition, not in dashboard code.

For current PmaControl, the natural place is near `ts_variable` semantics, not inside ad hoc controller lists.

## Recommended Initial Mapping

### `last`

Use `last` for:

- cumulative counters
- bytes / packets / operations totals
- booleans / states
- identifiers / positions / LSN-like values

And keep `stddev` only for numeric counters where reset/jitter detection is useful.

### `avg`

Use `avg` for:

- `Threads_running`
- queue sizes
- current memory
- instantaneous concurrency
- temperatures / percentages / current load-like gauges

And keep:

- `stddev`
- `min`
- `max`

for these by default.

## How Standard Deviation Helps Problem Detection

This is one of the most important additions.

### Example 1: stable vs unstable `Threads_running`

Two buckets may both show:

- average `12`

But:

- bucket A: stddev `0.5`
- bucket B: stddev `7.8`

Bucket B indicates concurrency spikes and possible contention.

### Example 2: network or disk counters with resets

For counters, the displayed value may still be `last`, but stddev can help detect:

- collector resets
- noisy sampling
- unusual jump patterns

### Example 3: memory or queue oscillation

Average alone can hide sawtooth behavior.

Stddev exposes:

- churn
- pressure oscillation
- burstiness

### Operational conclusion

If the goal includes problem detection, `stddev` is not optional for numeric gauges.

It should be part of the aggregate row from day one.

## Fast Query Strategy

At read time:

- range `<= 24h` => `10s`
- range `<= 180d` => `1m`
- range `> 180d` => `1h`

This should be deterministic and automatic.

No dashboard should decide to scan raw tables unless explicitly in a forensic/debug mode.

## RocksDB vs InnoDB

For these aggregate tables, prefer the engine already used consistently for your partitioned telemetry tables in PmaControl, but the practical choice should be:

- use the engine with the best current operational stability in your environment
- not the theoretically best engine

Given prior experience in this repo:

- avoid clever features that complicate maintenance
- avoid system versioning here
- keep retention as partition lifecycle

If RocksDB is already the chosen telemetry engine in your installation and behaves well under append/update workloads, it is acceptable. If not, InnoDB is the safer baseline for aggregate tables.

## Recommended Final Design

### Tables

- raw telemetry stays as-is
- add:
  - `ts_agg_10s`
  - `ts_agg_1m`
  - `ts_agg_1h`

### Stored values

Each row stores:

- `sample_count`
- `value_last`
- `value_avg`
- `value_stddev`
- optional `value_min`
- optional `value_max`

### Metadata

Each metric gets:

- `aggregation_policy = last | avg`
- `statistics_policy = none | stddev | stddev_min_max`

### Retention

- `10s`: `14 days`
- `1m`: `180 days`
- `1h`: `5 years`

### Partitions

- `10s`: daily
- `1m`: daily
- `1h`: monthly

### Rollup chain

- raw -> `10s`
- `10s` -> `1m`
- `1m` -> `1h`

### Query routing

- short range -> `10s`
- medium range -> `1m`
- long range -> `1h`

## What I Would Not Do

- no system versioning
- no partition-by-server
- no one-table-per-metric
- no dynamic on-the-fly aggregation for normal dashboards
- no storing synthetic zero rows for every empty bucket
- no direct long-range queries on raw series for standard UI

## Final Recommendation

The closest proven model for your need is Graphite’s archive idea, implemented inside MariaDB with explicit tables and retention by partition.

So the best PmaControl design is:

- Graphite-like rollups
- Prometheus-like precomputation mindset
- SingleStore-like hot/warm lifecycle thinking
- MariaDB-style partition retention

For PmaControl specifically, the most pragmatic and fastest architecture is:

- raw tables for ingestion
- `10s / 1m / 1h` aggregate tables
- metric-level `last` vs `avg`
- daily/monthly partitions
- fixed retention windows
- no runtime recomputation for standard graphs

That gives you predictable writes, cheap retention, and very fast reads.

## Sources

- Prometheus recording rules:
  - https://prometheus.io/docs/practices/rules/
- Prometheus configuration and retention:
  - https://prometheus.io/docs/prometheus/latest/configuration/configuration/
  - https://prometheus.io/docs/prometheus/2.55/storage/
- Graphite retentions and aggregation:
  - https://graphite.readthedocs.io/en/1.1.8/config-carbon.html
  - https://graphite.readthedocs.io/_/downloads/en/1.1.8/pdf/
- SingleStore time-series and storage type:
  - https://docs.singlestore.com/db/v8.9/developer-resources/functional-extensions/analyzing-time-series-data/
  - https://docs.singlestore.com/db/v8.5/create-a-database/choosing-a-table-storage-type/
  - https://docs.singlestore.com/db/v8.1/create-a-database/rowstore/
  - https://docs.singlestore.com/db/v8.7/create-a-database/creating-a-columnstore-table/
- MariaDB partitioning and system-versioned tables:
  - https://mariadb.com/docs/server/server-usage/partitioning-tables/partition-maintenance
  - https://mariadb.com/docs/server/reference/sql-structure/temporal-tables/system-versioned-tables
  - https://mariadb.com/docs/columnstore/mariadb-columnstore/management/managing-columnstore-database-environment/columnstore-partition-management
