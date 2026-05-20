<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Topology;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #901 — flags backends wired into a cluster hostgroup that may
 * actually be plain async-replication servers.
 *
 * The deterministic version (probe `wsrep_*` / `group_replication_*`
 * variables on each backend) needs cross-source data we don't yet
 * have a clean accessor for. Ship a conservative `warning` that
 * lists the backends so the operator can spot-check them; tighten
 * to a per-server `critical` once the backend-variables accessor
 * lands.
 */
final class AsyncReplicationAsClusterTable implements AuditCheck
{
    public function id(): string
    {
        return 'topology.async-declared-as-cluster';
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

        $sql = "SELECT DISTINCT s.hostname, s.port "
             . "FROM runtime_mysql_servers s "
             . "WHERE s.hostgroup_id IN ("
             .   "SELECT hostgroup FROM runtime_mysql_group_replication_hostgroups "
             .   "UNION ALL "
             .   "SELECT writer_hostgroup FROM runtime_mysql_galera_hostgroups "
             .   "UNION ALL "
             .   "SELECT reader_hostgroup FROM runtime_mysql_galera_hostgroups "
             .   "UNION ALL "
             .   "SELECT backup_writer_hostgroup FROM runtime_mysql_galera_hostgroups"
             . ")";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $candidates = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $hostname = (string) ($row['hostname'] ?? '');
            $port     = (int) ($row['port'] ?? 0);
            if ($hostname === '' || $port <= 0) {
                continue;
            }
            $candidates[] = $hostname . ':' . $port;
        }

        if (empty($candidates)) {
            return [];
        }

        return [
            new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                'Verify async vs cluster topology for the registered hostgroups',
                $sql . "\n  → " . implode("\n  → ", $candidates),
                "Spot-check `wsrep_local_state` / `group_replication_primary_member` on each backend.\n"
                . "If any is plain async replication, drop it from the cluster table:\n"
                . "  DELETE FROM mysql_galera_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "  DELETE FROM mysql_group_replication_hostgroups WHERE writer_hostgroup IN (...);\n"
                . "  LOAD MYSQL SERVERS TO RUNTIME;",
                null
            ),
        ];
    }
}
