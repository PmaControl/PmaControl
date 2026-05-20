<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Runtime;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #926 — flags zombie sessions in `stats_mysql_processlist`.
 *
 * Two flavours:
 *   - long-running statement: time_ms > 600s (10 min)
 *   - long Sleep:             command='Sleep' AND time_ms > 300s (5 min)
 *
 * Both eat connections in the backend pool. Recommend the
 * ProxySQL-side `KILL SESSION` over a backend-side KILL because
 * proxysql-side kill drops the frontend connection cleanly and
 * the backend connection is reused; backend-side kill leaves the
 * frontend hanging on the next operation.
 *
 * Ticket reference: #148.
 */
final class ZombieSessions implements AuditCheck
{
    public function id(): string
    {
        return 'runtime.zombie-sessions';
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

        $sql = "SELECT SessionID, hostgroup, ThreadID, ConnectionID, "
             .   "db, user, l_username, l_srv_host, hostname, "
             .   "l_srv_port, port, command, time_ms, info "
             . "FROM stats_mysql_processlist "
             . "WHERE time_ms > 600000 "
             .    "OR (command = 'Sleep' AND time_ms > 300000)";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $sid    = (string) ($row['SessionID']    ?? '?');
            $tid    = (string) ($row['ThreadID']     ?? '?');
            $h      = (string) ($row['hostname']     ?? '?');
            $p      = (string) ($row['port']         ?? '?');
            $cmd    = (string) ($row['command']      ?? '?');
            $tms    = (int)    ($row['time_ms']      ?? 0);
            $secs   = (int) round($tms / 1000);
            $user   = (string) ($row['l_username']   ?? ($row['user'] ?? '?'));
            $info   = (string) ($row['info']         ?? '');

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    'Zombie session SessionID=%s on %s:%s (%s for %ds)',
                    $sid,
                    $h,
                    $p,
                    $cmd,
                    $secs
                ),
                $sql . "\n  → SessionID=" . $sid
                     . " ThreadID=" . $tid
                     . " backend=" . $h . ":" . $p
                     . " user=" . $user
                     . " command=" . $cmd
                     . " time_ms=" . $tms
                     . ($info !== '' ? "\n     info: " . $info : ''),
                "Prefer the ProxySQL-side kill (drops the frontend conn and "
                . "lets the backend be reused):\n"
                . "  KILL SESSION " . $sid . ";\n"
                . "Backend-side fallback (leaves the frontend hanging):\n"
                . "  KILL " . $tid . ";   -- run on " . $h . ":" . $p,
                '#148'
            );
        }

        return $findings;
    }
}
