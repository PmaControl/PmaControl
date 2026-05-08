<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Runtime;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #925 — flags backends sitting in SHUNNED / SHUNNED_REPLICATION_LAG
 * in `runtime_mysql_servers`.
 *
 * ProxySQL re-tests SHUNNED backends on its monitor cadence. A
 * backend that's still SHUNNED at audit time means the underlying
 * cause (connect failure, replication lag) is persistent rather
 * than transient. The audit only sees a single snapshot, so the
 * "too long" qualifier is enforced by the operator's audit cadence:
 * if it shows up here it's still shunned right now.
 *
 * Warning per (hostgroup, hostname, port). Fix is the manual
 * unshun + LOAD, paired with the suggestion to investigate the
 * actual cause (#927 covers connect_check_failures growth).
 */
final class ShunnedTooLong implements AuditCheck
{
    public function id(): string
    {
        return 'runtime.shunned-too-long';
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

        $sql = "SELECT hostgroup_id, hostname, port, status "
             . "FROM runtime_mysql_servers "
             . "WHERE status IN ('SHUNNED', 'SHUNNED_REPLICATION_LAG')";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $hg     = (string) ($row['hostgroup_id'] ?? '?');
            $h      = (string) ($row['hostname']     ?? '?');
            $p      = (string) ($row['port']         ?? '?');
            $status = (string) ($row['status']       ?? '?');

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf('%s:%s in HG %s is %s', $h, $p, $hg, $status),
                $sql . "\n  → hostgroup_id=" . $hg
                     . " hostname=" . $h
                     . " port=" . $p
                     . " status=" . $status,
                "Investigate the underlying cause first (connect failures, "
                . "replication lag — see audit checks #927/#919). Once cleared:\n"
                . "  UPDATE mysql_servers SET status='ONLINE' "
                . "WHERE hostname='" . $h . "' AND port=" . $p
                . " AND hostgroup_id=" . $hg . ";\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
