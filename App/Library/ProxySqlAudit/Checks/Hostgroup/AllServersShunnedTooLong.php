<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Hostgroup;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #906 — flags hostgroups whose every server is in
 * `OFFLINE_HARD` / `SHUNNED` for at least 5 minutes.
 *
 * Severity is `warning` by default — a single SQL pass can't tell
 * whether the hostgroup is referenced by users / query rules /
 * cluster tables (that detail belongs to other checks). When it
 * IS referenced operators will see the cascading effect through
 * #904 / #920 anyway.
 */
final class AllServersShunnedTooLong implements AuditCheck
{
    public function id(): string
    {
        return 'hostgroup.all-servers-shunned-too-long';
    }

    public function category(): string
    {
        return 'hostgroup';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT hostgroup_id, "
             .         "MIN(time_last_change) AS first_offline_at, "
             .         "TIMESTAMPDIFF(MINUTE, MIN(time_last_change), NOW()) AS minutes_offline "
             . "FROM runtime_mysql_servers "
             . "WHERE status IN ('OFFLINE_HARD','SHUNNED') "
             . "GROUP BY hostgroup_id "
             . "HAVING SUM(status NOT IN ('OFFLINE_HARD','SHUNNED'))=0 "
             .   "AND TIMESTAMPDIFF(MINUTE, MIN(time_last_change), NOW()) >= 5";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    'Hostgroup %s has every server SHUNNED/OFFLINE_HARD for %s minute(s)',
                    (string) ($row['hostgroup_id'] ?? '?'),
                    (string) ($row['minutes_offline'] ?? '?')
                ),
                $sql . "\n  → " . sprintf(
                    'hostgroup_id=%s first_offline_at=%s minutes_offline=%s',
                    (string) ($row['hostgroup_id']      ?? '?'),
                    (string) ($row['first_offline_at']  ?? '?'),
                    (string) ($row['minutes_offline']   ?? '?')
                ),
                "Investigate the underlying backend (network / mysqld) and re-enable:\n"
                . "  UPDATE mysql_servers SET status='ONLINE' WHERE hostgroup_id=" . (string) ($row['hostgroup_id'] ?? '?') . ";\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
