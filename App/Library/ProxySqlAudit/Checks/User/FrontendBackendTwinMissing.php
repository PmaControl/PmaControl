<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #909 — direct repro of #148.
 *
 * ProxySQL needs both `frontend=1` AND `backend=1` rows for users
 * that authenticate clients AND re-authenticate against backends.
 * #148 had `pmacontrol (backend=0, frontend=1)` only and the
 * backend re-auth silently failed → 10s timeout.
 */
final class FrontendBackendTwinMissing implements AuditCheck
{
    public function id(): string
    {
        return 'user.frontend-backend-twin-missing';
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

        $sql = "SELECT username, "
             .         "SUM(frontend) AS f, "
             .         "SUM(backend)  AS b "
             . "FROM runtime_mysql_users "
             . "GROUP BY username "
             . "HAVING f = 0 OR b = 0";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $username = (string) ($row['username'] ?? '?');
            $f = (int) ($row['f'] ?? 0);
            $b = (int) ($row['b'] ?? 0);
            $missing = $f === 0 ? 'frontend' : 'backend';
            $present = $f === 0 ? 'backend'  : 'frontend';

            $findings[] = new Finding(
                Finding::SEVERITY_CRITICAL,
                $this->category(),
                $this->id(),
                sprintf("User '%s' missing %s twin row (has %s only)", $username, $missing, $present),
                $sql . "\n  → user='" . $username . "' frontend_sum=" . $f . " backend_sum=" . $b,
                "Insert the missing twin row and reload runtime:\n"
                . "  INSERT INTO mysql_users(username, password, default_hostgroup, frontend, backend)\n"
                . "    SELECT username, password, default_hostgroup,\n"
                . "      " . ($missing === 'frontend' ? '1, 0' : '0, 1') . "\n"
                . "    FROM mysql_users WHERE username='" . $username . "';\n"
                . "  LOAD MYSQL USERS TO RUNTIME;",
                '#148'
            );
        }

        return $findings;
    }
}
