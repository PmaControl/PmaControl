<?php

namespace App\Library\Reports;

use App\Library\ChartPayload;
use App\Library\Format;
use App\Library\MetricAggregate;
use Glial\Sgbd\Sgbd;

final class BusinessReportCatalog
{
    public const DEFAULT_LIMIT = 10;
    public const MAX_LIMIT = 50;

    public static function getReports(): array
    {
        return [
            'overview' => [
                'slug' => 'overview',
                'title' => 'Overview',
                'description' => 'Cross-domain operational snapshot from local PmaControl data.',
            ],
            'server_load' => [
                'slug' => 'server_load',
                'title' => 'Top servers by load',
                'description' => 'CPU and memory pressure from aggregated time-series metrics.',
            ],
            'replication_lag' => [
                'slug' => 'replication_lag',
                'title' => 'Replication lag',
                'description' => 'Largest known replica lag from collected slave/source metrics.',
            ],
            'database_size' => [
                'slug' => 'database_size',
                'title' => 'Database size',
                'description' => 'Largest schemas by data and index footprint.',
            ],
            'storage_capacity' => [
                'slug' => 'storage_capacity',
                'title' => 'Storage capacity',
                'description' => 'Backup storage areas closest to capacity.',
            ],
            'unused_indexes' => [
                'slug' => 'unused_indexes',
                'title' => 'Digest index signals',
                'description' => 'Statements collected with no-index or no-good-index counters.',
            ],
            'roadmap' => [
                'slug' => 'roadmap',
                'title' => 'Report roadmap',
                'description' => 'BI ideas that need a stronger data source before automation.',
            ],
        ];
    }

    public static function normalizeReport($report): string
    {
        $slug = is_scalar($report) ? strtolower(trim((string)$report)) : '';

        return isset(self::getReports()[$slug]) ? $slug : 'overview';
    }

    public static function normalizeLimit($limit): int
    {
        if (!is_numeric($limit)) {
            return self::DEFAULT_LIMIT;
        }

        $value = (int)$limit;
        if ($value < 1) {
            return self::DEFAULT_LIMIT;
        }

        return min($value, self::MAX_LIMIT);
    }

    public static function build(string $selectedReport = 'overview', array $query = []): array
    {
        $warnings = [];
        $limit = self::normalizeLimit($query['limit'] ?? self::DEFAULT_LIMIT);

        try {
            $db = Sgbd::sql(DB_DEFAULT);
        } catch (\Throwable $exception) {
            return self::buildFromRows([], [], [], [], [], $selectedReport, $limit, [
                'Database connection unavailable: ' . self::sanitizeMessage($exception->getMessage()),
            ]);
        }

        $serverRows = self::safeFetch($db, self::serverSql(), $warnings, 'server inventory');
        $metricRows = self::safeFetch($db, self::metricSql(), $warnings, 'aggregate metrics');
        $databaseRows = self::safeFetch($db, self::databaseSql(), $warnings, 'database inventory');
        $storageRows = self::safeFetch($db, self::storageSql(), $warnings, 'backup storage');
        $digestRows = self::safeFetch($db, self::digestSql(), $warnings, 'digest index signals');

        return self::buildFromRows(
            $serverRows,
            $metricRows,
            $databaseRows,
            $storageRows,
            $digestRows,
            $selectedReport,
            $limit,
            $warnings
        );
    }

    public static function buildFromRows(
        array $serverRows,
        array $metricRows,
        array $databaseRows,
        array $storageRows,
        array $digestRows,
        string $selectedReport = 'overview',
        int $limit = self::DEFAULT_LIMIT,
        array $warnings = []
    ): array {
        $reports = self::getReports();
        $selectedSlug = self::normalizeReport($selectedReport);
        $limit = self::normalizeLimit($limit);
        $servers = self::normalizeServers($serverRows);
        $metricsByServer = self::normalizeMetrics($metricRows);
        $serverLoadRows = self::buildServerLoadRows($servers, $metricsByServer, $limit);
        $lagRows = self::buildReplicationLagRows($servers, $metricsByServer, $limit);
        $databaseRows = self::buildDatabaseRows($databaseRows, $limit);
        $storageRows = self::buildStorageRows($storageRows, $limit);
        $digestRows = self::buildDigestRows($digestRows, $limit);

        $sections = [
            'server_load' => self::serverLoadSection($serverLoadRows),
            'replication_lag' => self::replicationLagSection($lagRows),
            'database_size' => self::databaseSizeSection($databaseRows),
            'storage_capacity' => self::storageCapacitySection($storageRows),
            'unused_indexes' => self::digestIndexSection($digestRows),
            'roadmap' => self::roadmapSection(),
        ];

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'reports' => array_values($reports),
            'selected_report' => $reports[$selectedSlug],
            'limit' => $limit,
            'summary_cards' => self::summaryCards($servers, $serverLoadRows, $lagRows, $databaseRows, $storageRows, $digestRows),
            'sections' => $selectedSlug === 'overview'
                ? array_values($sections)
                : [($sections[$selectedSlug] ?? self::roadmapSection())],
            'warnings' => array_values(array_unique(array_filter($warnings))),
        ];
    }

    private static function serverSql(): string
    {
        return "SELECT ms.id,
                       ms.display_name,
                       ms.name,
                       ms.ip,
                       ms.port,
                       ms.is_proxy,
                       c.libelle AS client,
                       e.libelle AS environment
                FROM mysql_server ms
                LEFT JOIN client c ON c.id = ms.id_client
                LEFT JOIN environment e ON e.id = ms.id_environment
                WHERE ms.is_deleted = 0
                  AND ms.is_monitored = 1
                ORDER BY c.libelle, e.libelle, ms.display_name, ms.id";
    }

    private static function metricSql(): string
    {
        $table = MetricAggregate::getResolutions()['1h']['table'];

        return "SELECT a.id_mysql_server,
                       v.name,
                       a.series_key,
                       COALESCE(a.value_last, a.value_avg, a.value_max, a.value_min) AS value,
                       a.bucket_start
                FROM `" . $table . "` a
                INNER JOIN ts_variable v ON v.id = a.id_ts_variable
                WHERE (
                    (v.name IN ('cpu_usage', 'memory_used', 'memory_total') AND v.`from` = 'ssh_stats')
                    OR (v.name IN ('seconds_behind_master', 'seconds_behind_source') AND v.`from` = 'slave')
                )
                  AND a.bucket_start = (SELECT MAX(bucket_start) FROM `" . $table . "`)
                ORDER BY a.id_mysql_server, v.name, a.bucket_start, a.series_key";
    }

    private static function databaseSql(): string
    {
        return "SELECT md.id_mysql_server,
                       ms.display_name,
                       c.libelle AS client,
                       e.libelle AS environment,
                       md.schema_name,
                       SUM(md.tables) AS tables_count,
                       SUM(md.rows) AS rows_count,
                       SUM(md.data_length) AS data_bytes,
                       SUM(md.index_length) AS index_bytes,
                       SUM(md.data_length + md.index_length) AS total_bytes
                FROM mysql_database md
                INNER JOIN mysql_server ms ON ms.id = md.id_mysql_server
                LEFT JOIN client c ON c.id = ms.id_client
                LEFT JOIN environment e ON e.id = ms.id_environment
                WHERE ms.is_deleted = 0
                  AND md.schema_name NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
                GROUP BY md.id_mysql_server, ms.display_name, c.libelle, e.libelle, md.schema_name
                ORDER BY total_bytes DESC
                LIMIT 200";
    }

    private static function storageSql(): string
    {
        return "SELECT bsa.id,
                       bsa.libelle,
                       bsa.ip,
                       bsa.port,
                       bsa.path,
                       bss.date,
                       bss.size,
                       bss.used,
                       bss.available,
                       bss.percent,
                       bss.backup
                FROM backup_storage_area bsa
                INNER JOIN backup_storage_space bss ON bss.id_backup_storage_area = bsa.id
                INNER JOIN (
                    SELECT id_backup_storage_area, MAX(date) AS max_date
                    FROM backup_storage_space
                    GROUP BY id_backup_storage_area
                ) latest
                  ON latest.id_backup_storage_area = bss.id_backup_storage_area
                 AND latest.max_date = bss.date
                ORDER BY bss.percent DESC, bsa.libelle
                LIMIT 200";
    }

    private static function digestSql(): string
    {
        return "SELECT mdd.id_mysql_server,
                       ms.display_name,
                       c.libelle AS client,
                       e.libelle AS environment,
                       mdd.schema_name,
                       SUM(stat.count_star) AS count_star,
                       SUM(stat.sum_no_index_used) AS no_index_used,
                       SUM(stat.sum_no_good_index_used) AS no_good_index_used,
                       MAX(stat.last_seen) AS last_seen,
                       MAX(stat.query_sample_text) AS query_sample_text
                FROM ts_mysql_digest_stat stat
                INNER JOIN mysql_database__mysql_digest mdd
                    ON mdd.id = stat.id_mysql_database__mysql_digest
                INNER JOIN mysql_server ms ON ms.id = mdd.id_mysql_server
                LEFT JOIN client c ON c.id = ms.id_client
                LEFT JOIN environment e ON e.id = ms.id_environment
                WHERE stat.date = (SELECT MAX(date) FROM ts_mysql_digest_stat)
                  AND ms.is_deleted = 0
                GROUP BY mdd.id_mysql_server, ms.display_name, c.libelle, e.libelle, mdd.schema_name
                HAVING SUM(stat.sum_no_index_used) + SUM(stat.sum_no_good_index_used) > 0
                ORDER BY SUM(stat.sum_no_index_used) + SUM(stat.sum_no_good_index_used) DESC
                LIMIT 200";
    }

    private static function safeFetch($db, string $sql, array &$warnings, string $label): array
    {
        try {
            $res = $db->sql_query($sql);
            $rows = [];
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }

            return $rows;
        } catch (\Throwable $exception) {
            $warnings[] = ucfirst($label) . ' unavailable: ' . self::sanitizeMessage($exception->getMessage());
            return [];
        }
    }

    private static function normalizeServers(array $serverRows): array
    {
        $servers = [];
        foreach ($serverRows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $servers[$id] = [
                'id' => $id,
                'display_name' => self::label($row['display_name'] ?? $row['name'] ?? ('#' . $id)),
                'client' => self::label($row['client'] ?? '-'),
                'environment' => self::label($row['environment'] ?? '-'),
                'ip' => self::label($row['ip'] ?? '-'),
                'port' => (int)($row['port'] ?? 0),
                'is_proxy' => (int)($row['is_proxy'] ?? 0),
            ];
        }

        return $servers;
    }

    private static function normalizeMetrics(array $metricRows): array
    {
        $metrics = [];
        foreach ($metricRows as $row) {
            $serverId = (int)($row['id_mysql_server'] ?? 0);
            $name = strtolower(trim((string)($row['name'] ?? '')));
            if ($serverId <= 0 || $name === '') {
                continue;
            }

            $bucket = strtotime((string)($row['bucket_start'] ?? '')) ?: 0;
            $value = self::toFloat($row['value'] ?? null);
            if ($value === null) {
                continue;
            }

            $current = $metrics[$serverId][$name] ?? null;
            if ($current === null || $bucket > $current['bucket']) {
                $metrics[$serverId][$name] = ['bucket' => $bucket, 'value' => $value];
                continue;
            }

            if ($bucket === $current['bucket'] && strpos($name, 'seconds_behind_') === 0) {
                $metrics[$serverId][$name]['value'] = max((float)$current['value'], $value);
            }
        }

        return $metrics;
    }

    private static function buildServerLoadRows(array $servers, array $metricsByServer, int $limit): array
    {
        $rows = [];
        foreach ($servers as $serverId => $server) {
            $metrics = $metricsByServer[$serverId] ?? [];
            $cpu = self::metricValue($metrics, 'cpu_usage');
            $memoryUsed = self::metricValue($metrics, 'memory_used');
            $memoryTotal = self::metricValue($metrics, 'memory_total');
            $memoryPercent = $memoryUsed !== null && $memoryTotal !== null && $memoryTotal > 0
                ? ($memoryUsed / $memoryTotal) * 100
                : null;
            $score = max($cpu ?? -1, $memoryPercent ?? -1);

            if ($score < 0) {
                continue;
            }

            $rows[] = [
                'server' => $server,
                'cpu_percent' => $cpu,
                'memory_percent' => $memoryPercent,
                'score' => $score,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return $right['score'] <=> $left['score'];
        });

        return array_slice($rows, 0, $limit);
    }

    private static function buildReplicationLagRows(array $servers, array $metricsByServer, int $limit): array
    {
        $rows = [];
        foreach ($servers as $serverId => $server) {
            $metrics = $metricsByServer[$serverId] ?? [];
            $lag = max(
                self::metricValue($metrics, 'seconds_behind_master') ?? -1,
                self::metricValue($metrics, 'seconds_behind_source') ?? -1
            );

            if ($lag < 0) {
                continue;
            }

            $rows[] = [
                'server' => $server,
                'lag_seconds' => $lag,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return $right['lag_seconds'] <=> $left['lag_seconds'];
        });

        return array_slice($rows, 0, $limit);
    }

    private static function buildDatabaseRows(array $databaseRows, int $limit): array
    {
        $rows = [];
        foreach ($databaseRows as $row) {
            $totalBytes = self::toFloat($row['total_bytes'] ?? null) ?? 0.0;
            $rows[] = [
                'server' => self::label($row['display_name'] ?? '#'.(string)($row['id_mysql_server'] ?? '')),
                'client' => self::label($row['client'] ?? '-'),
                'environment' => self::label($row['environment'] ?? '-'),
                'schema' => self::label($row['schema_name'] ?? '-'),
                'tables' => (int)($row['tables_count'] ?? 0),
                'rows' => (int)($row['rows_count'] ?? 0),
                'data_bytes' => self::toFloat($row['data_bytes'] ?? null) ?? 0.0,
                'index_bytes' => self::toFloat($row['index_bytes'] ?? null) ?? 0.0,
                'total_bytes' => $totalBytes,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return $right['total_bytes'] <=> $left['total_bytes'];
        });

        return array_slice($rows, 0, $limit);
    }

    private static function buildStorageRows(array $storageRows, int $limit): array
    {
        $rows = [];
        foreach ($storageRows as $row) {
            $rows[] = [
                'name' => self::label($row['libelle'] ?? '#'.(string)($row['id'] ?? '')),
                'endpoint' => self::label(($row['ip'] ?? '-') . ':' . ($row['port'] ?? '-')),
                'path' => self::label($row['path'] ?? '-'),
                'date' => self::label($row['date'] ?? '-'),
                'size' => self::toFloat($row['size'] ?? null) ?? 0.0,
                'used' => self::toFloat($row['used'] ?? null) ?? 0.0,
                'available' => self::toFloat($row['available'] ?? null) ?? 0.0,
                'percent' => self::toFloat($row['percent'] ?? null) ?? 0.0,
                'backup' => self::toFloat($row['backup'] ?? null) ?? 0.0,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return $right['percent'] <=> $left['percent'];
        });

        return array_slice($rows, 0, $limit);
    }

    private static function buildDigestRows(array $digestRows, int $limit): array
    {
        $rows = [];
        foreach ($digestRows as $row) {
            $noIndex = (int)($row['no_index_used'] ?? 0);
            $noGoodIndex = (int)($row['no_good_index_used'] ?? 0);
            $rows[] = [
                'server' => self::label($row['display_name'] ?? '#'.(string)($row['id_mysql_server'] ?? '')),
                'client' => self::label($row['client'] ?? '-'),
                'environment' => self::label($row['environment'] ?? '-'),
                'schema' => self::label($row['schema_name'] ?? '-'),
                'count_star' => (int)($row['count_star'] ?? 0),
                'no_index_used' => $noIndex,
                'no_good_index_used' => $noGoodIndex,
                'score' => $noIndex + $noGoodIndex,
                'last_seen' => self::label($row['last_seen'] ?? '-'),
                'sample' => self::shortenSql((string)($row['query_sample_text'] ?? '')),
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return $right['score'] <=> $left['score'];
        });

        return array_slice($rows, 0, $limit);
    }

    private static function serverLoadSection(array $rows): array
    {
        return [
            'slug' => 'server_load',
            'title' => 'Top servers by load',
            'description' => 'Current 1h aggregate pressure. Source: aggregate metrics only, no remote fan-out.',
            'charts' => [
                self::chart(
                    'reports-server-load',
                    'CPU and memory pressure',
                    'bar',
                    'percent',
                    array_map(static function (array $row): string {
                        return $row['server']['display_name'];
                    }, $rows),
                    [
                        'CPU %' => array_map(static function (array $row): ?float {
                            return $row['cpu_percent'];
                        }, $rows),
                        'Memory %' => array_map(static function (array $row): ?float {
                            return $row['memory_percent'];
                        }, $rows),
                    ]
                ),
            ],
            'tables' => [[
                'title' => 'Server pressure',
                'columns' => ['Server', 'Client', 'Environment', 'CPU', 'Memory', 'Endpoint'],
                'rows' => array_map(static function (array $row): array {
                    return [
                        $row['server']['display_name'],
                        $row['server']['client'],
                        $row['server']['environment'],
                        self::formatPercent($row['cpu_percent']),
                        self::formatPercent($row['memory_percent']),
                        $row['server']['ip'] . ':' . $row['server']['port'],
                    ];
                }, $rows),
            ]],
        ];
    }

    private static function replicationLagSection(array $rows): array
    {
        return [
            'slug' => 'replication_lag',
            'title' => 'Replication lag',
            'description' => 'Largest seconds_behind_master/source value per monitored server.',
            'charts' => [
                self::chart(
                    'reports-replication-lag',
                    'Lag by server',
                    'bar',
                    'seconds',
                    array_map(static function (array $row): string {
                        return $row['server']['display_name'];
                    }, $rows),
                    [
                        'Lag seconds' => array_map(static function (array $row): float {
                            return (float)$row['lag_seconds'];
                        }, $rows),
                    ]
                ),
            ],
            'tables' => [[
                'title' => 'Replica lag',
                'columns' => ['Server', 'Client', 'Environment', 'Lag', 'Endpoint'],
                'rows' => array_map(static function (array $row): array {
                    return [
                        $row['server']['display_name'],
                        $row['server']['client'],
                        $row['server']['environment'],
                        self::formatSeconds($row['lag_seconds']),
                        $row['server']['ip'] . ':' . $row['server']['port'],
                    ];
                }, $rows),
            ]],
        ];
    }

    private static function databaseSizeSection(array $rows): array
    {
        return [
            'slug' => 'database_size',
            'title' => 'Database size',
            'description' => 'Largest schemas from mysql_database snapshots.',
            'charts' => [
                self::chart(
                    'reports-database-size',
                    'Data + index size',
                    'bar',
                    'bytes',
                    array_map(static function (array $row): string {
                        return $row['server'] . ' / ' . $row['schema'];
                    }, $rows),
                    [
                        'Data' => array_map(static function (array $row): float {
                            return (float)$row['data_bytes'];
                        }, $rows),
                        'Index' => array_map(static function (array $row): float {
                            return (float)$row['index_bytes'];
                        }, $rows),
                    ]
                ),
            ],
            'tables' => [[
                'title' => 'Largest schemas',
                'columns' => ['Server', 'Client', 'Environment', 'Schema', 'Tables', 'Rows', 'Data', 'Index', 'Total'],
                'rows' => array_map(static function (array $row): array {
                    return [
                        $row['server'],
                        $row['client'],
                        $row['environment'],
                        $row['schema'],
                        (string)$row['tables'],
                        number_format((int)$row['rows']),
                        self::formatBytes($row['data_bytes']),
                        self::formatBytes($row['index_bytes']),
                        self::formatBytes($row['total_bytes']),
                    ];
                }, $rows),
            ]],
        ];
    }

    private static function storageCapacitySection(array $rows): array
    {
        return [
            'slug' => 'storage_capacity',
            'title' => 'Storage capacity',
            'description' => 'Latest backup storage occupancy by area.',
            'charts' => [
                self::chart(
                    'reports-storage-capacity',
                    'Storage usage',
                    'bar',
                    'percent',
                    array_map(static function (array $row): string {
                        return $row['name'];
                    }, $rows),
                    [
                        'Used %' => array_map(static function (array $row): float {
                            return (float)$row['percent'];
                        }, $rows),
                    ]
                ),
            ],
            'tables' => [[
                'title' => 'Backup storage',
                'columns' => ['Storage', 'Endpoint', 'Path', 'Used', 'Available', 'Usage', 'Last sample'],
                'rows' => array_map(static function (array $row): array {
                    return [
                        $row['name'],
                        $row['endpoint'],
                        $row['path'],
                        self::formatBytes($row['used']),
                        self::formatBytes($row['available']),
                        self::formatPercent($row['percent']),
                        $row['date'],
                    ];
                }, $rows),
            ]],
        ];
    }

    private static function digestIndexSection(array $rows): array
    {
        return [
            'slug' => 'unused_indexes',
            'title' => 'Digest index signals',
            'description' => 'Digest counters that indicate missing or inefficient index usage.',
            'charts' => [
                self::chart(
                    'reports-unused-indexes',
                    'No-index counters',
                    'bar',
                    'count',
                    array_map(static function (array $row): string {
                        return $row['server'] . ' / ' . $row['schema'];
                    }, $rows),
                    [
                        'No index used' => array_map(static function (array $row): int {
                            return (int)$row['no_index_used'];
                        }, $rows),
                        'No good index used' => array_map(static function (array $row): int {
                            return (int)$row['no_good_index_used'];
                        }, $rows),
                    ]
                ),
            ],
            'tables' => [[
                'title' => 'Digest index signals',
                'columns' => ['Server', 'Client', 'Environment', 'Schema', 'Exec', 'No index', 'No good index', 'Last seen', 'Sample'],
                'rows' => array_map(static function (array $row): array {
                    return [
                        $row['server'],
                        $row['client'],
                        $row['environment'],
                        $row['schema'],
                        number_format((int)$row['count_star']),
                        number_format((int)$row['no_index_used']),
                        number_format((int)$row['no_good_index_used']),
                        $row['last_seen'],
                        $row['sample'],
                    ];
                }, $rows),
            ]],
        ];
    }

    private static function roadmapSection(): array
    {
        return [
            'slug' => 'roadmap',
            'title' => 'Report roadmap',
            'description' => 'Useful BI ideas that should stay local/read-only but need dedicated collectors or follow-up tickets.',
            'charts' => [],
            'tables' => [],
            'notes' => [
                'Monthly unused indexes can be hardened from digest counters plus table/index inventory.',
                'Weekly digest emails belong with the Digest scheduler flow, while Reports should render the latest capture.',
                'Cluster configuration diffs should reuse CompareConfig snapshots instead of opening live server connections.',
                'All Reports data must remain local PmaControl data; no Mysql::getDbLink fan-out in the HTTP request path.',
            ],
        ];
    }

    private static function summaryCards(
        array $servers,
        array $serverLoadRows,
        array $lagRows,
        array $databaseRows,
        array $storageRows,
        array $digestRows
    ): array {
        $totalDatabaseBytes = array_sum(array_map(static function (array $row): float {
            return (float)$row['total_bytes'];
        }, $databaseRows));

        return [
            [
                'label' => 'Monitored servers',
                'value' => (string)count($servers),
                'note' => 'mysql_server.is_monitored = 1',
            ],
            [
                'label' => 'Max CPU',
                'value' => self::formatPercent($serverLoadRows[0]['cpu_percent'] ?? null),
                'note' => $serverLoadRows[0]['server']['display_name'] ?? 'No aggregate metric',
            ],
            [
                'label' => 'Max lag',
                'value' => self::formatSeconds($lagRows[0]['lag_seconds'] ?? null),
                'note' => $lagRows[0]['server']['display_name'] ?? 'No replication metric',
            ],
            [
                'label' => 'Top DB total',
                'value' => self::formatBytes($totalDatabaseBytes),
                'note' => 'Shown reports subset total',
            ],
            [
                'label' => 'Storage max',
                'value' => self::formatPercent($storageRows[0]['percent'] ?? null),
                'note' => $storageRows[0]['name'] ?? 'No storage sample',
            ],
            [
                'label' => 'No-index signals',
                'value' => number_format((int)array_sum(array_map(static function (array $row): int {
                    return (int)$row['score'];
                }, $digestRows))),
                'note' => 'Latest digest sample',
            ],
        ];
    }

    private static function chart(string $id, string $title, string $type, string $unit, array $labels, array $series): array
    {
        $colors = ChartPayload::colorPalette(count($series));
        $datasets = [];
        $index = 0;
        foreach ($series as $label => $data) {
            $datasets[] = ChartPayload::dataset((string)$label, array_values($data), $colors[$index] ?? $colors[0]);
            $index++;
        }

        return [
            'id' => $id,
            'title' => $title,
            'type' => $type,
            'unit' => $unit,
            'labels' => array_values($labels),
            'datasets' => $datasets,
        ];
    }

    private static function metricValue(array $metrics, string $name): ?float
    {
        return isset($metrics[$name]['value']) ? (float)$metrics[$name]['value'] : null;
    }

    private static function toFloat($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float)$value;
    }

    private static function label($value): string
    {
        $label = trim((string)$value);

        return $label === '' ? '-' : $label;
    }

    private static function formatBytes($bytes): string
    {
        if (!is_numeric($bytes)) {
            return 'n/a';
        }

        $parts = Format::byteParts($bytes, 'iec');
        $decimals = $parts !== null && $parts['factor'] === 0 ? 0 : 2;

        return Format::bytes($bytes, $decimals, 'iec');
    }

    private static function formatPercent($value): string
    {
        if (!is_numeric($value)) {
            return 'n/a';
        }

        return number_format((float)$value, 2) . '%';
    }

    private static function formatSeconds($value): string
    {
        if (!is_numeric($value)) {
            return 'n/a';
        }

        $seconds = (int)round((float)$value);
        if ($seconds < 60) {
            return $seconds . 's';
        }

        if ($seconds < 3600) {
            return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
        }

        return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
    }

    private static function shortenSql(string $sql): string
    {
        $sql = preg_replace('/\s+/', ' ', trim($sql)) ?? trim($sql);
        if ($sql === '') {
            return '-';
        }

        if (mb_strlen($sql) <= 160) {
            return $sql;
        }

        return mb_substr($sql, 0, 157) . '...';
    }

    private static function sanitizeMessage(string $message): string
    {
        $message = preg_replace('/((?:password|passwd|pwd|secret|token)\s*[=:]\s*)[^\s;&]+/i', '$1***', $message) ?? $message;
        $message = preg_replace('/([a-z][a-z0-9+.-]*:\/\/[^:\s@]+):[^@\s]+@/i', '$1:***@', $message) ?? $message;

        return trim(mb_substr($message, 0, 512));
    }
}
