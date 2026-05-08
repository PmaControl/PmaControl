<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #914 — flags the case where the monitor user exists on a backend
 * but its `Host` ACL doesn't accept ProxySQL's outbound IP.
 *
 * Example: `'monitor'@'%.example.com'` is created on the backend but
 * ProxySQL connects from `10.x.y.z`; backend logs `Access denied for
 * user`, ProxySQL marks the host SHUNNED. Cross-source — needs both
 * the backend `mysql.user` rows AND ProxySQL's actual outbound IP.
 *
 * Per-backend grants probing isn't yet wired through the audit
 * framework; ships the conservative info-fallback consistent with
 * #912 / #913 — list candidates and recommend operator spot-check.
 */
final class MonitorUserHostMismatch implements AuditCheck
{
    public function id(): string
    {
        return 'user.monitor-user-host-mismatch';
    }

    public function category(): string
    {
        return 'user';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $vRes = $db->sql_query_silent(
            "SELECT variable_value FROM runtime_global_variables WHERE variable_name='mysql-monitor_username'"
        );
        if ($vRes === false) {
            return [];
        }
        $vRow = $db->sql_fetch_array($vRes, MYSQLI_ASSOC);
        $monitorUser = trim((string) ($vRow['variable_value'] ?? ''));
        if ($monitorUser === '') {
            return []; // #916 covers this
        }

        $bRes = $db->sql_query_silent("SELECT DISTINCT hostname, port FROM runtime_mysql_servers");
        if ($bRes === false) {
            return [];
        }
        $backends = [];
        while ($row = $db->sql_fetch_array($bRes, MYSQLI_ASSOC)) {
            $h = (string) ($row['hostname'] ?? '');
            $p = (int) ($row['port'] ?? 0);
            if ($h !== '' && $p > 0) {
                $backends[] = $h . ':' . $p;
            }
        }
        if (empty($backends)) {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_INFO,
                $this->category(),
                $this->id(),
                sprintf(
                    "Manual spot-check recommended: verify monitor user '%s' Host ACL accepts ProxySQL's outbound IP",
                    $monitorUser
                ),
                "monitor_user='" . $monitorUser . "'\n"
                . "backends:\n  - " . implode("\n  - ", $backends),
                "Find ProxySQL's outbound IP (the address it connects FROM):\n"
                . "  -- inside ProxySQL admin:  SELECT @@bind_address;  -- if exposed\n"
                . "  -- or check the backend's `Host_Cache` for the actual source.\n"
                . "On each backend run:\n"
                . "  SELECT user, host FROM mysql.user WHERE user='" . $monitorUser . "';\n"
                . "If no Host pattern matches the outbound IP, create the matching row:\n"
                . "  CREATE USER '" . $monitorUser . "'@'<proxysql_outbound_ip>' IDENTIFIED BY '<password>';\n"
                . "  GRANT REPLICATION CLIENT, USAGE ON *.* TO '" . $monitorUser . "'@'<proxysql_outbound_ip>';",
                null
            ),
        ];
    }
}
