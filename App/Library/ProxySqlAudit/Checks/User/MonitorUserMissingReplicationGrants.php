<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #913 — flags backends where the monitor user lacks the grants
 * ProxySQL needs to read replication state.
 *
 * Without `REPLICATION CLIENT` (MySQL) / `BINLOG MONITOR` (MariaDB
 * 10.5+) / `REPLICA MONITOR` (MySQL 8.0.26+), ProxySQL can't run
 * `SHOW SLAVE STATUS` / `SHOW REPLICA STATUS` and
 * `mysql_replication_hostgroups` will keep flapping the writer.
 *
 * Cross-source: ideally we'd `SHOW GRANTS FOR '<m>'@'<host>'` per
 * backend. We don't yet have a per-backend grants snapshot, so
 * this ships the conservative version (info-fallback consistent
 * with #912).
 */
final class MonitorUserMissingReplicationGrants implements AuditCheck
{
    public function id(): string
    {
        return 'user.monitor-user-missing-replication-grants';
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
                    "Manual spot-check recommended: verify monitor user '%s' has REPLICATION CLIENT / BINLOG MONITOR / REPLICA MONITOR on every backend",
                    $monitorUser
                ),
                "monitor_user='" . $monitorUser . "'\n"
                . "backends:\n  - " . implode("\n  - ", $backends),
                "On each backend run:\n"
                . "  SHOW GRANTS FOR '" . $monitorUser . "'@'<proxysql_outbound_ip>';\n"
                . "If REPLICATION CLIENT / BINLOG MONITOR / REPLICA MONITOR is missing, grant it:\n"
                . "  GRANT REPLICATION CLIENT, BINLOG MONITOR, REPLICA MONITOR\n"
                . "    ON *.* TO '" . $monitorUser . "'@'<proxysql_outbound_ip>';\n"
                . "(BINLOG MONITOR is the MariaDB 10.5+ alias; REPLICA MONITOR is the MySQL 8.0.26+ alias.)",
                null
            ),
        ];
    }
}
