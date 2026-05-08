<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Hostgroup;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #904 — flags `mysql_users` rows whose `default_hostgroup` points
 * at a hostgroup that holds zero ONLINE/SHUNNED servers.
 *
 * Direct lift from incident #148: every connection from that user
 * timed out at `mysql-default_query_timeout=10000` ms because the
 * routed hostgroup was an empty Galera offline_hostgroup.
 *
 * The `SHUNNED` status is included because operators should be
 * notified when a transiently-shunned hostgroup is the *only*
 * destination — that's still a live failure mode.
 */
final class EmptyDefaultHostgroup implements AuditCheck
{
    public function id(): string
    {
        return 'hostgroup.empty-default-hostgroup';
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

        $sql = "SELECT u.username, u.default_hostgroup, "
             .         "COUNT(s.hostgroup_id) AS servers "
             . "FROM runtime_mysql_users u "
             . "LEFT JOIN runtime_mysql_servers s "
             .   "ON s.hostgroup_id = u.default_hostgroup "
             .  "AND s.status IN ('ONLINE','SHUNNED') "
             . "GROUP BY u.username, u.default_hostgroup "
             . "HAVING servers = 0";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $username = (string) ($row['username'] ?? '?');
            $hg       = (string) ($row['default_hostgroup'] ?? '?');

            $findings[] = new Finding(
                Finding::SEVERITY_CRITICAL,
                $this->category(),
                $this->id(),
                sprintf(
                    "User '%s' is routed to hostgroup %s which has 0 reachable servers",
                    $username,
                    $hg
                ),
                $sql . "\n  → user='" . $username . "' default_hostgroup=" . $hg,
                "Re-point default_hostgroup to a populated writer:\n"
                . "  UPDATE mysql_users SET default_hostgroup=<W> WHERE username='" . $username . "';\n"
                . "  LOAD MYSQL USERS TO RUNTIME;",
                '#148'
            );
        }

        return $findings;
    }
}
