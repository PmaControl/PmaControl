<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #911 — flags `mysql_users` rows whose `default_schema` is set
 * to a system schema (`information_schema`, `mysql`).
 *
 * Seen alongside the broken twin row in #148. Operators almost
 * never want their default_schema to land on a system schema —
 * it's usually copy/paste leftover or a literal misconfiguration.
 */
final class SuspiciousDefaultSchema implements AuditCheck
{
    public function id(): string
    {
        return 'user.suspicious-default-schema';
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

        $sql = "SELECT username, default_schema "
             . "FROM runtime_mysql_users "
             . "WHERE LOWER(default_schema) IN ('information_schema','mysql')";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $username = (string) ($row['username'] ?? '?');
            $schema   = (string) ($row['default_schema'] ?? '?');

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf("User '%s' has default_schema='%s' (system schema)", $username, $schema),
                $sql . "\n  → user='" . $username . "' default_schema='" . $schema . "'",
                "Set default_schema to the application database (or NULL for the server's default):\n"
                . "  UPDATE mysql_users SET default_schema='<APP_DB>' WHERE username='" . $username . "';\n"
                . "  LOAD MYSQL USERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
