<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Cluster;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #923 — flags that proxysql_cluster peering symmetry cannot be
 * verified from this host.
 *
 * "Symmetric peering" means: every peer that *we* list must also
 * list *us*. Confirming that requires opening an admin connection
 * to each peer with credentials we don't have here. So this check
 * is conservative: it lists the local peer set and the local
 * (hostname, port) admin endpoints, then recommends the operator
 * spot-check via the same SELECT on each peer. Same info-fallback
 * pattern as #912/#913/#914/#918/#919.
 *
 * Severity is `info` — silence by design when the local peer set
 * is empty (no cluster configured), warn-by-suggestion otherwise.
 */
final class AsymmetricPeering implements AuditCheck
{
    public function id(): string
    {
        return 'cluster.asymmetric-peering';
    }

    public function category(): string
    {
        return 'cluster';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT hostname, port FROM runtime_proxysql_servers ORDER BY hostname, port";
        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $peers = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $peers[] = (string) ($row['hostname'] ?? '?')
                     . ':'
                     . (string) ($row['port']     ?? '?');
        }

        if (empty($peers)) {
            return [];
        }

        $list = implode(', ', $peers);

        return [new Finding(
            Finding::SEVERITY_INFO,
            $this->category(),
            $this->id(),
            'proxysql_cluster peer membership cannot be verified from this host',
            $sql . "\n  local peer set: " . $list
                 . "\n  cross-peer verification needs admin credentials per peer; "
                 . "not attempted here.",
            "On each peer above, run:\n"
            . "  " . $sql . "\n"
            . "and confirm every peer lists every other peer "
            . "(including this one). Asymmetric sets cause one-way "
            . "config drift via proxysql_cluster.",
            null
        )];
    }
}
