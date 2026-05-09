<?php

declare(strict_types=1);

namespace App\Service\Ndb;

/**
 * Epic #799 / lot 4 — refines `ndb_node` rows with the runtime
 * metrics published by the SQL/API node via the `ndbinfo` schema:
 *
 *   - ndbinfo.nodes        → uptime / status corroboration
 *   - ndbinfo.memoryusage  → memory_used / memory_total per data node
 *
 * The mgmd-side parser (lot 3) tells us "who is up" with sub-second
 * accuracy; ndbinfo answers "how busy" — they're complementary.
 *
 * The collector talks to the SQL/API node via the existing mysql_server
 * connection (the same one Aspirateur uses), so no new credentials,
 * no new firewall hole. It's pure SQL.
 *
 * Pure orchestration — DB layer (Glial-shaped) is injected, tests
 * stub it.
 */
final class NdbInfoCollector
{
    /**
     * @param object $sqlNodeDb     mysqli wrapper of the SQL/API node (with ndbinfo schema reachable)
     * @param object $pmacontrolDb  mysqli wrapper of pmacontrol's own DB (where ndb_node lives)
     * @return array{updated:int, errors:list<string>, alerts_opened:int, alerts_resolved:int}
     */
    public function collect(object $sqlNodeDb, object $pmacontrolDb, int $idCluster): array
    {
        $errors  = [];
        $updated = 0;

        // 1) Memory usage, aggregated per data node.
        //    `memoryusage` rows have one entry per (node_id, memory_type).
        //    We sum across types for the "total memory used" snapshot,
        //    and also extract the two principal types (Data memory,
        //    Long message buffer rows are noise — we keep DataMemory and
        //    IndexMemory specifically because operators look at those).
        try {
            $rows = self::fetchAll($sqlNodeDb, "SELECT node_id, memory_type, used, total FROM ndbinfo.memoryusage");
        } catch (\Throwable $e) {
            $errors[] = 'ndbinfo.memoryusage failed: ' . $e->getMessage();
            $rows = [];
        }

        $byNode = [];
        foreach ($rows as $row) {
            $nid  = (int) ($row['node_id'] ?? 0);
            $type = strtolower((string) ($row['memory_type'] ?? ''));
            $used = (int) ($row['used'] ?? 0);
            $tot  = (int) ($row['total'] ?? 0);

            if (!isset($byNode[$nid])) {
                $byNode[$nid] = [
                    'used_bytes'         => 0,
                    'total_bytes'        => 0,
                    'data_used_bytes'    => 0,
                    'index_used_bytes'   => 0,
                ];
            }
            $byNode[$nid]['used_bytes']  += $used;
            $byNode[$nid]['total_bytes'] += $tot;
            if (str_contains($type, 'data memory')) {
                $byNode[$nid]['data_used_bytes'] += $used;
            } elseif (str_contains($type, 'index memory')) {
                $byNode[$nid]['index_used_bytes'] += $used;
            }
        }

        foreach ($byNode as $nid => $totals) {
            $sql = self::buildMemoryUpdateSql($idCluster, $nid, $totals, $pmacontrolDb);
            $pmacontrolDb->sql_query($sql);
            $updated++;
        }

        // 2) Uptime corroboration from ndbinfo.nodes (cheap, even if the
        //    mgmd-side already gave us status — uptime is only here).
        try {
            $rows = self::fetchAll($sqlNodeDb, "SELECT node_id, uptime, status FROM ndbinfo.nodes");
        } catch (\Throwable $e) {
            $errors[] = 'ndbinfo.nodes failed: ' . $e->getMessage();
            $rows = [];
        }

        foreach ($rows as $row) {
            $nid    = (int) ($row['node_id'] ?? 0);
            $uptime = (int) ($row['uptime']  ?? 0);
            if ($nid <= 0) {
                continue;
            }
            $sql = "UPDATE `ndb_node` SET `uptime_sec` = " . $uptime
                . " WHERE `id_ndb_cluster` = " . $idCluster
                . " AND `ndb_node_id` = " . $nid;
            $pmacontrolDb->sql_query($sql);
            $updated++;
        }

        // Re-evaluate alerts now that memory_* columns have refreshed —
        // memory pressure / full conditions are computed off these.
        $alerts = ['opened' => 0, 'resolved' => 0];
        try {
            $cluster = self::fetchCluster($pmacontrolDb, $idCluster);
            if ($cluster !== null) {
                $nodes = self::fetchNodes($pmacontrolDb, $idCluster);
                $alerts = (new NdbAlertEmitter())->emit($pmacontrolDb, $cluster, $nodes);
            }
        } catch (\Throwable $e) {
            $errors[] = 'NdbAlertEmitter (post-ndbinfo) failed: ' . $e->getMessage();
        }

        return [
            'updated'         => $updated,
            'errors'          => $errors,
            'alerts_opened'   => $alerts['opened'],
            'alerts_resolved' => $alerts['resolved'],
        ];
    }

    private static function fetchCluster(object $db, int $idCluster): ?array
    {
        $res = $db->sql_query("SELECT id, display_name FROM `ndb_cluster` WHERE id = " . $idCluster);
        if (!$res) {
            return null;
        }
        if (method_exists($db, 'sql_fetch_array')) {
            $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
            return $row ?: null;
        }
        return null;
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function fetchNodes(object $db, int $idCluster): array
    {
        $res = $db->sql_query(
            "SELECT ndb_node_id, role, hostname, ip, port, node_group, is_primary, status, "
            . "ndb_version, uptime_sec, memory_used_mb, memory_total_mb, "
            . "data_memory_used_mb, index_memory_used_mb "
            . "FROM `ndb_node` WHERE id_ndb_cluster = " . $idCluster
        );
        if (!$res) {
            return [];
        }
        $rows = [];
        if (method_exists($db, 'sql_fetch_array')) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Public so unit tests can pin the SQL shape.
     *
     * @param array{used_bytes:int,total_bytes:int,data_used_bytes:int,index_used_bytes:int} $totals
     */
    public static function buildMemoryUpdateSql(int $idCluster, int $nodeId, array $totals, object $db): string
    {
        $usedMb       = (int) round($totals['used_bytes']       / 1024 / 1024);
        $totalMb      = (int) round($totals['total_bytes']      / 1024 / 1024);
        $dataUsedMb   = (int) round($totals['data_used_bytes']  / 1024 / 1024);
        $indexUsedMb  = (int) round($totals['index_used_bytes'] / 1024 / 1024);

        return "UPDATE `ndb_node` SET "
            . "`memory_used_mb` = "        . $usedMb       . ", "
            . "`memory_total_mb` = "       . $totalMb      . ", "
            . "`data_memory_used_mb` = "   . $dataUsedMb   . ", "
            . "`index_memory_used_mb` = "  . $indexUsedMb  . " "
            . "WHERE `id_ndb_cluster` = "  . $idCluster
            . " AND `ndb_node_id` = "      . $nodeId;
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function fetchAll(object $db, string $sql): array
    {
        if (!method_exists($db, 'sql_query')) {
            throw new \RuntimeException('NdbInfoCollector: DB handle missing sql_query()');
        }
        $res = $db->sql_query($sql);
        if (!$res) {
            return [];
        }

        $out = [];
        if (method_exists($db, 'sql_fetch_array')) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $out[] = $row;
            }
        } elseif (is_iterable($res)) {
            foreach ($res as $row) {
                $out[] = (array) $row;
            }
        }
        return $out;
    }
}
