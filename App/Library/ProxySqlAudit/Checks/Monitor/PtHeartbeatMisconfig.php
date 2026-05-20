<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Monitor;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #918 — flags ProxySQL configurations that point at a Percona
 * pt-heartbeat table for replication-lag measurement when the
 * referenced table may not exist on the master.
 *
 * Cross-source: ideally we'd probe the master's
 * `information_schema.tables` for the configured
 * `mysql-monitor_replication_lag_table`. We don't yet have a
 * per-backend information-schema accessor wired through, so this
 * ships the conservative info-fallback consistent with
 * #912 / #913 / #914 — list the configured table and recommend
 * operator spot-check.
 */
final class PtHeartbeatMisconfig implements AuditCheck
{
    public function id(): string
    {
        return 'monitor.pt-heartbeat-misconfig';
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

        $sql = "SELECT variable_name, variable_value "
             . "FROM runtime_global_variables "
             . "WHERE variable_name IN ("
             .   "'mysql-monitor_replication_lag_use_percona_heartbeat',"
             .   "'mysql-monitor_replication_lag_table'"
             . ")";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $useHb = '';
        $table = '';
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $name = (string) ($row['variable_name'] ?? '');
            $val  = (string) ($row['variable_value'] ?? '');
            if ($name === 'mysql-monitor_replication_lag_use_percona_heartbeat') {
                $useHb = strtolower(trim($val));
            } elseif ($name === 'mysql-monitor_replication_lag_table') {
                $table = trim($val);
            }
        }

        $enabled = in_array($useHb, ['true', '1', 'on', 'yes'], true);
        if (!$enabled || $table === '') {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_INFO,
                $this->category(),
                $this->id(),
                sprintf(
                    "pt-heartbeat enabled — verify '%s' exists on the master",
                    $table
                ),
                "mysql-monitor_replication_lag_use_percona_heartbeat=" . $useHb . "\n"
                . "mysql-monitor_replication_lag_table='" . $table . "'",
                "On the master, confirm the heartbeat table exists and is being written to:\n"
                . "  SELECT 1 FROM " . $table . " LIMIT 1;\n"
                . "  SELECT TIMESTAMPDIFF(SECOND, ts, NOW()) AS lag_seen FROM " . $table . " ORDER BY ts DESC LIMIT 1;\n"
                . "If the table is missing, deploy pt-heartbeat:\n"
                . "  pt-heartbeat --update --create-table --database <db>\n"
                . "or disable the integration:\n"
                . "  SET mysql-monitor_replication_lag_use_percona_heartbeat='false';\n"
                . "  LOAD MYSQL VARIABLES TO RUNTIME; SAVE MYSQL VARIABLES TO DISK;",
                null
            ),
        ];
    }
}
