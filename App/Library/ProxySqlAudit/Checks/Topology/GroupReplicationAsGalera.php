<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Topology;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #900 — symmetric to #899: flags backends whose hostgroup is wired
 * into BOTH `runtime_mysql_galera_hostgroups` AND
 * `runtime_mysql_group_replication_hostgroups`, looked at from the
 * Galera side.
 *
 * Together with #899, the two checks fully cover the cross-table
 * declaration mistake. Either deletion (drop from galera or drop
 * from GR) is valid; ProxySQL simply needs exactly one cluster
 * type wired per hostgroup.
 *
 * Motivated by #107 / #108.
 */
final class GroupReplicationAsGalera implements AuditCheck
{
    public function id(): string
    {
        return 'topology.gr-declared-as-galera';
    }

    public function category(): string
    {
        return 'topology';
    }

    public function run(object $db, array $ctx = []): array
    {
        $sql = "SELECT s.hostgroup_id, s.hostname, s.port "
             . "FROM runtime_mysql_servers s "
             . "WHERE EXISTS ("
             .   "SELECT 1 FROM runtime_mysql_galera_hostgroups g "
             .    "WHERE g.writer_hostgroup = s.hostgroup_id "
             .       "OR g.reader_hostgroup = s.hostgroup_id "
             .       "OR g.backup_writer_hostgroup = s.hostgroup_id "
             .       "OR g.offline_hostgroup = s.hostgroup_id"
             . ") "
             . "AND s.hostgroup_id IN ("
             .   "SELECT hostgroup FROM runtime_mysql_group_replication_hostgroups"
             . ")";

        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }
        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $offenders = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $offenders[] = sprintf(
                'hostgroup=%s host=%s:%s',
                (string) ($row['hostgroup_id'] ?? '?'),
                (string) ($row['hostname']     ?? '?'),
                (string) ($row['port']         ?? '?')
            );
        }

        if (empty($offenders)) {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_CRITICAL,
                $this->category(),
                $this->id(),
                sprintf(
                    '%d backend(s) declared in BOTH Group Replication and Galera hostgroups',
                    count($offenders)
                ),
                $sql . "\n  → " . implode("\n  → ", $offenders),
                "DELETE FROM mysql_galera_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "LOAD MYSQL SERVERS TO RUNTIME;\n"
                . "(or the symmetric DELETE on mysql_group_replication_hostgroups — keep exactly one)",
                '#107'
            ),
        ];
    }
}
