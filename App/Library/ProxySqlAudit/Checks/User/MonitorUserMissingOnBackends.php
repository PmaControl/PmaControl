<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #912 — flags backends where the configured monitor user is not
 * present.
 *
 * Cross-source: ideally we'd query each backend's `mysql.user`
 * directly. We don't yet have a per-backend grants accessor, so
 * this ships the conservative version: list the monitor user +
 * the (host, port) of every backend, recommend operator
 * spot-check `SELECT user, host FROM mysql.user WHERE user='<m>'`.
 *
 * The deterministic version can replace this once a per-backend
 * grants snapshot lands.
 */
final class MonitorUserMissingOnBackends implements AuditCheck
{
    public function id(): string
    {
        return 'user.monitor-user-missing-on-backends';
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

        // Read mysql-monitor_username; if unset, defer to #916.
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

        // Pull every distinct backend (hostname, port).
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

        // Conservative info-finding: cross-source grant probing isn't
        // wired here yet. List candidates so operators can spot-check.
        return [
            new Finding(
                Finding::SEVERITY_INFO,
                $this->category(),
                $this->id(),
                sprintf(
                    "Manual spot-check recommended: verify monitor user '%s' exists on every backend",
                    $monitorUser
                ),
                "monitor_user='" . $monitorUser . "'\n"
                . "backends:\n  - " . implode("\n  - ", $backends),
                "On each backend run:\n"
                . "  SELECT user, host FROM mysql.user WHERE user='" . $monitorUser . "';\n"
                . "If any returns 0 rows, recreate the user there:\n"
                . "  CREATE USER '" . $monitorUser . "'@'<proxysql_outbound_ip>' IDENTIFIED BY '<password>';\n"
                . "  GRANT REPLICATION CLIENT, USAGE ON *.* TO '" . $monitorUser . "'@'<proxysql_outbound_ip>';",
                null
            ),
        ];
    }
}
