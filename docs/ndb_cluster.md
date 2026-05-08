# MySQL NDB Cluster support — operator guide

PmaControl natively models MySQL NDB Cluster topologies (Epic #799). One
`ndb_cluster` row carries the connection details to the management node
(`ndb_mgmd`), `ndb_node` rows carry per-member state collected by two
collectors, and the SQL/API endpoints stay represented as standard
`mysql_server` rows so existing dashboards keep working.

## Data model

| Table | Role |
|---|---|
| `ndb_cluster` | One row per cluster — display name, mgmd host/port, ssh wrapper, latest version. |
| `ndb_node` | One row per member (mgmd / data / sql) — node id, role, ip, status, node group, memory snapshot. |
| `ndb_cluster__mysql_server` | Operator-managed link from a cluster to the SQL/API `mysql_server` rows. |
| `mysql_server.is_ndb_cluster_node` | Flag on SQL/API endpoints, complements `is_proxysql / is_maxscale / …`. |

The schema lives in `sql/incremental_v2/20260507_ndb_cluster_schema.sql`
and `20260507_ndb_cluster_collector_columns.sql`.

## Onboarding a new cluster

1. **Insert the cluster row.** Set the connection details to the mgmd —
   either a direct `ndb_mgm` invocation or a wrapper (the lab uses
   `pct exec` on the Proxmox host because the mgmd runs in an LXC):

   ```sql
   INSERT INTO ndb_cluster (display_name, mgmd_host, mgmd_port,
       ssh_target_host, ssh_target_port, ssh_login, command_template)
   VALUES ('ndb84', '10.68.68.244', 1186,
           '',   22, 'root',
           'ndb_mgm -h {{mgmd_host}}:{{mgmd_port}} -e show');
   ```

   The `{{mgmd_host}}` and `{{mgmd_port}}` placeholders in
   `command_template` are substituted by `NdbMgmShowFetcher`. To wrap the
   call in SSH, set `ssh_target_host` and use a template such as
   `ssh root@host pct exec 290 -- ndb_mgm -h {{mgmd_host}}:{{mgmd_port}} -e show`.

2. **Onboard the SQL/API endpoint(s)** as standard `mysql_server` rows
   (the same way you onboard any MySQL/MariaDB instance), then mark them
   as cluster members and link them to the cluster:

   ```sql
   UPDATE mysql_server SET is_ndb_cluster_node = 1 WHERE id = 267;
   INSERT INTO ndb_cluster__mysql_server (id_ndb_cluster, id_mysql_server)
   VALUES (1, 267);
   ```

3. **Run the collectors** (or wait for the next Aspirateur cycle — see
   below). Within ~60 s the cluster appears on `/architecture/index`
   and a dedicated page becomes available at `/NdbCluster/index/<id>`.

## Collectors

Two collectors feed the model — they're complementary:

| Service | Source | Frequency | Updates |
|---|---|---|---|
| `App\Service\Ndb\NdbCollector` | `ndb_mgm -e show` (parsed by `App\Library\Ndb\MgmShowParser`) | every cluster cycle | `ndb_node` topology + `ndb_cluster.ndb_version` |
| `App\Service\Ndb\NdbInfoCollector` | `ndbinfo.memoryusage` + `ndbinfo.nodes` on the SQL/API node | every cluster cycle | `ndb_node.memory_*_mb`, `uptime_sec` |

`NdbCollector` is read-only, idempotent (UPSERT on
`(id_ndb_cluster, ndb_node_id)`) and tolerant of mgmd timeouts — a
failed fetch leaves the previous snapshot untouched.

`NdbInfoCollector` queries the SQL/API node via the existing
`mysql_server` connection. No extra credentials, no extra firewall hole.

## UI

- `/architecture/index` — the cluster shape is a 3-band block (orange
  Management / green Data nodes / blue SQL-API). Edges:
  - dashed orange = mgmd arbitrate (heartbeat / config),
  - solid blue = SQL plane (sql/api → data via NDB engine),
  - solid green between paired data nodes = replica.
- `/NdbCluster/index/<id>` — per-cluster dashboard with the three role
  bands, status badges, uptime, memory tiles.

## Alerts

Computed by `App\Service\Ndb\NdbAlertEmitter` at the end of every
collection cycle and persisted as rows in `pmc_event_alert` keyed by a
sha256 fingerprint (`ndb:<cluster>:<code>:<entity>`) so re-runs do not
fan out. Stale alerts auto-resolve with `date_end = NOW(6)`.

| Code | Severity | Condition |
|---|---|---|
| `NDB_DATA_NODE_DOWN` | warning | 1 data node !STARTED, the node group still has at least one peer up. |
| `NDB_NODEGROUP_LOST` | critical | Every data node of a node group is in a non-STARTED state — fragments of that group are unreachable. |
| `NDB_MGMD_DOWN` | warning | One mgmd !CONNECTED, at least another mgmd is up. |
| `NDB_NO_ARBITRATOR` | critical | All mgmds are unreachable — no arbitrator, no monitoring, no joiner. |
| `NDB_SQL_NODE_DOWN` | info | One SQL/API !CONNECTED, peers are still up. |
| `NDB_MEMORY_PRESSURE` | warning | A data node uses > 90 % of its memory pool. |
| `NDB_MEMORY_FULL` | critical | A data node uses > 98 % of its memory pool — writes will start failing soon. |

## Cleanup

Soft-delete a cluster:

```sql
UPDATE ndb_cluster SET is_deleted = 1 WHERE id = <id>;
```

Soft-deleted clusters drop out of the dot3 render and the dashboard but
their rows (and FK-cascaded `ndb_node` and `ndb_cluster__mysql_server`)
stay around for forensics. Hard delete is a regular `DELETE` and the FK
cascade does the cleanup.

To detach a SQL/API node without deleting the cluster:

```sql
UPDATE mysql_server SET is_ndb_cluster_node = 0 WHERE id = <id>;
DELETE FROM ndb_cluster__mysql_server
WHERE id_ndb_cluster = <cluster> AND id_mysql_server = <id>;
```

## Troubleshooting

- **Cluster shows no data nodes** — the `ndb_mgm` command failed (timeout,
  wrong host/port, ssh keys missing). Check `command` in the collector
  return shape; run the same command by hand to repro.
- **Memory tiles empty** — `ndbinfo` is reachable only after the SQL/API
  node has fully started. If `ndbinfo.memoryusage` raises an error the
  collector logs it without aborting; check `tmp/log/`.
- **Alert keeps coming back** — the fingerprint includes the entity, so a
  flapping data node reopens the same row instead of creating new ones.
  Inspect `pmc_event_alert.context` for the last-seen status and times.
- **No mgmd auth in the lab** — `ndb_mgm` typically has no auth in the
  default config. For production, wrap the call in SSH (the
  `command_template` field accepts arbitrary shell) and rely on SSH
  keys for authentication.
