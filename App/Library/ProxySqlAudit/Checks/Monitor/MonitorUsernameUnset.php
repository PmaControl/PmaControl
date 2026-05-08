<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Monitor;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #916 — flags ProxySQL instances with no `mysql-monitor_username`
 * configured.
 *
 * When unset, ProxySQL's monitor module is effectively disabled:
 * every cluster check returns "no opinion",
 * `mysql_replication_hostgroups` won't reroute on master fail,
 * Galera/GR auto-failover never triggers.
 *
 * `mysql-monitor_password` can be intentionally empty in some
 * deployments (e.g. `auth_socket` on backend), so we don't pair
 * the username check with a password presence check here.
 */
final class MonitorUsernameUnset implements AuditCheck
{
    public function id(): string
    {
        return 'monitor.monitor-username-unset';
    }

    public function category(): string
    {
        return 'monitor';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT variable_value FROM runtime_global_variables "
             . "WHERE variable_name='mysql-monitor_username'";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        $value = trim((string) ($row['variable_value'] ?? ''));
        if ($value !== '') {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_CRITICAL,
                $this->category(),
                $this->id(),
                'mysql-monitor_username is unset — monitoring effectively disabled',
                $sql . "\n  → variable_value=''",
                "Pick a monitor username and reload runtime + persist:\n"
                . "  SET mysql-monitor_username='<m>';\n"
                . "  SET mysql-monitor_password='<password>';\n"
                . "  LOAD MYSQL VARIABLES TO RUNTIME;\n"
                . "  SAVE MYSQL VARIABLES TO DISK;",
                null
            ),
        ];
    }
}
