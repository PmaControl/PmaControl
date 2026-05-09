<?php

namespace App\Controller;

use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;

/**
 * Epic #799 / lot 6 — operator-facing dashboard for one NDB cluster.
 *
 * The route `/NdbCluster/index/<id>` is linked from the dot3 cluster
 * shape (lot 5). It lists the three role bands (mgmd / data / sql),
 * the status reported by the mgmd parser (lot 3), the memory usage
 * collected from `ndbinfo.memoryusage` on the SQL/API node (lot 4),
 * and the linked mysql_server row (so operators can jump to the usual
 * /MysqlServer/main page for the SQL endpoint).
 *
 * No mutation here — the page is read-only.
 */
class NdbCluster extends Controller
{
    public function index($param)
    {
        $idCluster = isset($param[0]) ? (int) $param[0] : 0;

        $this->title = '<i class="fa fa-cubes"></i> NDB Cluster';

        $db = Sgbd::sql(DB_DEFAULT);

        if ($idCluster <= 0) {
            $this->set('data', [
                'cluster' => null,
                'nodes'   => [],
                'sql_servers' => [],
                'errors'  => ['Invalid cluster id.'],
            ]);
            return;
        }

        $sql = "SELECT id, display_name, mgmd_host, mgmd_port, ssh_target_host, "
            . "ssh_target_port, ssh_login, command_template, ndb_version, "
            . "no_of_replicas, date_inserted, date_updated "
            . "FROM `ndb_cluster` WHERE id = " . $idCluster . " AND is_deleted = 0";
        $res = $db->sql_query($sql);
        $cluster = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : false;

        if (!$cluster) {
            $this->set('data', [
                'cluster' => null,
                'nodes'   => [],
                'sql_servers' => [],
                'errors'  => ['NDB cluster ' . $idCluster . ' not found or deleted.'],
            ]);
            return;
        }

        $sql = "SELECT ndb_node_id, role, hostname, ip, port, node_group, "
            . "is_primary, status, ndb_version, uptime_sec, "
            . "memory_used_mb, memory_total_mb, data_memory_used_mb, "
            . "index_memory_used_mb, date_seen "
            . "FROM `ndb_node` WHERE id_ndb_cluster = " . $idCluster . " "
            . "ORDER BY FIELD(role,'mgmd','data','sql'), node_group, ndb_node_id";
        $res = $db->sql_query($sql);

        $nodes = ['mgmd' => [], 'data' => [], 'sql' => []];
        $totals = [
            'mgmd' => 0, 'mgmd_up' => 0,
            'data' => 0, 'data_up' => 0,
            'sql'  => 0, 'sql_up'  => 0,
        ];
        if ($res) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $role = (string)($row['role'] ?? '');
                if (!isset($nodes[$role])) {
                    continue;
                }
                $up = (strtoupper((string)$row['status']) === 'STARTED'
                    || strtoupper((string)$row['status']) === 'CONNECTED');
                $row['_up'] = $up;
                $nodes[$role][] = $row;
                $totals[$role]++;
                if ($up) {
                    $totals[$role.'_up']++;
                }
            }
        }

        // Linked SQL/API mysql_server rows (so the operator can jump to /MysqlServer/main).
        $sql = "SELECT m.id, m.display_name, m.ip, m.port "
            . "FROM `ndb_cluster__mysql_server` l "
            . "JOIN `mysql_server` m ON m.id = l.id_mysql_server "
            . "WHERE l.id_ndb_cluster = " . $idCluster;
        $res = $db->sql_query($sql);
        $sqlServers = [];
        if ($res) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $sqlServers[(int)$row['id']] = $row;
            }
        }

        $this->set('data', [
            'cluster'     => $cluster,
            'nodes'       => $nodes,
            'totals'      => $totals,
            'sql_servers' => $sqlServers,
            'errors'      => [],
        ]);
    }
}
