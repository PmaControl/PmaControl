<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Hostgroup;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #905 — flags hostgroup IDs declared in any cluster table
 * (Galera writer/reader/backup_writer/offline, GR hostgroup,
 * replication writer/reader) that have zero rows in
 * `runtime_mysql_servers`. These are typically stale cluster
 * declarations left behind after a failover or refactor.
 *
 * Distinct from #904 which is about users pointing at empty
 * hostgroups; this one is about cluster tables themselves.
 */
final class OrphanHostgroup implements AuditCheck
{
    public function id(): string
    {
        return 'hostgroup.orphan-hostgroup';
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

        $sql = "SELECT DISTINCT u.hg, u.source FROM ("
             .   "SELECT writer_hostgroup        AS hg, 'galera_writer'        AS source FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT reader_hostgroup,         'galera_reader'             FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT backup_writer_hostgroup,  'galera_backup_writer'      FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT offline_hostgroup,        'galera_offline'            FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT hostgroup,                'group_replication'         FROM runtime_mysql_group_replication_hostgroups "
             .   "UNION SELECT writer_hostgroup,         'replication_writer'        FROM runtime_mysql_replication_hostgroups "
             .   "UNION SELECT reader_hostgroup,         'replication_reader'        FROM runtime_mysql_replication_hostgroups"
             . ") u "
             . "WHERE NOT EXISTS ("
             .   "SELECT 1 FROM runtime_mysql_servers s WHERE s.hostgroup_id = u.hg"
             . ")";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $orphans = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $orphans[] = sprintf('hostgroup=%s declared by %s', (string) $row['hg'], (string) $row['source']);
        }

        if (empty($orphans)) {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    '%d orphan hostgroup(s) — declared in a cluster table but with no servers',
                    count($orphans)
                ),
                $sql . "\n  → " . implode("\n  → ", $orphans),
                "Either populate the hostgroup with mysql_servers rows or drop the dangling cluster declaration:\n"
                . "  DELETE FROM mysql_galera_hostgroups WHERE writer_hostgroup=<HG>;\n"
                . "  -- or symmetric variant on mysql_group_replication_hostgroups / mysql_replication_hostgroups\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            ),
        ];
    }
}
