<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Monitor;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #919 — flags ProxySQL `mysql-server_version` that may not match
 * the actual backend versions.
 *
 * Clients negotiate features and SQL dialect against
 * `mysql-server_version`; if it's "5.7.30" but the backend is
 * 8.0.36, clients get the wrong feature set advertised. Common
 * after a major upgrade where the variable wasn't bumped.
 *
 * Cross-source: ideally we'd `SELECT @@global.version` per
 * backend. Per-backend probing isn't yet wired through; ships the
 * conservative info-fallback consistent with #912 / #913 / #914 /
 * #918 — list the configured value + backends and recommend
 * operator spot-check.
 */
final class ServerVersionDrift implements AuditCheck
{
    public function id(): string
    {
        return 'monitor.server-version-drift';
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

        $vRes = $db->sql_query_silent(
            "SELECT variable_value FROM runtime_global_variables WHERE variable_name='mysql-server_version'"
        );
        if ($vRes === false) {
            return [];
        }
        $vRow = $db->sql_fetch_array($vRes, MYSQLI_ASSOC);
        $configured = trim((string) ($vRow['variable_value'] ?? ''));
        if ($configured === '') {
            return [];
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
                    "Manual spot-check recommended: verify mysql-server_version='%s' matches every backend",
                    $configured
                ),
                "mysql-server_version='" . $configured . "'\n"
                . "backends:\n  - " . implode("\n  - ", $backends),
                "On each backend run:\n"
                . "  SELECT @@global.version;\n"
                . "If the major.minor (or major) differs from '" . $configured . "', bump the ProxySQL variable:\n"
                . "  SET mysql-server_version='<actual>';\n"
                . "  LOAD MYSQL VARIABLES TO RUNTIME;\n"
                . "  SAVE MYSQL VARIABLES TO DISK;",
                null
            ),
        ];
    }
}
