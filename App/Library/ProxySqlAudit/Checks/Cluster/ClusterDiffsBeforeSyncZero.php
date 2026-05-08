<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Cluster;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #924 — flags `admin-cluster_*_diffs_before_sync` set to 0.
 *
 * Setting any `*_diffs_before_sync` to 0 disables the diff
 * debounce: every checksum bump on a peer triggers an immediate
 * pull, so a momentary blip on one peer (a config write that's
 * about to be reverted, a mid-load-runtime state) propagates
 * through the whole cluster instead of being absorbed. The
 * ProxySQL ship default is 3 — three identical checksum
 * snapshots in a row before pulling.
 *
 * Warning per offending variable; fix sets the variable back to
 * 3 and pushes admin variables to runtime.
 */
final class ClusterDiffsBeforeSyncZero implements AuditCheck
{
    public function id(): string
    {
        return 'cluster.diffs-before-sync-zero';
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

        $sql = "SELECT variable_name, variable_value "
             . "FROM runtime_global_variables "
             . "WHERE variable_name LIKE 'admin-cluster_%_diffs_before_sync' "
             .   "AND CAST(variable_value AS UNSIGNED) = 0";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $name = (string) ($row['variable_name']  ?? '?');
            $val  = (string) ($row['variable_value'] ?? '?');

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    '%s=%s — proxysql_cluster propagates every change without diff debounce',
                    $name,
                    $val
                ),
                $sql . "\n  → " . $name . "=" . $val,
                "Restore the ProxySQL default (3 consecutive identical checksums "
                . "before sync):\n"
                . "  UPDATE global_variables SET variable_value='3' "
                . "WHERE variable_name='" . $name . "';\n"
                . "  LOAD ADMIN VARIABLES TO RUNTIME;\n"
                . "  SAVE ADMIN VARIABLES TO DISK;",
                null
            );
        }

        return $findings;
    }
}
