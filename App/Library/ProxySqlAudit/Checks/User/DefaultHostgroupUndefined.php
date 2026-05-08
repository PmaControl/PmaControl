<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\User;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #910 — flags `mysql_users` rows whose `default_hostgroup`
 * points at a hostgroup that's never declared in
 * `runtime_mysql_servers`.
 *
 * Complementary to #904 (which catches hostgroups that exist but
 * are empty); this one catches hostgroups that *don't even
 * exist* — the user is functionally routed to nowhere.
 */
final class DefaultHostgroupUndefined implements AuditCheck
{
    public function id(): string
    {
        return 'user.default-hostgroup-undefined';
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

        $sql = "SELECT u.username, u.default_hostgroup "
             . "FROM runtime_mysql_users u "
             . "WHERE NOT EXISTS ("
             .   "SELECT 1 FROM runtime_mysql_servers s "
             .    "WHERE s.hostgroup_id = u.default_hostgroup"
             . ")";

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
                    "User '%s' default_hostgroup=%s is never declared in mysql_servers",
                    $username, $hg
                ),
                $sql . "\n  → user='" . $username . "' default_hostgroup=" . $hg,
                "Either populate hostgroup " . $hg . " with mysql_servers rows, or re-point the user:\n"
                . "  UPDATE mysql_users SET default_hostgroup=<EXISTING_HG> WHERE username='" . $username . "';\n"
                . "  LOAD MYSQL USERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
