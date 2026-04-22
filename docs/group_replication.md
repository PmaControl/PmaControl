# Group Replication (InnoDB cluster) & MySQL vs MariaDB

## UI

Page: `/InnoDBCluster/index/` — menu **Architecture > Group Replication**. Rendered by the `InnoDBCluster` controller.

## Role detection (priority order)

1. **`gr_member_role`** — collected by the Aspirateur from
   `performance_schema.replication_group_members WHERE MEMBER_ID = @@server_uuid`.
   Values: `PRIMARY`, `SECONDARY`. Most reliable source.
2. **Fallback** — `super_read_only` / `read_only`: if `gr_member_role` is empty (not yet collected, or server offline), a node with `super_read_only = OFF` is considered `PRIMARY`.

## State detection

Same pattern. `gr_member_state` from `performance_schema` — values `ONLINE`, `RECOVERING`, `ERROR`, `OFFLINE`, `UNREACHABLE` — with fallback to `mysql_available`.

## Dot3 topology

GR clusters render as a subgraph with two sub-groups:

- **Primary** — green `#e8f5e9`, border `#1b5e20`
- **Replica** — blue `#e3f2fd`, border `#1565c0`

Each node shows `GR Role` and `GR State` rows, colored by status (green=PRIMARY, blue=SECONDARY, yellow=RECOVERING, red=ERROR).

## MySQL vs MariaDB syntax — always branch on `$db->getServerType()`

Replication actions (`activateGtid`, `deactivateGtid`, `startSlave`, `stopSlave`, `skipCounter`, `setupSource`, `setParallelThreads`) implemented in `App/Controller/MasterSlave.php`, `Slave.php`, etc., must branch:

| | MariaDB | MySQL 8+ |
|---|---|---|
| Stop replica | `STOP SLAVE 'conn'` | `STOP REPLICA FOR CHANNEL 'conn'` |
| Change source | `CHANGE MASTER … TO MASTER_USE_GTID` | `CHANGE REPLICATION SOURCE TO SOURCE_AUTO_POSITION` |
| Parallel workers | `slave_parallel_threads` | `replica_parallel_workers` |
| Persist | — | `SET PERSIST` (survives restart without editing `my.cnf`) |

## GTID activation (MySQL)

MySQL requires a 4-step `gtid_mode` migration **before** setting `SOURCE_AUTO_POSITION = 1`:

```
OFF  →  OFF_PERMISSIVE  →  ON_PERMISSIVE  →  ON
```

plus `enforce_gtid_consistency = ON`. `activateGtid()` enables GTID on the master first (located via `Mysql::getMaster()`), then on the slave. Uses `SET PERSIST` for durability.

## Skip counter under GTID

`sql_slave_skip_counter` does not work with `gtid_mode = ON` on MySQL. `skipCounter()` detects this and injects an empty transaction:

```sql
SET GTID_NEXT = 'uuid:N';
BEGIN;
COMMIT;
SET GTID_NEXT = 'AUTOMATIC';
```

The GTID to skip is found via `GTID_SUBTRACT()` on `performance_schema`, with fallback to parsing `Executed_Gtid_Set`.
