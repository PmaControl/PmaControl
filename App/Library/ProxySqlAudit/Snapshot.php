<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit;

/**
 * #929 — runs the audit registry against a ProxySQL admin
 * connection and persists the per-severity counts in
 * `proxysql_audit_snapshot` (one row per `proxysql_server.id`).
 *
 * The home-page card reads this snapshot table directly, so the
 * audit pipeline runs on the aspirateur cadence and `/home` does
 * not pay the per-check cost.
 *
 * Designed to be safe to call from the aspirateur worker: a
 * runtime failure in any one check is already swallowed by
 * `Runner::runAll`, and the persistence step is wrapped in a
 * try/catch by the caller — see `Aspirateur::testproxysql`.
 */
final class Snapshot
{
    /**
     * Run the audit and write/update the snapshot row.
     *
     * @param object      $db                  ProxySQL admin DB handle
     * @param int         $idProxysqlServer    Primary key of `proxysql_server`
     * @param object|null $pmacontrolDb        Default pmacontrol DB handle (where the snapshot table lives)
     * @return array{critical:int,warning:int,info:int} the counts persisted
     */
    public static function record(object $db, int $idProxysqlServer, ?object $pmacontrolDb): array
    {
        $ctx = [
            'id_proxysql_server' => $idProxysqlServer,
            'pmacontrol_db'      => $pmacontrolDb,
        ];

        $findings = Runner::runAll($db, $ctx);

        $counts = self::countBySeverity($findings);

        if ($pmacontrolDb !== null && method_exists($pmacontrolDb, 'sql_query_silent')) {
            self::persist($pmacontrolDb, $idProxysqlServer, $counts, null);
        }

        return $counts;
    }

    /**
     * @param list<Finding> $findings
     * @return array{critical:int,warning:int,info:int}
     */
    public static function countBySeverity(array $findings): array
    {
        $counts = ['critical' => 0, 'warning' => 0, 'info' => 0];
        foreach ($findings as $f) {
            if (!$f instanceof Finding) {
                continue;
            }
            $sev = $f->severity;
            if (isset($counts[$sev])) {
                $counts[$sev]++;
            }
        }
        return $counts;
    }

    /**
     * INSERT…ON DUPLICATE KEY UPDATE the counts. Caller wraps in
     * try/catch because the snapshot table may not exist yet
     * (migration pending) or the connection may be unhealthy.
     *
     * @param array{critical:int,warning:int,info:int} $counts
     */
    public static function persist(
        object $pmacontrolDb,
        int $idProxysqlServer,
        array $counts,
        ?string $lastError
    ): void {
        $err = $lastError === null
            ? 'NULL'
            : "'" . self::escape($pmacontrolDb, $lastError) . "'";

        $sql = "INSERT INTO proxysql_audit_snapshot "
             . "(id_proxysql_server, findings_critical, findings_warning, findings_info, last_checked_at, last_error) "
             . "VALUES ("
             . (int) $idProxysqlServer . ", "
             . (int) $counts['critical'] . ", "
             . (int) $counts['warning'] . ", "
             . (int) $counts['info'] . ", "
             . "NOW(), "
             . $err
             . ") ON DUPLICATE KEY UPDATE "
             . "findings_critical = VALUES(findings_critical), "
             . "findings_warning  = VALUES(findings_warning), "
             . "findings_info     = VALUES(findings_info), "
             . "last_checked_at   = VALUES(last_checked_at), "
             . "last_error        = VALUES(last_error)";

        $pmacontrolDb->sql_query_silent($sql);
    }

    private static function escape(object $db, string $s): string
    {
        if (method_exists($db, 'sql_real_escape_string')) {
            return (string) $db->sql_real_escape_string($s);
        }
        // Last-resort: strip the dangerous chars rather than failing.
        return str_replace(["'", "\\", "\0"], '', $s);
    }
}
