# BLACKHOLE binlog relay (`/Blackhole/index`)

Epic [#1212](https://git.istosia.com/pmacontrol/pmacontrol/issues/1212). Sub-tickets: schema flag (#1213), conversion script (#1214), this doc (#1215), ProxySQL audit (#1216), UI page (#1217).

A **BLACKHOLE binlog relay** is a MySQL / MariaDB node that:

1. replicates from an upstream master,
2. stores every write in the `BLACKHOLE` engine — the row is discarded immediately,
3. re-emits the event into **its own binlog** via `log_slave_updates = ON` / `log_replica_updates = ON`,
4. serves that binlog to downstream slaves, PITR tools, or CDC consumers (Debezium, Maxwell, …).

The result is a very cheap fan-out / archive / CDC node: no InnoDB buffer pool, no row data, only binlogs.

## When to use it

| Use-case | Why a BLACKHOLE relay helps |
|---|---|
| **Fan-out** | One master, one relay, N downstream slaves. The master serves a single Binlog Dump connection instead of N. |
| **Long binlog archive** | `expire_logs_days = 365` on the relay without bloating the primary. Feeds PITR and `mysqlbinlog --read-from-remote-server`. |
| **CDC isolation** | Debezium / Maxwell hit the relay; a noisy consumer never touches the real master. |
| **Filter / transform** | `replicate-do-db`, `replicate-rewrite-db`, `binlog-row-image=MINIMAL` to produce a derived stream. |

## Configuration

Both pipelines apply these `SET GLOBAL` values; for persistence, also add them to `my.cnf`:

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
super_read_only          = ON           # MySQL only; MariaDB ignores
gtid_mode                = ON           # MySQL — pair with enforce_gtid_consistency=ON
# gtid_strict_mode       = ON           # MariaDB
```

**Why `binlog_format = ROW` is mandatory** — in STATEMENT mode, `UPDATE t SET … WHERE col=(SELECT …)` on a BLACKHOLE table hits an empty set; the replicated statement also returns empty downstream, and the chain desyncs silently. In ROW mode the before/after images are injected verbatim into the relay's binlog, which is exactly what downstream consumers need.

## Two pipelines, one runner

Both pipelines are surfaced on `/Blackhole/index` and dispatched by the column `blackhole_conversion.provision_mode`. The same CLI runner executes either:

```bash
php App/Webroot/index.php Blackhole runConvertCli <conversion_id>
```

| `provision_mode` | Use when | Entry point in `BlackholeRelay` |
|---|---|---|
| `convert`    | Target is **already** a replicating slave you want to repurpose | `runConvert($row)` |
| `greenfield` | Target is a **fresh empty** node; you only know its future master | `runGreenfield($row)` |

## `sql_log_bin = 0` — non-negotiable for every relay-side DDL

The relay runs with `log_slave_updates = ON`. Any DDL we execute locally would land in **the relay's own binlog** and be replayed by every downstream slave (now or later). For a BLACKHOLE conversion, that would propagate the `ALTER … ENGINE=BLACKHOLE` to every downstream — disaster.

Both pipelines therefore wrap every relay-side DDL in a session with `sql_log_bin = 0`, **and actively verify** the variable's value, not just trust the SET:

1. `SET SESSION sql_log_bin = 0` (fail-closed: if the SET errors, abort).
2. `SHOW VARIABLES LIKE 'sql_log_bin'` → must return `OFF`. The value is surfaced verbatim in the progress log. (Some MariaDB builds silently refuse the SET inside an open transaction with `gtid_strict_mode = ON`.)
3. **Before every individual `ALTER`**, re-issue `SHOW VARIABLES LIKE 'sql_log_bin'`. If the value drifts to anything other than `OFF`, the run aborts on the spot.
4. `SET SESSION sql_log_bin = 1` in a `finally` block (Sgbd::sql() may pool / reuse the connection — leaving sql_log_bin=0 would be wrong for the next caller).

For the greenfield restore phase, the receiving `mysql` client also gets `--init-command='SET sql_log_bin=0'` so the CREATE TABLEs streamed from `mysqldump` never enter the relay's binlog.

Progress-log excerpt:

```
SET SESSION sql_log_bin = 0 — keep ALTERs out of the relay binlog
SHOW VARIABLES LIKE 'sql_log_bin' → OFF — ok                       ← global post-SET proof
[sql_log_bin=OFF] ALTER TABLE `db1`.`t1` ENGINE=BLACKHOLE — ok     ← per-ALTER re-check
[sql_log_bin=OFF] ALTER TABLE `db1`.`t2` ENGINE=BLACKHOLE — ok
…
SET SESSION sql_log_bin = 1 — restore default
```

## Pipeline A — convert an existing slave in place

UI entry: Tools → **BLACKHOLE** → *Candidate servers* → **Convert to BLACKHOLE** (or **Dry-run**).

Refuses if the target has no configured replication channel — for an empty node use Pipeline B.

Steps (`BlackholeRelay::runConvert($row)`):

1. Load conversion row + target server.
2. Open `Sgbd::sql($server['name'])` (same alias the rest of PmaControl already uses for `SHOW SLAVE STATUS`).
3. **Pre-condition** — assert target is a replica.
4. `STOP ALL SLAVES` / `STOP REPLICA` / `STOP SLAVE` (whichever the target accepts).
5. Enumerate non-system tables.
6. `SET SESSION sql_log_bin = 0` + verify (see section above).
7. Per table, with per-ALTER `sql_log_bin` re-check: `ALTER TABLE … ENGINE=BLACKHOLE`.
8. `SET SESSION sql_log_bin = 1` (in `finally`).
9. `SET GLOBAL default_storage_engine = 'BLACKHOLE'` + `binlog_format = 'ROW'` + `read_only = ON` + `super_read_only = ON` (best-effort; MariaDB has no super_read_only and silently errors — non-fatal).
10. `UPDATE mysql_server SET is_binlog_relay = 1 WHERE id = ?`.
11. `START ALL SLAVES` / `START REPLICA` / `START SLAVE`.

## Pipeline B — provision a relay from scratch (greenfield)

UI entry: Tools → **BLACKHOLE** → *Create new BLACKHOLE relay from scratch* → pick target + master.

Steps (`BlackholeRelay::runGreenfield($row)`):

1. Open `Sgbd::sql()` for both target and master.
2. **`assertTargetIsIdle()`** — refuse if either:
   - `information_schema.tables` has any row outside `mysql / sys / information_schema / performance_schema`,
   - or `information_schema.processlist` has any non-monitoring session (the monitoring allowlist is built from every distinct `mysql_server.login`, plus `root / mariadb.session / mysql.session`).
   The `/Blackhole/probeIdle/<id>/` endpoint runs this check live for the UI dropdown so the operator gets a green ✓ or red ✗ with the reason **before** clicking "Provision relay".
3. `SHOW MASTER STATUS` on the master. Refuse if binary logging is off.
4. **Schema-only dump**: `mysqldump --defaults-extra-file=<tmp> --no-data --routines --triggers --events --single-transaction --master-data=2 --all-databases --ignore-database=mysql --ignore-database=sys --ignore-database=performance_schema --ignore-database=information_schema 2>err | mysql --defaults-extra-file=<tmp> --init-command='SET sql_log_bin=0'`. Two `--defaults-extra-file` temp files isolate the passwords from the command line; `--init-command` keeps the CREATE TABLEs out of the relay's binlog.
5. Enumerate the freshly-created tables on the target.
6. Convert all of them with the same guarded loop as Pipeline A (steps 6-8 above).
7. `CHANGE MASTER TO MASTER_HOST='<ip>', MASTER_PORT=<port>, MASTER_USER='<user>', MASTER_PASSWORD='<pwd>', MASTER_LOG_FILE='<file>', MASTER_LOG_POS=<pos>, MASTER_CONNECT_RETRY=10` using the position captured at step 3. The progress log redacts the password (`MASTER_PASSWORD='********'`).
8. Same runtime config + inventory flag as Pipeline A (steps 9-10).
9. `START SLAVE`.

The replication user defaults to the master's `mysql_server.login`; override via the UI text input. The password is the one PmaControl already stores (and decrypts) for the master.

## CLI

Either pipeline is reachable from a shell once the conversion row exists. The UI inserts it for you; to craft one by hand:

```sql
-- Pipeline A
INSERT INTO blackhole_conversion
    (id_mysql_server, status, dry_run, provision_mode, started_by, progress, error_message, created_at)
VALUES
    (<target_id>, 'pending', 1, 'convert', 'cli', '[]', '', NOW());

-- Pipeline B
INSERT INTO blackhole_conversion
    (id_mysql_server, id_mysql_server__master, replication_user,
     status, dry_run, provision_mode, started_by, progress, error_message, created_at)
VALUES
    (<target_id>, <master_id>, 'repl',
     'pending', 1, 'greenfield', 'cli', '[]', '', NOW());

-- then:
-- php App/Webroot/index.php Blackhole runConvertCli <inserted_id>
```

`dry_run = 1` exercises the whole pipeline including SHOW VARIABLES proofs but skips the real `ALTER`, `CHANGE MASTER`, and `mysqldump | mysql` mutations.

## Database surface

`sql/incremental_v2/20260512_blackhole_relay.sql` and `…_blackhole_greenfield.sql` add:

- `mysql_server.is_binlog_relay TINYINT(1) NOT NULL DEFAULT 0`,
- table `blackhole_conversion` (one row per run, JSON progress, status enum, table counts, error_message, timestamps),
- `blackhole_conversion.provision_mode ENUM('convert','greenfield')`,
- `blackhole_conversion.id_mysql_server__master` (greenfield target's master),
- `blackhole_conversion.replication_user` (optional override),
- `menu` row for the Tools → BLACKHOLE entry (idempotent nested-set update).

## Endpoints

| Method  | Route                                                     | Purpose |
|---------|-----------------------------------------------------------|---------|
| `GET`   | `/Blackhole/index`                                        | Page (relays / candidates / greenfield form / history) |
| `POST`  | `/Blackhole/startConvert/<target_id>/ajax:true/`          | Kick off Pipeline A |
| `POST`  | `/Blackhole/startGreenfield/<target_id>/<master_id>/ajax:true/` | Kick off Pipeline B |
| `GET`   | `/Blackhole/probeIdle/<target_id>/ajax:true/`             | Live idleness preflight for the greenfield dropdown |
| `GET`   | `/Blackhole/status/<conversion_id>/ajax:true/`            | Poll one conversion row (status + progress JSON) |
| CLI     | `php App/Webroot/index.php Blackhole runConvertCli <id>`  | Same dispatcher the UI forks in the background |

All AJAX URLs are suffixed with `/ajax:true/` so `Bootstrap.php` skips the DEBUG footer that would otherwise corrupt the JSON.

## Monitoring after conversion

Keep:

- **lag** — `/slave/show` polls every 5 s (#1207).
- **binlog disk growth** — disk usage of `/var/log/mysql`.
- **`purged_gtid` gap** — the relay's purge horizon must stay ahead of every downstream's start position.
- **replication IO / SQL state** — same alerts as a normal slave.

Disable (noisy or meaningless on a relay):

- **InnoDB buffer pool hit ratio** — there is no InnoDB activity.
- **table sizes** — every table is empty.
- **fragmentation / OPTIMIZE TABLE** — no-op.

## Pitfalls

- **DDL replayed from the master** — `CREATE TABLE … ENGINE=InnoDB` arriving via replication will create an InnoDB on the relay (the master's DDL has its own `sql_log_bin = ON` semantics). Overhead is low (no rows flow into it), but if it matters, schedule a post-DDL `ALTER ENGINE=BLACKHOLE`.
- **Triggers** — do not fire on BLACKHOLE writes in ROW mode. Their effects are already in the binlog as ROW events from the master.
- **Foreign keys** — silently ignored by BLACKHOLE. The relay gives zero integrity guarantee, so `read_only = ON` (+ `super_read_only` on MySQL) is non-negotiable.
- **AUTO_INCREMENT** — not generated on BLACKHOLE. Never write to the relay as a client; downstream is always via replication.
- **Performance Schema** — `events_statements_*` show almost nothing useful (everything is in `Slave_worker`). Skip perfschema-based dashboards on a relay.
- **`super_read_only` on MariaDB** — the variable does not exist. The `SET GLOBAL super_read_only = ON` step errors silently; not fatal, but worth knowing.

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
- Issue [#1218](https://git.istosia.com/pmacontrol/pmacontrol/issues/1218) — diagnostic on slave 222 (`slave_parallel_mode='none'` with 15 workers unused) — illustrates exactly the kind of node that would benefit from a BLACKHOLE relay in front.
