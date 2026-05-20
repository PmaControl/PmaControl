<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Runtime;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #927 — flags backends with high `ConnFAIL` in
 * `stats_mysql_connection_pool`.
 *
 * Single-snapshot DB: the audit can't compute "growing" without a
 * historical baseline (we'd need the previous snapshot). Same
 * conservative pattern as #918/#919 — fall back to a threshold
 * warning that's still actionable: `ConnFAIL > 100` since the
 * counter was last reset is high enough to indicate a persistent
 * connectivity problem rather than a few stale connections.
 *
 * Fix: investigate connectivity, monitor user, pt-heartbeat, then
 * reset via `SELECT * FROM stats_mysql_connection_pool_reset` to
 * get a clean baseline for the next audit run.
 */
final class ConnectFailuresGrowing implements AuditCheck
{
    public function id(): string
    {
        return 'runtime.connect-failures-growing';
    }

    public function category(): string
    {
        return 'runtime';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT hostgroup, srv_host, srv_port, ConnFAIL, ConnERR "
             . "FROM stats_mysql_connection_pool "
             . "WHERE ConnFAIL > 100";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $hg   = (string) ($row['hostgroup'] ?? '?');
            $h    = (string) ($row['srv_host']  ?? '?');
            $p    = (string) ($row['srv_port']  ?? '?');
            $fail = (string) ($row['ConnFAIL']  ?? '?');
            $err  = (string) ($row['ConnERR']   ?? '?');

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    'Backend %s:%s in HG %s has ConnFAIL=%s (>100)',
                    $h,
                    $p,
                    $hg,
                    $fail
                ),
                $sql . "\n  → hostgroup=" . $hg
                     . " host=" . $h
                     . " port=" . $p
                     . " ConnFAIL=" . $fail
                     . " ConnERR=" . $err,
                "Investigate the connectivity cause (network, monitor user "
                . "ACLs, pt-heartbeat) — see audit checks #912/#913/#918. "
                . "Once fixed, reset the counters to get a clean baseline:\n"
                . "  SELECT * FROM stats_mysql_connection_pool_reset;",
                null
            );
        }

        return $findings;
    }
}
