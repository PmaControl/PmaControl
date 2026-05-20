<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #915 — flags `mysql_users` rows whose `password` is empty or
 * one of the well-known defaults that automated scanners try.
 *
 * The default-password list intentionally stays short: empty,
 * 'mysql', 'root', 'admin', 'password', 'proxysql'. A noisier
 * dictionary would generate false positives on legitimate
 * passwords that happen to be short.
 */
final class WeakDefaultPassword implements AuditCheck
{
    public function id(): string
    {
        return 'user.weak-default-password';
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

        $sql = "SELECT username "
             . "FROM runtime_mysql_users "
             . "WHERE password IN ('','mysql','root','admin','password','proxysql')";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $username = (string) ($row['username'] ?? '?');
            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf("User '%s' has a weak/default password", $username),
                $sql . "\n  → user='" . $username . "'",
                "Rotate to a strong password and reload runtime:\n"
                . "  UPDATE mysql_users SET password='<strong>' WHERE username='" . $username . "';\n"
                . "  LOAD MYSQL USERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
