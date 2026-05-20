<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Topology;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #903 — flags any single (hostname, port) backend that ends up
 * across more than one of `mysql_galera_hostgroups`,
 * `mysql_group_replication_hostgroups`, `mysql_replication_hostgroups`.
 *
 * ProxySQL would run multiple monitor types against it and the
 * verdicts race — flapping writers, conflicting SHUNNED transitions.
 */
final class BackendInMultipleClusterTypes implements AuditCheck
{
    public function id(): string
    {
        return 'topology.backend-in-multiple-cluster-types';
    }

    public function category(): string
    {
        return 'topology';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        // For every backend, count how many distinct cluster-type tables
        // claim its hostgroup. > 1 = misconfig.
        $sql = "SELECT s.hostname, s.port, "
             .   "SUM(CASE WHEN g.hg IS NOT NULL THEN 1 ELSE 0 END) AS in_galera, "
             .   "SUM(CASE WHEN gr.hg IS NOT NULL THEN 1 ELSE 0 END) AS in_gr, "
             .   "SUM(CASE WHEN rr.hg IS NOT NULL THEN 1 ELSE 0 END) AS in_repl "
             . "FROM runtime_mysql_servers s "
             . "LEFT JOIN ("
             .   "SELECT writer_hostgroup AS hg FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT reader_hostgroup FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT backup_writer_hostgroup FROM runtime_mysql_galera_hostgroups "
             .   "UNION SELECT offline_hostgroup FROM runtime_mysql_galera_hostgroups"
             . ") g ON g.hg = s.hostgroup_id "
             . "LEFT JOIN ("
             .   "SELECT hostgroup AS hg FROM runtime_mysql_group_replication_hostgroups"
             . ") gr ON gr.hg = s.hostgroup_id "
             . "LEFT JOIN ("
             .   "SELECT writer_hostgroup AS hg FROM runtime_mysql_replication_hostgroups "
             .   "UNION SELECT reader_hostgroup FROM runtime_mysql_replication_hostgroups"
             . ") rr ON rr.hg = s.hostgroup_id "
             . "GROUP BY s.hostname, s.port "
             . "HAVING (CASE WHEN in_galera>0 THEN 1 ELSE 0 END "
             .       "+ CASE WHEN in_gr>0 THEN 1 ELSE 0 END "
             .       "+ CASE WHEN in_repl>0 THEN 1 ELSE 0 END) > 1";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $offenders = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $types = [];
            if (!empty($row['in_galera'])) $types[] = 'Galera';
            if (!empty($row['in_gr']))     $types[] = 'GroupReplication';
            if (!empty($row['in_repl']))   $types[] = 'Replication';
            $offenders[] = sprintf(
                '%s:%s in {%s}',
                (string) ($row['hostname'] ?? '?'),
                (string) ($row['port']     ?? '?'),
                implode(',', $types)
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
                sprintf('%d backend(s) declared in more than one cluster type', count($offenders)),
                $sql . "\n  → " . implode("\n  → ", $offenders),
                "Pick exactly one cluster type and DELETE the others:\n"
                . "  DELETE FROM mysql_galera_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "  DELETE FROM mysql_group_replication_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "  DELETE FROM mysql_replication_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            ),
        ];
    }
}
