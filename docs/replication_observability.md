# Replication observability

Issue #1280 adds a single integrated observability layer around the existing
`/slave/show/<id>/<connection_name>/` page. The implementation reuses the
current replication status payload, existing `ts_value_*` time-series data and
small cache columns on `mysql_server`.

## Page primitives

- Health scorecard: computed by `App\Library\ReplicationHealth` from the
  current channel status, lag SLA, GTID drift, semi-sync, SSL, filters,
  heartbeat and applier state.
- GTID drift: `App\Library\GtidSet` supports MySQL UUID GTID sets and MariaDB
  domain/server/sequence sets. Cached errant sets live on `mysql_server`.
- Multi-channel cards: rendered from the same channel list already used by the
  `/slave/show` tabs.
- Stuck SQL detector: compares stable `Exec_Master_Log_Pos` history with IO
  progress when history is available.
- Filters audit: lists active `replicate_*` filters and raises severity when
  combined with `binlog_format=STATEMENT`.
- Semi-sync ACK SLA: tracks yes/no ACK counters and timeout ratio.
- Retention forecast: uses binlog retention inputs when available.
- Replication user / SSL audit: surfaces SSL disabled or expiring cert state.
- Per-database lag: normalizes worker rows from
  `replication_applier_status_by_worker`.
- Reconnect tracker: parses MySQL error-log reconnect lines and IO-error
  transitions.
- Metadata viewer: `/slave/replicationMetadata/<id>/ajax:true/` dumps the
  relevant `performance_schema.replication_*` and `mysql.slave_*_info` tables
  with sensitive fields masked.
- GTID diff tool: `/Replication/gtidDiff/`.
- Replication-only topology: `/Replication/topology/`.
- Failover pre-flight: read-only JSON check via
  `/slave/failoverPreflight/<id>/<connection>/ajax:true/`.
- Annotations: `replication_annotation` stores operator markers shown under the
  lag chart.

## Storage

`sql/incremental_v2/20260518_replication_observability.sql` adds:

- `mysql_server.errant_gtid_set`
- `mysql_server.errant_gtid_sampled_at`
- `mysql_server.errant_gtid_ignore`
- `mysql_server.replica_lag_sla_seconds`
- `replication_annotation`
- `ts_variable` rows for `slave::gtid_executed`,
  `status::rpl_semi_sync_master_avg_wait_time` and
  `slave::replication_applier_status_by_worker`

## Collection

`Aspirateur::sampleGtidExecuted($id_mysql_server)` reads
`@@global.gtid_executed`, falling back to MariaDB's `gtid_slave_pos` and
`gtid_binlog_pos`. The method returns a regular collector payload so the
existing integration path stores it like other metrics.

## Failover semantics

`App\Library\FailoverPreflight` is read-only. A failed blocker returns
`blocked`; warnings return `risky`; all green returns `ready`. Mutating
promotion workflows are intentionally outside this issue.
