# ProxySQL configuration audit

`/ProxySQL/audit/<id>/` runs a battery of read-only checks against a ProxySQL admin connection and reports configuration errors with severity, evidence, and a fix recommendation. The same shape feeds the *ProxySQL audit* card on the home page (#929).

> **Read-only.** The audit never writes to ProxySQL. Operators apply fixes themselves with the suggested SQL.

## When to use it

- After a ProxySQL upgrade — `mysql-server_version`, monitor intervals and the SLAVE/REPLICA syntax all drift across releases.
- After a topology change — adding a Galera node, migrating Async → GR, splitting hostgroups.
- After a customer escalation that smells like ProxySQL — empty hostgroup timeouts, "connection is locked to hostgroup X" errors, monitor user grant problems.
- Snapshot the report from the **Copy as JSON** button and paste into the ticket so support has the evidence in one block.

## Severity vocabulary

| Severity | When | Operator action |
|---|---|---|
| `critical` | Traffic-impacting misconfig (10s timeouts, split-brain risk, monitor totally disabled) | Act now |
| `warning`  | Drift / degraded posture (orphan hostgroup, weak password, interval outliers) | Act soon |
| `info`     | Audit-framework metadata (a check threw, registry empty…) | No operator action |

The home-page card surfaces `critical` and `warning` counts; `info` stays internal so the front page isn't noisy.

## Categories

Findings group into seven buckets:

- **Topology** — Galera/GR/Async cross-declarations, hostgroup ID collisions.
- **Hostgroup integrity** — empty hostgroups, OFFLINE_HARD storms, read_only mismatches.
- **User configuration** — frontend-only/backend-only twins, default_hostgroup → empty, monitor user grants.
- **Monitor settings** — `mysql-monitor_*` interval outliers, pt-heartbeat config, `mysql-server_version` drift.
- **Query rules** — destination_hostgroup undefined, flagIN/flagOUT chaining cycles.
- **ProxySQL cluster** — duplicate peers, asymmetric peering, `cluster_*_diffs_before_sync = 0`.
- **Runtime / drift** — long-SHUNNED servers, zombie sessions (#148 aftermath), monitor connect failures, errlog storms.

The full list of checks lives in [`App/Library/ProxySqlAudit/Registry.php`](../App/Library/ProxySqlAudit/Registry.php).

## How to add a new check

A check is a small class implementing `App\Library\ProxySqlAudit\AuditCheck`:

```php
namespace App\Library\ProxySqlAudit\Checks\Hostgroup;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

final class EmptyDefaultHostgroup implements AuditCheck
{
    public function id(): string { return 'hostgroup.empty-default'; }
    public function category(): string { return 'hostgroup'; }

    public function run(object $db, array $ctx = []): array
    {
        $sql = "SELECT u.username, u.default_hostgroup, COUNT(s.hostgroup_id) AS servers
                FROM runtime_mysql_users u
                LEFT JOIN runtime_mysql_servers s
                  ON s.hostgroup_id = u.default_hostgroup
                 AND s.status IN ('ONLINE','SHUNNED')
                GROUP BY u.username, u.default_hostgroup
                HAVING servers = 0";

        $findings = [];
        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $findings[] = new Finding(
                Finding::SEVERITY_CRITICAL,
                'hostgroup',
                $this->id(),
                "User {$row['username']} routed to empty hostgroup {$row['default_hostgroup']}",
                "$sql\n  -> {$row['username']} (HG {$row['default_hostgroup']})",
                "UPDATE mysql_users SET default_hostgroup=<W> WHERE username='{$row['username']}'; LOAD MYSQL USERS TO RUNTIME;",
                '#148'
            );
        }
        return $findings;
    }
}
```

Then register it in [`App/Library/ProxySqlAudit/Registry.php`](../App/Library/ProxySqlAudit/Registry.php):

```php
public static function checks(): array
{
    return [
        \App\Library\ProxySqlAudit\Checks\Hostgroup\EmptyDefaultHostgroup::class,
        // ... other checks
    ];
}
```

The runner instantiates each class fresh per request, so checks must be stateless. Throwing is allowed — it surfaces as a `meta` info-finding rather than a 500.

## Cross-source data via `$ctx`

The controller passes a context bag to every check:

| Key | Type | Use |
|---|---|---|
| `id_proxysql_server` | `int` | Lookup peer via pmacontrol's `proxysql_server` table |
| `pmacontrol_db` | Glial DB handle | Read backend-side `ts_value_*` for grants/version drift checks |

Add new keys here as new check categories need them; document them in this section so other contributors don't shadow each other.

## Snapshot export

The **Copy as JSON** button on the audit tab serialises the full report:

```json
{
  "id_proxysql_server": 8,
  "severity_counts": { "critical": 2, "warning": 5, "info": 0 },
  "findings": [
    {
      "severity": "critical",
      "category": "hostgroup",
      "check_id": "hostgroup.empty-default",
      "title": "User pmacontrol routed to empty hostgroup 5",
      "evidence": "...SQL...",
      "fix": "UPDATE mysql_users ...",
      "ticket": "#148"
    }
  ]
}
```

This is the format the home-page card and any future export pipeline will consume.

## Related tickets

- EPIC: [#895](https://git.istosia.com/pmacontrol/pmacontrol/issues/895) — overall audit feature.
- Framework + UI: #896, #897, #898.
- Lots 1-7 (concrete checks): #899-#928.
- Home-page surfacing: #929.
- Historical incidents motivating specific checks: #107, #108, #143, #148.
