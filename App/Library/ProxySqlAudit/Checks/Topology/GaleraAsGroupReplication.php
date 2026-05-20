<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Topology;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #899 — flags backends whose hostgroup is wired into both
 * `runtime_mysql_galera_hostgroups` AND
 * `runtime_mysql_group_replication_hostgroups`.
 *
 * ProxySQL's Galera monitor and GR monitor will then fight over the
 * same target — write split-brain risk, monitor flapping, false
 * SHUNNED transitions. Exactly one cluster type must be active per
 * hostgroup.
 *
 * Motivated by #107 (Dot3 rendered no host-group rows because the
 * cluster was registered as the wrong type).
 */
final class GaleraAsGroupReplication implements AuditCheck
{
    public function id(): string
    {
        return 'topology.galera-declared-as-gr';
    }

    public function category(): string
    {
        return 'topology';
    }

    public function run(object $db, array $ctx = []): array
    {
        $sql = "SELECT s.hostgroup_id, s.hostname, s.port "
             . "FROM runtime_mysql_servers s "
             . "WHERE s.hostgroup_id IN ("
             .   "SELECT hostgroup FROM runtime_mysql_group_replication_hostgroups"
             . ") "
             . "AND EXISTS ("
             .   "SELECT 1 FROM runtime_mysql_galera_hostgroups g "
             .    "WHERE g.writer_hostgroup = s.hostgroup_id "
             .       "OR g.reader_hostgroup = s.hostgroup_id "
             .       "OR g.backup_writer_hostgroup = s.hostgroup_id "
             .       "OR g.offline_hostgroup = s.hostgroup_id"
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
                    '%d backend(s) declared in BOTH Galera and Group Replication hostgroups',
                    count($offenders)
                ),
                $sql . "\n  → " . implode("\n  → ", $offenders),
                "DELETE FROM mysql_group_replication_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "LOAD MYSQL SERVERS TO RUNTIME;\n"
                . "(or the symmetric DELETE on mysql_galera_hostgroups — keep exactly one)",
                '#107'
            ),
        ];
    }
}
