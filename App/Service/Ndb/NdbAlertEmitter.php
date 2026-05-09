<?php

declare(strict_types=1);

namespace App\Service\Ndb;

/**
 * Epic #799 / lot 7 — turn an NDB collection result into rows in
 * `pmc_event_alert`. Pure orchestration, no I/O directly: the DB
 * handle is injected so tests can stub it.
 *
 * Alert codes map (from the Epic #799 spec, comment 1):
 *
 *   NDB_DATA_NODE_DOWN    warning  : 1 data node !STARTED, NG still has 1+ up
 *   NDB_NODEGROUP_LOST    critical : every data node of a node group is down
 *   NDB_MGMD_DOWN         warning  : 1 mgmd !CONNECTED (multi-mgmd cluster)
 *   NDB_NO_ARBITRATOR     critical : every mgmd !CONNECTED
 *   NDB_SQL_NODE_DOWN     info     : 1 SQL/API !CONNECTED, others up
 *   NDB_MEMORY_PRESSURE   warning  : data node memory_used / memory_total > 0.9
 *   NDB_MEMORY_FULL       critical : data node memory_used / memory_total > 0.98
 *
 * The fingerprint is `sha256(code + ':' + entity)` so re-emitting the
 * same alert in the next collection cycle UPDATEs the open row instead
 * of creating a new one. Auto-resolve: any open alert for this cluster
 * whose code is no longer in the live set is moved to `status=resolved`
 * with `date_end=NOW(6)`.
 */
final class NdbAlertEmitter
{
    public const SOURCE = 'ndb_collector';
    public const CATEGORY = 'ndb';

    /**
     * Compute the live alert set from the per-cluster snapshot we already
     * have on hand (cluster row + ndb_node rows).
     *
     * @param array<string,mixed>             $cluster Row from `ndb_cluster`
     * @param list<array<string,mixed>>       $nodes   Rows from `ndb_node` for this cluster
     * @return list<array{code:string, severity:string, title:string, message:string, entity:string, context:array<string,mixed>}>
     */
    public static function computeAlerts(array $cluster, array $nodes): array
    {
        $alerts = [];

        $byRole = ['mgmd' => [], 'data' => [], 'sql' => []];
        $byNg   = [];
        foreach ($nodes as $n) {
            $role = (string)($n['role'] ?? '');
            if (!isset($byRole[$role])) {
                continue;
            }
            $byRole[$role][] = $n;
            if ($role === 'data') {
                $ng = $n['node_group'] === null ? -1 : (int)$n['node_group'];
                $byNg[$ng][] = $n;
            }
        }

        $up = static function (array $n): bool {
            $s = strtoupper((string)($n['status'] ?? ''));
            return $s === 'STARTED' || $s === 'CONNECTED';
        };

        // Node groups: critical if every data node of a group is down.
        // Otherwise warning per individual data node not started.
        foreach ($byNg as $ng => $members) {
            $upMembers = array_filter($members, $up);
            if (count($upMembers) === 0 && count($members) > 0) {
                $alerts[] = [
                    'code'     => 'NDB_NODEGROUP_LOST',
                    'severity' => 'critical',
                    'title'    => 'NDB node group ' . ($ng < 0 ? '(none)' : (string)$ng) . ' is fully down',
                    'message'  => 'Every data node of node group ' . ($ng < 0 ? '(none)' : (string)$ng)
                        . ' is in a non-STARTED state. The cluster cannot serve queries '
                        . 'that target the fragments owned by this group.',
                    'entity'   => 'ng:' . $ng,
                    'context'  => [
                        'node_group' => $ng,
                        'members'    => array_map(static fn ($m) => (int)$m['ndb_node_id'], $members),
                    ],
                ];
                continue;
            }
            foreach ($members as $n) {
                if (!$up($n)) {
                    $alerts[] = [
                        'code'     => 'NDB_DATA_NODE_DOWN',
                        'severity' => 'warning',
                        'title'    => 'NDB data node ' . (int)$n['ndb_node_id'] . ' is ' . (string)$n['status'],
                        'message'  => 'Data node ' . (int)$n['ndb_node_id']
                            . ' (' . (string)$n['ip'] . ') reports status '
                            . (string)$n['status'] . '. The cluster is still serving '
                            . 'queries because at least one peer of node group '
                            . ($n['node_group'] === null ? '(none)' : (string)$n['node_group'])
                            . ' is up.',
                        'entity'   => 'node:' . (int)$n['ndb_node_id'],
                        'context'  => [
                            'ndb_node_id' => (int)$n['ndb_node_id'],
                            'role'        => 'data',
                            'status'      => (string)$n['status'],
                            'node_group'  => $n['node_group'] === null ? null : (int)$n['node_group'],
                        ],
                    ];
                }
            }
        }

        // mgmd
        $mgmdUp = array_filter($byRole['mgmd'], $up);
        if (count($byRole['mgmd']) > 0 && count($mgmdUp) === 0) {
            $alerts[] = [
                'code'     => 'NDB_NO_ARBITRATOR',
                'severity' => 'critical',
                'title'    => 'No NDB management node reachable',
                'message'  => 'Every mgmd is in a non-CONNECTED state. The cluster is '
                    . 'still serving queries via the data plane but cannot arbitrate '
                    . 'split-brain situations or accept new node joins.',
                'entity'   => 'mgmd:all',
                'context'  => ['mgmd_total' => count($byRole['mgmd'])],
            ];
        } else {
            foreach ($byRole['mgmd'] as $n) {
                if (!$up($n)) {
                    $alerts[] = [
                        'code'     => 'NDB_MGMD_DOWN',
                        'severity' => 'warning',
                        'title'    => 'NDB mgmd ' . (int)$n['ndb_node_id'] . ' is ' . (string)$n['status'],
                        'message'  => 'Management node ' . (int)$n['ndb_node_id']
                            . ' (' . (string)$n['ip'] . ') reports status '
                            . (string)$n['status'] . '. At least one other mgmd is still up.',
                        'entity'   => 'mgmd:' . (int)$n['ndb_node_id'],
                        'context'  => [
                            'ndb_node_id' => (int)$n['ndb_node_id'],
                            'role'        => 'mgmd',
                            'status'      => (string)$n['status'],
                        ],
                    ];
                }
            }
        }

        // SQL/API
        $sqlUp = array_filter($byRole['sql'], $up);
        $sqlTotal = count($byRole['sql']);
        if ($sqlTotal > 0) {
            foreach ($byRole['sql'] as $n) {
                if (!$up($n) && count($sqlUp) >= 1) {
                    $alerts[] = [
                        'code'     => 'NDB_SQL_NODE_DOWN',
                        'severity' => 'info',
                        'title'    => 'NDB SQL/API node ' . (int)$n['ndb_node_id'] . ' is ' . (string)$n['status'],
                        'message'  => 'SQL/API node ' . (int)$n['ndb_node_id']
                            . ' (' . (string)$n['ip'] . ') reports status '
                            . (string)$n['status'] . '. Other SQL/API nodes are still up.',
                        'entity'   => 'sql:' . (int)$n['ndb_node_id'],
                        'context'  => [
                            'ndb_node_id' => (int)$n['ndb_node_id'],
                            'role'        => 'sql',
                            'status'      => (string)$n['status'],
                        ],
                    ];
                }
            }
        }

        // Memory pressure / full per data node.
        foreach ($byRole['data'] as $n) {
            $used  = $n['memory_used_mb']  ?? null;
            $total = $n['memory_total_mb'] ?? null;
            if ($used === null || $total === null || (int)$total <= 0) {
                continue;
            }
            $ratio = ((int)$used) / ((int)$total);
            if ($ratio > 0.98) {
                $alerts[] = [
                    'code'     => 'NDB_MEMORY_FULL',
                    'severity' => 'critical',
                    'title'    => 'NDB data node ' . (int)$n['ndb_node_id'] . ' memory at ' . (int)round($ratio * 100) . '%',
                    'message'  => 'Data node ' . (int)$n['ndb_node_id'] . ' uses '
                        . (int)$used . '/' . (int)$total . ' MB of memory. NDB writes '
                        . 'will start failing imminently — provision more DataMemory '
                        . 'or shed load.',
                    'entity'   => 'node:' . (int)$n['ndb_node_id'] . ':mem',
                    'context'  => [
                        'ndb_node_id' => (int)$n['ndb_node_id'],
                        'used_mb'     => (int)$used,
                        'total_mb'    => (int)$total,
                        'ratio'       => round($ratio, 4),
                    ],
                ];
            } elseif ($ratio > 0.9) {
                $alerts[] = [
                    'code'     => 'NDB_MEMORY_PRESSURE',
                    'severity' => 'warning',
                    'title'    => 'NDB data node ' . (int)$n['ndb_node_id'] . ' memory at ' . (int)round($ratio * 100) . '%',
                    'message'  => 'Data node ' . (int)$n['ndb_node_id'] . ' uses '
                        . (int)$used . '/' . (int)$total . ' MB of memory.',
                    'entity'   => 'node:' . (int)$n['ndb_node_id'] . ':mem',
                    'context'  => [
                        'ndb_node_id' => (int)$n['ndb_node_id'],
                        'used_mb'     => (int)$used,
                        'total_mb'    => (int)$total,
                        'ratio'       => round($ratio, 4),
                    ],
                ];
            }
        }

        return $alerts;
    }

    /**
     * Persist the live alert set into `pmc_event_alert`. Idempotent —
     * re-running the same collection cycle leaves rows unchanged. Any
     * previously-open alert whose code+entity is no longer in the live
     * set is auto-resolved.
     *
     * @param object $db      Glial DB link (sql_query / sql_real_escape_string)
     * @param array<string,mixed>          $cluster Row from `ndb_cluster`
     * @param list<array<string,mixed>>    $nodes   Rows from `ndb_node` for this cluster
     * @return array{opened:int, resolved:int}
     */
    public function emit(object $db, array $cluster, array $nodes): array
    {
        $idCluster = (int) ($cluster['id'] ?? 0);
        if ($idCluster <= 0) {
            return ['opened' => 0, 'resolved' => 0];
        }

        $alerts = self::computeAlerts($cluster, $nodes);

        // Build set of live fingerprints to drive auto-resolve below.
        $liveFingerprints = [];
        foreach ($alerts as $a) {
            $liveFingerprints[self::fingerprintHex($idCluster, $a['code'], $a['entity'])] = true;
        }

        $opened = 0;
        foreach ($alerts as $a) {
            $sql = self::buildUpsertSql($db, $idCluster, $a, (string)($cluster['display_name'] ?? ''));
            $db->sql_query($sql);
            $opened++;
        }

        $resolved = $this->resolveStaleAlerts($db, $idCluster, $liveFingerprints);

        return ['opened' => $opened, 'resolved' => $resolved];
    }

    /**
     * Public so unit tests can pin the SQL shape exactly.
     *
     * @param array{code:string, severity:string, title:string, message:string, entity:string, context:array<string,mixed>} $a
     */
    public static function buildUpsertSql(object $db, int $idCluster, array $a, string $displayName): string
    {
        $fpHex = self::fingerprintHex($idCluster, $a['code'], $a['entity']);
        $title = self::escape($db, $a['title']);
        $message = self::escape($db, $a['message']);
        $source = self::escape($db, self::SOURCE);
        $category = self::escape($db, self::CATEGORY);
        $severity = self::escape($db, $a['severity']);
        $labels = self::escape($db, json_encode([
            'ndb_cluster_id'   => $idCluster,
            'ndb_cluster_name' => $displayName,
            'code'             => $a['code'],
        ], JSON_UNESCAPED_SLASHES));
        $context = self::escape($db, json_encode($a['context'], JSON_UNESCAPED_SLASHES));

        return "INSERT INTO `pmc_event_alert` "
            . "(`scope`, `kind`, `severity`, `status`, `date_start`, `title`, "
            . "`message`, `source`, `category`, `fingerprint`, `entity_type`, "
            . "`entity_id`, `labels`, `context`) VALUES ("
            . "'system', 'alert', '" . $severity . "', 'open', NOW(6), '"
            . $title . "', '" . $message . "', '" . $source . "', '"
            . $category . "', UNHEX('" . $fpHex . "'), 'ndb_cluster', "
            . $idCluster . ", '" . $labels . "', '" . $context . "') "
            . "ON DUPLICATE KEY UPDATE "
            . "`severity` = VALUES(`severity`), "
            . "`title` = VALUES(`title`), "
            . "`message` = VALUES(`message`), "
            . "`labels` = VALUES(`labels`), "
            . "`context` = VALUES(`context`), "
            . "`status` = IF(`status` IN ('resolved','closed'), 'open', `status`), "
            . "`date_end` = IF(`status` IN ('resolved','closed'), NULL, `date_end`), "
            . "`updated_at` = NOW(6)";
    }

    public static function fingerprintHex(int $idCluster, string $code, string $entity): string
    {
        return hash('sha256', 'ndb:' . $idCluster . ':' . $code . ':' . $entity);
    }

    /**
     * Mark previously-open alerts as resolved when they are no longer in
     * the live set.
     *
     * @param array<string,bool> $liveFingerprintSet  hex → true
     */
    private function resolveStaleAlerts(object $db, int $idCluster, array $liveFingerprintSet): int
    {
        $sql = "SELECT id, HEX(`fingerprint`) AS fp_hex FROM `pmc_event_alert` "
            . "WHERE `source` = '" . self::escape($db, self::SOURCE) . "' "
            . "AND `entity_type` = 'ndb_cluster' AND `entity_id` = " . $idCluster
            . " AND `status` = 'open'";
        $res = $db->sql_query($sql);
        if (!$res) {
            return 0;
        }

        $stale = [];
        if (method_exists($db, 'sql_fetch_array')) {
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $hex = strtolower((string)($row['fp_hex'] ?? ''));
                if ($hex === '' || isset($liveFingerprintSet[$hex])) {
                    continue;
                }
                $stale[] = (int)$row['id'];
            }
        }

        if (empty($stale)) {
            return 0;
        }

        $db->sql_query(
            "UPDATE `pmc_event_alert` SET `status` = 'resolved', `date_end` = NOW(6), "
            . "`updated_at` = NOW(6) WHERE id IN (" . implode(',', $stale) . ")"
        );

        return count($stale);
    }

    private static function escape(object $db, string $value): string
    {
        if (method_exists($db, 'sql_real_escape_string')) {
            return (string) $db->sql_real_escape_string($value);
        }
        return addslashes($value);
    }
}
