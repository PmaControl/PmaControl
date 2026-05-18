# System-versioned partitions — `PARTITION(pn)` hint convention

PmaControl runs several MariaDB tables with both **`SYSTEM VERSIONING`** and a
**`PARTITION BY SYSTEM_TIME`** layout (`PARTITIONS 2`: `p0` = history,
`pn` = current). This document explains why the codebase sprinkles an explicit
`PARTITION(pn)` hint after the table name in `FROM` / `JOIN`, when *not* to do
it, and how to verify the planner is doing what we want.

See also: [database_conventions.md](database_conventions.md).

## 1. What `PARTITION BY SYSTEM_TIME` actually does

A system-versioned table keeps every version of every row. When you `UPDATE`
or `DELETE`, the previous version is not gone — it gets `ROW_END` stamped
with `NOW(6)` and stays in the table as an invisible historical row.
A normal `SELECT … FROM t` only sees rows whose `ROW_END` is the magic
`MAX_DATETIME6` (i.e. "still current").

`PARTITION BY SYSTEM_TIME PARTITIONS 2` splits the storage:

- **`p0`** — historical rows (`ROW_END < MAX_DATETIME6`)
- **`pn`** — the current set (`ROW_END = MAX_DATETIME6`)

The optimiser is *usually* smart enough to prune `p0` when the query has no
`FOR SYSTEM_TIME` clause, because hidden `ROW_END` predicates pin it to `pn`.
But "usually" is not "always".

## 2. Why PmaControl puts the hint explicitly

`ssh_tunnel` has **76 200 624 rows in `p0`** and **49 rows in `pn`**. Even a
full table scan over 49 rows is sub-millisecond; a scan over 76 M rows is
seconds, blocks the connection cache, and fills the InnoDB buffer pool with
garbage. Reasons to write `FROM ssh_tunnel PARTITION(pn)` rather than rely
on auto-pruning:

1. **Faster plan time** — the optimiser doesn't even consider `p0`.
2. **Defensive against rewrites** — wrap the query in a subquery, a view,
   `UNION`, derived-table optimisation, etc., and the implicit pruning may
   be silently dropped. The hint survives.
3. **Self-documenting** — a developer reading the line sees the intent
   ("live row only") immediately.
4. **Immune to session variables** — `SET SESSION
   system_versioning_asof = '<date>'` will otherwise widen *every* read on
   the connection. With `PARTITION(pn)` the read stays scoped.

## 3. How to write a live query

```sql
SELECT id, dns, port
FROM   alias_dns PARTITION(pn)
WHERE  is_from_ssh = 1;
```

Joins between two partitioned tables get the hint on both sides:

```sql
SELECT t.*, m.display_name
FROM   ssh_tunnel   PARTITION(pn) t
LEFT   JOIN mysql_server PARTITION(pn) m ON t.id_mysql_server = m.id
WHERE  t.date_end IS NULL;
```

## 4. How to write a historical query

When you want to walk history, use `FOR SYSTEM_TIME`. Do **not** add
`PARTITION(pn)` — the two are mutually exclusive in intent:

```sql
-- snapshot at a given point in time
SELECT * FROM global_variable
FOR SYSTEM_TIME AS OF TIMESTAMP '2026-01-01 00:00:00'
WHERE id_mysql_server = 12;

-- full history
SELECT *, ROW_START, ROW_END FROM global_variable
FOR SYSTEM_TIME ALL
WHERE id_mysql_server = 12
ORDER BY ROW_START;
```

The KPI variable-diff query in
`App/Library/Kpi/KpiServerDrilldown.php` is a good example of mixing the
two patterns in one statement: the "today" side carries `PARTITION(pn)`,
the "yesterday" side carries `FOR SYSTEM_TIME AS OF`.

## 5. Confirming the prune worked

```sql
EXPLAIN PARTITIONS
SELECT * FROM ssh_tunnel PARTITION(pn) WHERE date_end IS NULL;
```

The `partitions` column must show `pn` (and only `pn`). If it shows
`p0,pn` or just `p0`, the hint was either dropped (subquery rewrite) or
the table was not what we thought.

`SHOW CREATE TABLE <t>` should contain `PARTITION BY SYSTEM_TIME` — that
is the marker for "this hint is legal". For `PARTITION BY RANGE`, `HASH`
or `LIST` tables (e.g. the `ts_value_*` time-series shards) the partition
names are different (`p_<year>_<week>` etc.) and the hint convention does
not apply.

## 6. When NOT to use `PARTITION(pn)`

- **DML against the table itself.** `UPDATE`, `DELETE`, `INSERT` must not
  restrict to `pn` — the storage engine handles the partition routing
  internally, and an explicit hint can silently strip historical rows.
- **Intentional history walks.** Anything with `FOR SYSTEM_TIME ALL`,
  `FOR SYSTEM_TIME AS OF`, `FOR SYSTEM_TIME BETWEEN`, or anything that
  manually filters `row_start BETWEEN …` to time-travel a dot3 / audit
  page (e.g. `App/Controller/Dot3.php` with `$date_request`).
- **A `SELECT` used as a sub-query of `DELETE … WHERE id IN (SELECT …)`**
  whose intent is to harvest historical IDs — but for current-row
  purges we *do* keep the hint on the sub-SELECT (see
  `App/Controller/Listener.php::script_global_variable`).

## 7. PmaControl tables under SYSTEM_TIME partitioning

The full list, today (verify with the SQL at the end of this doc):

| Table            | Primary key | Live-query WHERE clause                                      | Notes                                              |
|------------------|-------------|--------------------------------------------------------------|----------------------------------------------------|
| `alias_dns`      | `id`        | (no filter — just `PARTITION(pn)`)                           | DNS / port aliases per `id_mysql_server`           |
| `global_variable`| `id`        | `id_mysql_server = ?` and/or `variable_name = ?`             | `SHOW GLOBAL VARIABLES` cache, populated by Aspirateur |
| `mysql_server`   | `id`        | `is_deleted = 0` and/or `id = ?`                             | Inventory; only 1 371 history rows today           |
| `ssh_tunnel`     | `id`        | `date_end IS NULL` and optionally `id_mysql_server IS NULL`  | 76 M history rows; **biggest win** for the hint    |
| `vip_server`     | `id`        | `id_mysql_server = ?` or `dns = ?`                           | VIP route table, populated by Aspirateur           |

## 8. Schema-migration pattern

`ALTER TABLE` on a system-versioned table preserves history *only* when
the session knob is set:

```sql
SET @@SESSION.system_versioning_alter_history = KEEP;
ALTER TABLE mysql_server ADD COLUMN new_flag TINYINT(1) NOT NULL DEFAULT 0;
```

Without `KEEP`, MariaDB rewrites the history rows to fit the new schema
and *can* drop rows whose old column layout does not survive (e.g.
`NOT NULL` adds, type narrowing). In `sql/incremental_v2/` every migration
that touches one of the five tables above must set `KEEP` first.

When the migration adds *and* renames partitions (rare — you'd be
re-numbering history), do it inside a single transaction:

```sql
SET @@SESSION.system_versioning_alter_history = KEEP;
START TRANSACTION;
ALTER TABLE ssh_tunnel ADD PARTITION (PARTITION p1 HISTORY);
ALTER TABLE ssh_tunnel REORGANIZE PARTITION p0 INTO (PARTITION p0 HISTORY);
COMMIT;
```

## 9. Verifying the list

```sql
SELECT t.TABLE_NAME
FROM   information_schema.tables       t
JOIN   information_schema.partitions   p USING (TABLE_SCHEMA, TABLE_NAME)
WHERE  t.TABLE_SCHEMA = 'pmacontrol'
  AND  t.CREATE_OPTIONS LIKE '%partitioned%'
  AND  p.PARTITION_NAME IN ('p0','pn')
GROUP BY t.TABLE_NAME
HAVING COUNT(DISTINCT p.PARTITION_NAME) = 2
ORDER BY t.TABLE_NAME;
```

Re-run this query whenever a migration introduces a new
`WITH SYSTEM VERSIONING PARTITION BY SYSTEM_TIME PARTITIONS 2` table — the
new entry needs the `PARTITION(pn)` audit pass.

## 10. Where the audit lives in the codebase

Search the codebase for the convention with:

```bash
grep -rIn -E 'PARTITION\s*\(\s*pn\s*\)' App/Controller/ App/Library/
```

Files that currently apply the hint (May 2026):

- `App/Controller/Tunnel.php` — all live tunnel reads
- `App/Controller/Aspirateur.php` — inventory + SSH key look-ups
- `App/Controller/Audit.php` — `buildConfigDiffSql`, server look-up,
  dot3-cluster JOIN
- `App/Controller/MaxScale.php`, `App/Controller/MysqlRouter.php` — alias
  + tunnel maps
- `App/Controller/Slave.php` — `getTunnelDetails` (the reload-from-master
  pipeline is patched separately)
- `App/Controller/Variable.php`, `App/Controller/Listener.php`,
  `App/Controller/CheckConfig.php`, `App/Controller/Alias.php` —
  variable / alias index pages
- `App/Library/Display.php`, `App/Library/Galera.php`,
  `App/Library/Kpi/KpiServerDrilldown.php`, `App/Library/BinlogAnalyzer.php`,
  `App/Library/Mysql.php` — shared helpers
- `App/Controller/Dot3.php`, `App/Controller/Server.php` — single-row
  look-ups

DML statements and `FOR SYSTEM_TIME …` walks deliberately keep the
default routing.
