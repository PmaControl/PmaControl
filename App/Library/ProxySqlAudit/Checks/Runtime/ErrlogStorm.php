<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Runtime;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #928 — flags errno-storms in `stats_mysql_errors`.
 *
 * Each row in `stats_mysql_errors` is (hostgroup, hostname, port,
 * errno) and a `count_star` of how many times that errno has been
 * raised since the last reset. count_star > 100 indicates a
 * persistent error that the operator should investigate (denied
 * access, schema mismatch, deadlocks, broken query rule, …)
 * rather than an isolated incident.
 *
 * Info-fallback: older ProxySQL versions don't have this table;
 * a `false` from `sql_query_silent` is treated as "no data" and
 * the check returns empty — same conservative pattern as
 * #918 / #919.
 */
final class ErrlogStorm implements AuditCheck
{
    public function id(): string
    {
        return 'runtime.errlog-storm';
    }

    public function category(): string
    {
        return 'runtime';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT hostgroup, hostname, port, errno, count_star, last_seen "
             . "FROM stats_mysql_errors "
             . "WHERE count_star > 100";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $hg     = (string) ($row['hostgroup']  ?? '?');
            $h      = (string) ($row['hostname']   ?? '?');
            $p      = (string) ($row['port']       ?? '?');
            $errno  = (string) ($row['errno']      ?? '?');
            $count  = (string) ($row['count_star'] ?? '?');
            $last   = (string) ($row['last_seen']  ?? '');

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    'Backend %s:%s in HG %s errno=%s count=%s',
                    $h,
                    $p,
                    $hg,
                    $errno,
                    $count
                ),
                $sql . "\n  → hostgroup=" . $hg
                     . " host=" . $h
                     . " port=" . $p
                     . " errno=" . $errno
                     . " count_star=" . $count
                     . ($last !== '' ? " last_seen=" . $last : ''),
                "Investigate the underlying cause for errno=" . $errno
                . " (denied access, schema mismatch, deadlock, broken "
                . "query rule). Once fixed, reset the counters for a "
                . "clean baseline:\n"
                . "  SELECT * FROM stats_mysql_errors_reset;",
                null
            );
        }

        return $findings;
    }
}
