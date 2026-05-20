<?php

declare(strict_types=1);

namespace App\Service\Ndb;

use App\Library\Ndb\MgmShowParser;

/**
 * Epic #799 / lot 3 — orchestrates one collection cycle for one
 * `ndb_cluster` row:
 *
 *   1. fetch raw `ndb_mgm -e show` output via NdbMgmShowFetcher,
 *   2. parse via MgmShowParser,
 *   3. UPSERT each node row into `ndb_node` (idempotent on the
 *      composite UNIQUE (id_ndb_cluster, ndb_node_id)).
 *
 * Pure orchestration — no I/O directly. The fetcher is injected and
 * the DB layer is passed in (Glial-shaped: `sql_query`,
 * `sql_real_escape_string`), so tests can stub everything.
 */
final class NdbCollector
{
    private NdbMgmShowFetcher $fetcher;

    public function __construct(?NdbMgmShowFetcher $fetcher = null)
    {
        $this->fetcher = $fetcher ?? new NdbMgmShowFetcher();
    }

    /**
     * @param object              $db      Glial DB link (sql_query / sql_real_escape_string)
     * @param array<string,mixed> $cluster Row from `ndb_cluster`
     * @return array{
     *   reachable:bool, parsed_nodes:int, upserted:int,
     *   errors:list<string>, command:string,
     *   alerts_opened:int, alerts_resolved:int
     * }
     */
    public function collect(object $db, array $cluster): array
    {
        $idCluster = (int) ($cluster['id'] ?? 0);
        if ($idCluster <= 0) {
            return [
                'reachable'       => false,
                'parsed_nodes'    => 0,
                'upserted'        => 0,
                'errors'          => ['NdbCollector: missing cluster id'],
                'command'         => '',
                'alerts_opened'   => 0,
                'alerts_resolved' => 0,
            ];
        }

        $fetched = $this->fetcher->fetch($cluster);
        $parsed  = MgmShowParser::parse($fetched['stdout']);

        $upserted = 0;
        foreach ($parsed['nodes'] as $node) {
            $sql = self::buildUpsertSql($idCluster, $node, $db);
            $db->sql_query($sql);
            $upserted++;
        }

        // Persist the cluster-level metadata (version) when the parser
        // could derive one from the live nodes.
        if ($parsed['cluster_version'] !== null) {
            $sql = "UPDATE `ndb_cluster` SET `ndb_version` = '"
                . self::escape((string) $parsed['cluster_version'], $db)
                . "' WHERE `id` = " . $idCluster;
            $db->sql_query($sql);
        }

        // Lot 7: emit alerts based on the freshly-UPSERTed node rows.
        // We re-read instead of trusting `$parsed` because the row may have
        // been refined by NdbInfoCollector between cycles (memory columns).
        $alerts = ['opened' => 0, 'resolved' => 0];
        if ($parsed['reachable']) {
            $nodes = $this->loadNodes($db, $idCluster);
            $alerts = (new NdbAlertEmitter())->emit($db, $cluster, $nodes);
        }

        return [
            'reachable'       => $parsed['reachable'],
            'parsed_nodes'    => count($parsed['nodes']),
            'upserted'        => $upserted,
            'errors'          => $parsed['errors'],
            'command'         => $fetched['command'],
            'alerts_opened'   => $alerts['opened'],
            'alerts_resolved' => $alerts['resolved'],
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function loadNodes(object $db, int $idCluster): array
    {
        $sql = "SELECT ndb_node_id, role, hostname, ip, port, node_group, "
            . "is_primary, status, ndb_version, uptime_sec, "
            . "memory_used_mb, memory_total_mb, data_memory_used_mb, index_memory_used_mb "
            . "FROM `ndb_node` WHERE id_ndb_cluster = " . $idCluster;
        $res = $db->sql_query($sql);
        if (!$res) {
            return [];
        }

        $rows = [];
        if (method_exists($db, 'sql_fetch_array')) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }
        } elseif (is_iterable($res)) {
            foreach ($res as $row) {
                $rows[] = (array) $row;
            }
        }
        return $rows;
    }

    /**
     * Public so unit tests can pin the SQL shape exactly (UPSERT key,
     * column order, NULL handling for node_group / ndb_version).
     *
     * @param array{
     *   ndb_node_id:int, role:string, ip:string, status:string,
     *   node_group:?int, is_primary:bool, ndb_version:?string
     * } $node
     */
    public static function buildUpsertSql(int $idCluster, array $node, object $db): string
    {
        $role        = self::escape((string) $node['role'], $db);
        $ip          = self::escape((string) $node['ip'], $db);
        $status      = self::escape((string) $node['status'], $db);
        $nodeGroup   = $node['node_group'] === null ? 'NULL' : (string) (int) $node['node_group'];
        $isPrimary   = $node['is_primary'] ? 1 : 0;
        $ndbVersion  = $node['ndb_version'] === null
            ? 'NULL'
            : "'" . self::escape((string) $node['ndb_version'], $db) . "'";

        return "INSERT INTO `ndb_node` "
            . "(`id_ndb_cluster`, `ndb_node_id`, `role`, `ip`, `status`, "
            . "`node_group`, `is_primary`, `ndb_version`) VALUES ("
            . $idCluster . ", "
            . (int) $node['ndb_node_id'] . ", "
            . "'" . $role . "', "
            . "'" . $ip . "', "
            . "'" . $status . "', "
            . $nodeGroup . ", "
            . $isPrimary . ", "
            . $ndbVersion . ") "
            . "ON DUPLICATE KEY UPDATE "
            . "`role` = VALUES(`role`), "
            . "`ip` = VALUES(`ip`), "
            . "`status` = VALUES(`status`), "
            . "`node_group` = VALUES(`node_group`), "
            . "`is_primary` = VALUES(`is_primary`), "
            . "`ndb_version` = COALESCE(VALUES(`ndb_version`), `ndb_version`), "
            . "`date_seen` = CURRENT_TIMESTAMP";
    }

    private static function escape(string $value, object $db): string
    {
        if (method_exists($db, 'sql_real_escape_string')) {
            return (string) $db->sql_real_escape_string($value);
        }
        // Fallback for stub DBs in tests that don't expose the escape
        // helper — addslashes is "good enough" for the canonical text
        // we feed (alphanumeric + dots + colons).
        return addslashes($value);
    }
}
