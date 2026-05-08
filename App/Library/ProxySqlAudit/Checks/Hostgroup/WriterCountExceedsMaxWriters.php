<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Hostgroup;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #908 — flags Galera cluster rows where the number of ONLINE
 * servers in the writer hostgroup exceeds `max_writers`.
 *
 * The contract in `mysql_galera_hostgroups.max_writers` is "at
 * most this many writers"; ProxySQL silently keeps additional
 * writers in `OFFLINE_HARD` and operators only notice when one
 * unexpectedly becomes the sole writer.
 */
final class WriterCountExceedsMaxWriters implements AuditCheck
{
    public function id(): string
    {
        return 'hostgroup.writer-count-exceeds-max-writers';
    }

    public function category(): string
    {
        return 'hostgroup';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT g.writer_hostgroup, "
             .         "COUNT(s.hostgroup_id) AS writers, "
             .         "g.max_writers "
             . "FROM runtime_mysql_galera_hostgroups g "
             . "LEFT JOIN runtime_mysql_servers s "
             .   "ON s.hostgroup_id = g.writer_hostgroup "
             .  "AND s.status = 'ONLINE' "
             . "GROUP BY g.writer_hostgroup, g.max_writers "
             . "HAVING writers > g.max_writers";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $hg          = (string) ($row['writer_hostgroup'] ?? '?');
            $writers     = (int) ($row['writers']     ?? 0);
            $maxWriters  = (int) ($row['max_writers'] ?? 0);

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    'Writer hostgroup %s has %d ONLINE writers but max_writers=%d',
                    $hg, $writers, $maxWriters
                ),
                $sql . "\n  → writer_hostgroup=" . $hg
                     . " online_writers=" . $writers
                     . " max_writers=" . $maxWriters,
                "Either raise max_writers to match reality or move the surplus to backup_writer:\n"
                . "  UPDATE mysql_galera_hostgroups SET max_writers=" . $writers . " WHERE writer_hostgroup=" . $hg . ";\n"
                . "  -- or move N-max_writers servers to backup_writer_hostgroup\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            );
        }

        return $findings;
    }
}
