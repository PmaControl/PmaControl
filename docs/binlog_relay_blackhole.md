# BLACKHOLE binlog relay (`/Blackhole/index`)

Epic [#1212](https://git.istosia.com/pmacontrol/pmacontrol/issues/1212). Lots: schema (#1213), conversion script (#1214), this doc (#1215), ProxySQL audit (#1216), UI (#1217).

A **BLACKHOLE binlog relay** is a MySQL/MariaDB node that:

1. replicates from an upstream master,
2. stores every write in the `BLACKHOLE` engine — the row is discarded immediately,
3. re-emits the event into **its own binlog** via `log_slave_updates=ON` / `log_replica_updates=ON`,
4. serves that binlog to downstream slaves, PITR tools, or CDC consumers (Debezium, Maxwell, …).

The result is a very cheap fan-out / archive / CDC node: no InnoDB buffer pool, no row data, only binlogs.

## When to use it

| Use-case | Why a BLACKHOLE relay helps |
|---|---|
| **Fan-out** | One master, one relay, N downstream slaves. The master serves a single Binlog Dump connection instead of N. |
| **Long binlog archive** | `expire_logs_days=365` on the relay without bloating the primary. Feeds PITR and `mysqlbinlog --read-from-remote-server`. |
| **CDC isolation** | Debezium / Maxwell hit the relay; a noisy consumer never touches the real master. |
| **Filter / transform** | `replicate-do-db`, `replicate-rewrite-db`, `binlog-row-image=MINIMAL` to produce a derived stream. |

## Configuration

The conversion script applies these `SET GLOBAL` values; for persistence, add them to `my.cnf`:

```ini
[mysqld]
server_id                = <unique>
default_storage_engine   = BLACKHOLE
log_bin                  = /var/log/mysql/binlog
log_slave_updates        = ON           # MariaDB / MySQL < 8.4
# log_replica_updates    = ON           # MySQL >= 8.4
binlog_format            = ROW          # mandatory — see "Pitfalls"
binlog_row_image         = FULL
expire_logs_days         = 30
sync_binlog              = 1
read_only                = ON
super_read_only          = ON
gtid_mode                = ON           # MySQL — pair with enforce_gtid_consistency=ON
# gtid_strict_mode       = ON           # MariaDB
```

**Why `binlog_format=ROW` is mandatory** — in STATEMENT mode, `UPDATE t SET … WHERE col=(SELECT …)` on a BLACKHOLE table hits an empty set; the replicated statement also returns empty downstream, and the chain desyncs silently. In ROW mode the before/after images are injected verbatim into the relay's binlog, which is exactly what downstream consumers need.

## Table conversion

The conversion script enumerates `information_schema.tables` and runs an `ALTER TABLE … ENGINE=BLACKHOLE` per row, excluding the system schemas (`mysql`, `sys`, `performance_schema`, `information_schema`).

To preview manually:

```sql
SELECT CONCAT('ALTER TABLE `', table_schema, '`.`', table_name, '` ENGINE=BLACKHOLE;')
FROM information_schema.tables
WHERE table_type = 'BASE TABLE'
  AND table_schema NOT IN ('mysql','sys','performance_schema','information_schema')
  AND COALESCE(engine, '') <> 'BLACKHOLE';
```

## How to run

There are **two pipelines**, both routed through the same CLI runner.

### Pipeline A — convert an existing slave in place

Use this when the target is already a configured slave (CHANGE MASTER done, replication running) and you want to repurpose it.

- UI: Tools → **BLACKHOLE** → *Candidate servers* → **Convert to BLACKHOLE** (or **Dry-run**).
- The pipeline refuses if the target has no configured replication channel.

### Pipeline B — provision a relay from scratch (greenfield)

Use this when the target is a fresh empty node and you only know its future master.

- UI: Tools → **BLACKHOLE** → *Create new BLACKHOLE relay from scratch* → pick target + master.
- Refuses if the target has any non-system table or any non-monitoring session (live preflight via `/Blackhole/probeIdle/<id>/`).
- `mysqldump --no-data --routines --triggers --events --single-transaction --master-data=2 --all-databases` on the master, piped into `mysql` on the target. Schema only — BLACKHOLE drops the rows anyway, and the binlog stream from `--master-data=2`'s recorded position replays the rest.
- `ALTER TABLE … ENGINE=BLACKHOLE` on every non-system table.
- `CHANGE MASTER TO MASTER_HOST=…, MASTER_LOG_FILE=…, MASTER_LOG_POS=…` using that position.
- `SET GLOBAL default_storage_engine='BLACKHOLE'`, `binlog_format='ROW'`, `read_only=ON`, `super_read_only=ON`.
- `UPDATE mysql_server SET is_binlog_relay = 1`.
- `START SLAVE`.

The replication user defaults to the master's `mysql_server.login`; override via the UI input. The password is the one PmaControl already stores (and decrypts) for the master.

### CLI

The UI button forks the same runner. From a shell:

```bash
php App/Webroot/index.php Blackhole runConvertCli <conversion_id>
```

The conversion row must already exist (`blackhole_conversion`); the UI inserts it for you. To craft one by hand for pipeline A:

```sql
INSERT INTO blackhole_conversion
    (id_mysql_server, status, dry_run, provision_mode, started_by, progress, error_message, created_at)
VALUES
    (<id>, 'pending', 1, 'convert', 'cli', '[]', '', NOW());
```

For pipeline B (greenfield):

```sql
INSERT INTO blackhole_conversion
    (id_mysql_server, id_mysql_server__master, replication_user,
     status, dry_run, provision_mode, started_by, progress, error_message, created_at)
VALUES
    (<target_id>, <master_id>, 'repl',
     'pending', 1, 'greenfield', 'cli', '[]', '', NOW());
-- then: php App/Webroot/index.php Blackhole runConvertCli <inserted_id>
```

## `sql_log_bin = 0` — non-negotiable for every DDL on the relay

Both pipelines wrap their DDL with `SET SESSION sql_log_bin = 0` before any `ALTER TABLE … ENGINE=BLACKHOLE` (and the greenfield `mysql` client uses `--init-command='SET sql_log_bin=0'` for the `mysqldump | mysql` restore). Reason:

- The relay runs with `log_slave_updates = ON` so the events it receives from its master are forwarded into its own binlog for downstream consumers.
- Without `sql_log_bin = 0`, the `ALTER … ENGINE=BLACKHOLE` we run would land in that same binlog. Any slave connecting downstream — now or later — would replay it and convert its own tables to BLACKHOLE. Disaster.
- Same goes for the `CREATE TABLE … ENGINE=InnoDB` statements injected by the dump-restore phase: they describe the master's schema and must not appear in the relay's downstream stream (the downstream master is the relay's upstream master, which already produced those events at its own positions).

You'll see two extra steps in the progress log around the convert loop:
`SET SESSION sql_log_bin = 0 — keep ALTERs out of the relay binlog` and the matching `SET SESSION sql_log_bin = 1 — restore default`.

## Pipeline A — convert in place (what the script does)

1. Load conversion row + target server.
2. Open a `Sgbd::sql($server['name'])` connection (same alias used by SHOW SLAVE STATUS).
3. **Pre-condition** — refuse if the target has no configured replication channel. A BLACKHOLE node only makes sense as a downstream relay.
4. `STOP ALL SLAVES` / `STOP REPLICA` / `STOP SLAVE` (whichever the target accepts).
5. Discover non-system tables.
6. `ALTER TABLE … ENGINE=BLACKHOLE` on each table.
7. `SET GLOBAL default_storage_engine = 'BLACKHOLE'` + `binlog_format = 'ROW'` + `read_only = ON` + `super_read_only = ON`.
8. `UPDATE mysql_server SET is_binlog_relay = 1 WHERE id = ?`.
9. `START ALL SLAVES` / `START REPLICA` / `START SLAVE`.

Each step writes to `blackhole_conversion.progress` (JSON array of `{key, message, status, time_start, time_end}`).

## Monitoring after conversion

Keep these checks on a relay:

- **lag** — `/slave/show` still polls every 5 s (#1207).
- **binlog disk growth** — disk usage of `/var/log/mysql`.
- **`purged_gtid` gap** — make sure the relay's purge horizon is later than every downstream's start position.
- **replication IO/SQL state** — same alerts as a normal slave.

Disable (they will be noisy or meaningless):

- **InnoDB buffer pool hit ratio** — there is no InnoDB activity.
- **table sizes** — every table is empty.
- **fragmentation / OPTIMIZE TABLE** — no-op.

## Pitfalls

- **DDL** — `CREATE TABLE … ENGINE=InnoDB` arriving via replication will create an InnoDB on the relay. Overhead is low (no data flows into it), but if it matters, schedule a post-DDL `ALTER ENGINE=BLACKHOLE`.
- **Triggers** — do not fire on BLACKHOLE writes in ROW mode. Their effects are already in the binlog as ROW events from the master.
- **Foreign keys** — silently ignored by BLACKHOLE. The relay gives zero integrity guarantee, so `read_only=ON` / `super_read_only=ON` are non-negotiable.
- **AUTO_INCREMENT** — not generated on BLACKHOLE. Never write to the relay as a client; downstream is always via replication.
- **Performance Schema** — `events_statements_*` show almost nothing useful (everything is in `Slave_worker`). Skip perfschema-based dashboards on a relay.

## Rollback

The MVP script does not auto-rollback. To revert a relay:

1. `STOP SLAVE`.
2. From a previous `mysqldump --no-data --routines` of the source: `mysql < schema.sql` (DDL-only — recreates InnoDB tables empty).
3. `START SLAVE` and let it backfill from the master's binlog.
4. `UPDATE mysql_server SET is_binlog_relay = 0`.

A proper rollback path (DDL snapshot at conversion time + replay) is tracked as a follow-up to Lot 2 (#1214).

## Related

- Lot 4 (#1216) — ProxySQL audit rule that flags a relay erroneously routed in a read hostgroup.
- Lot 5 (#1217) — UI page (this page), badge on `/slave/show`, KPI filter on `/Home/index`.
- Issue [#1207](https://git.istosia.com/pmacontrol/pmacontrol/issues/1207) — live polling of Binlog read + Relay exec positions on `/slave/show`.
