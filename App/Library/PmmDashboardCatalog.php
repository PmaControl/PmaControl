<?php

namespace App\Library;

use App\Controller\Dot3;
use App\Library\EngineMemoryBreakdown;
use Glial\Sgbd\Sgbd;

class PmmDashboardCatalog
{
    private const RANGE_PRESETS = [
        '1h' => 3600,
        '6h' => 21600,
        '24h' => 86400,
    ];

    public static function build(string $dashboard, int $idMysqlServer, array $options = []): array
    {
        $dashboards = self::getDashboards();

        if (!isset($dashboards[$dashboard])) {
            throw new \InvalidArgumentException('Unknown PMM dashboard: ' . $dashboard);
        }

        $server = self::getServer($idMysqlServer);
        $range = self::normalizeRange($options);

        if ($dashboard === 'overview') {
            return self::buildOverview($idMysqlServer, $server, $dashboards, $range);
        }

        return match ($dashboard) {
            'system' => self::buildSystem($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'innodb' => self::buildInnoDB($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'binlog' => self::buildBinlog($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'galera' => self::buildGalera($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'performance_schema' => self::buildPerformanceSchema($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'aria' => self::buildAria($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'myisam' => self::buildMyISAM($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'rocksdb' => self::buildRocksDb($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            'proxysql' => self::buildProxySql($idMysqlServer, $server, $dashboards[$dashboard], $range, $dashboards),
            default => throw new \InvalidArgumentException('Unhandled PMM dashboard: ' . $dashboard),
        };
    }

    public static function getDashboards(): array
    {
        return [
            'overview' => [
                'slug' => 'overview',
                'title' => 'Overview',
                'description' => 'PMM dashboard inventory rebuilt inside PmaControl for MySQL/MariaDB storage engines, system telemetry and ProxySQL.',
                'pmm_dashboard' => 'Dashboard inventory',
                'pmm_source' => 'percona/grafana-dashboards',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards',
            ],
            'system' => [
                'slug' => 'system',
                'title' => 'System',
                'description' => 'Equivalent of PMM OS dashboards: Node Summary, CPU, Memory, Disk, Network and Processes.',
                'pmm_dashboard' => 'Node Summary / CPU Utilization Details / Memory Details / Disk Details / Network Details / Processes Details',
                'pmm_source' => 'dashboards/OS/*.json',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards/tree/main/dashboards/OS',
            ],
            'innodb' => [
                'slug' => 'innodb',
                'title' => 'InnoDB',
                'description' => 'Equivalent of PMM MySQL InnoDB Details with split sections for activity, buffer pool, logging, IO, locking and internal structures.',
                'pmm_dashboard' => 'MySQL InnoDB Details',
                'pmm_source' => 'dashboards/MySQL/MySQL_InnoDB_Details.json',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards/blob/main/dashboards/MySQL/MySQL_InnoDB_Details.json',
            ],
            'binlog' => [
                'slug' => 'binlog',
                'title' => 'Binlog',
                'description' => 'Binlog and GTID view rebuilt in PmaControl from already collected binary log variables and mysql_binlog inventory.',
                'pmm_dashboard' => 'MySQL Replication / binary log related panels',
                'pmm_source' => 'dashboards/MySQL/*replication* + PmaControl mysql_binlog collector',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards',
            ],
            'galera' => [
                'slug' => 'galera',
                'title' => 'Galera',
                'description' => 'Equivalent of PMM PXC Galera Cluster Summary using wsrep, SST and datadir metrics already collected by PmaControl.',
                'pmm_dashboard' => 'PXC Galera Cluster Summary',
                'pmm_source' => 'pmmdemo Percona / dashboards PXC cluster summary',
                'pmm_source_url' => 'https://pmmdemo.percona.com/pmm-ui/graph/d/pxc-cluster-summary/pxc-galera-cluster-summary',
            ],
            'performance_schema' => [
                'slug' => 'performance_schema',
                'title' => 'Performance Schema',
                'description' => 'Equivalent of PMM MySQL Performance Schema Details using P_S enablement and sizing variables already collected by PmaControl.',
                'pmm_dashboard' => 'MySQL Performance Schema Details',
                'pmm_source' => 'pmmdemo Percona / mysql-performance-schema-details',
                'pmm_source_url' => 'https://pmmdemo.percona.com/pmm-ui/graph/d/mysql-performance-schema/mysql-performance-schema-details',
            ],
            'aria' => [
                'slug' => 'aria',
                'title' => 'Aria',
                'description' => 'Equivalent of the Aria part of PMM MySQL MyISAM Aria Details for Aria pagecache and Aria configuration.',
                'pmm_dashboard' => 'MySQL MyISAM Aria Details',
                'pmm_source' => 'dashboards/MySQL/MySQL_MyISAM_Aria_Details.json',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards/blob/main/dashboards/MySQL/MySQL_MyISAM_Aria_Details.json',
            ],
            'myisam' => [
                'slug' => 'myisam',
                'title' => 'MyISAM',
                'description' => 'Equivalent of the MyISAM part of PMM MySQL MyISAM Aria Details for MyISAM key cache and MyISAM configuration.',
                'pmm_dashboard' => 'MySQL MyISAM Aria Details',
                'pmm_source' => 'dashboards/MySQL/MySQL_MyISAM_Aria_Details.json',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards/blob/main/dashboards/MySQL/MySQL_MyISAM_Aria_Details.json',
            ],
            'rocksdb' => [
                'slug' => 'rocksdb',
                'title' => 'RocksDB',
                'description' => 'Equivalent of PMM MySQL MyRocks Details using MariaDB/MyRocks counters already collected by PmaControl when available.',
                'pmm_dashboard' => 'MySQL MyRocks Details',
                'pmm_source' => 'dashboards/MySQL/MySQL_MyRocks_Details.json',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards/blob/main/dashboards/MySQL/MySQL_MyRocks_Details.json',
            ],
            'proxysql' => [
                'slug' => 'proxysql',
                'title' => 'ProxySQL',
                'description' => 'Equivalent of PMM ProxySQL Instance Summary. PmaControl currently covers topology and runtime JSON, but not the full exporter time series set.',
                'pmm_dashboard' => 'ProxySQL Instance Summary',
                'pmm_source' => 'dashboards/MySQL/ProxySQL_Instance_Summary.json',
                'pmm_source_url' => 'https://github.com/percona/grafana-dashboards/blob/main/dashboards/MySQL/ProxySQL_Instance_Summary.json',
            ],
        ];
    }

    public static function normalizeRange(array $options = []): array
    {
        $timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        $now = new \DateTimeImmutable('now', $timezone);

        $preset = (string)($options['range'] ?? '24h');
        $rangeMode = (string)($options['range_mode'] ?? 'preset');

        if (!isset(self::RANGE_PRESETS[$preset])) {
            $preset = '24h';
        }

        $mode = 'preset';
        $end = $now;
        $start = $end->sub(new \DateInterval('PT' . self::RANGE_PRESETS[$preset] . 'S'));

        $customStart = self::parseDateTimeInput($options['start'] ?? null, $timezone);
        $customEnd = self::parseDateTimeInput($options['end'] ?? null, $timezone);

        if ($rangeMode === 'custom' && $customStart !== null && $customEnd !== null && $customEnd > $customStart) {
            $diff = $customEnd->getTimestamp() - $customStart->getTimestamp();
            if ($diff > 0 && $diff <= self::RANGE_PRESETS['24h']) {
                $mode = 'custom';
                $start = $customStart;
                $end = $customEnd;
            }
        }

        return [
            'preset' => $preset,
            'range_mode' => $mode,
            'mode' => $mode,
            'start' => $start,
            'end' => $end,
            'start_value' => $start->format('Y-m-d\TH:i'),
            'end_value' => $end->format('Y-m-d\TH:i'),
            'bucket_seconds' => self::getBucketSeconds($preset, $mode),
        ];
    }

    private static function buildOverview(int $idMysqlServer, array $server, array $dashboards, array $range): array
    {
        $current = self::getCurrentValues($idMysqlServer, ['version', 'version_comment', 'mysql_available', 'cpu_usage', 'memory_total', 'memory_used']);
        $cards = [
            self::buildStatusCard('Selected server', $server['display_name'] ?? ('Server #' . $idMysqlServer), 'Target server for all PMM-equivalent screens.'),
            self::buildStatusCard('Product', self::formatProductBanner($current), 'Current product banner from PmaControl current-value extraction.'),
            self::buildStatusCard('MySQL availability', self::formatAvailability($current['mysql_available'] ?? null), 'Current mysql_available status from PmaControl.'),
            self::buildStatusCard('CPU', self::formatPercent($current['cpu_usage'] ?? null), 'Current CPU usage from ssh_stats::cpu_usage.'),
            self::buildStatusCard('Memory used', self::formatBytes($current['memory_used'] ?? null), 'Current memory_used from ssh_stats::memory_used.'),
        ];

        $rows = [];
        foreach ($dashboards as $slug => $dashboard) {
            if ($slug === 'overview') {
                continue;
            }

            $rows[] = [
                'screen' => $dashboard['title'],
                'route' => 'Pmm/' . $slug . '/' . $idMysqlServer,
                'route_url' => '/pmacontrol/en/Pmm/' . $slug . '/' . $idMysqlServer,
                'pmm_dashboard' => $dashboard['pmm_dashboard'],
                'source' => $dashboard['pmm_source'],
                'comment' => $dashboard['description'],
            ];
        }

        $notes = [
            'PMM official dashboards were inspected from the public Grafana dashboard JSON repository.',
            'MySQL Router has no official PMM dashboard in the inspected source tree. Existing PmaControl coverage remains under MysqlRouter/* and is documented as a gap in the PMM rebuild docs.',
            'Charts here use PmaControl historical storage and may expose approximations when PMM relies on exporter-side rates that PmaControl stores only as cumulative counters.',
        ];

        return [
            'dashboard' => $dashboards['overview'],
            'server' => $server,
            'range' => $range,
            'menu' => $dashboards,
            'summary_cards' => $cards,
            'sections' => [
                [
                    'title' => 'Available screens',
                    'description' => 'Implemented PMM-style screens in PmaControl.',
                    'cards' => [],
                    'charts' => [],
                    'tables' => [
                        [
                            'title' => 'Screens',
                            'columns' => ['Screen', 'Route', 'PMM dashboard', 'PMM source', 'Coverage comment'],
                            'rows' => array_map(static function (array $row): array {
                                return [
                                    $row['screen'],
                                    '<a href="' . htmlspecialchars($row['route_url'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['route'], ENT_QUOTES, 'UTF-8') . '</a>',
                                    $row['pmm_dashboard'],
                                    $row['source'],
                                    $row['comment'],
                                ];
                            }, $rows),
                        ],
                    ],
                    'notes' => $notes,
                ],
            ],
        ];
    }

    private static function buildSystem(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'version', 'version_comment', 'mysql_available', 'cpu_usage', 'memory_total', 'memory_used',
            'swap_total', 'swap_used', 'load_average_1_min', 'load_average_5_min', 'load_average_15_min',
            'uptime', 'user_connected', 'disks', 'cpu_detail', 'memory_detail_kb',
        ];
        $historyKeys = [
            'cpu_usage', 'memory_total', 'memory_used', 'swap_total', 'swap_used',
            'load_average_1_min', 'load_average_5_min', 'load_average_15_min', 'user_connected',
        ];

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);
        $diskRows = self::buildDiskRows($current['disks'] ?? null);
        $engineMemory = EngineMemoryBreakdown::build($idMysqlServer, [
            'range' => $range['preset'] ?? '24h',
            'range_mode' => $range['range_mode'] ?? 'preset',
            'start' => $range['start_value'] ?? null,
            'end' => $range['end_value'] ?? null,
        ]);
        $processMemoryChart = $engineMemory['process_memory_chart'] ?? [];
        $processMemoryChart['renderer'] = 'engineMemoryProcess';
        $processMemoryChart['title'] = 'Top process memory';

        $summaryCards = [
            self::buildStatusCard('CPU usage', self::formatPercent($current['cpu_usage'] ?? null), 'Equivalent to PMM CPU Usage current value.'),
            self::buildStatusCard('Load average', self::formatLoadCard($current['load_average_1_min'] ?? null, $current['load_average_5_min'] ?? null, $current['load_average_15_min'] ?? null), 'Equivalent to PMM Load Average stat cards.'),
            self::buildStatusCard('RAM used', self::formatBytes($current['memory_used'] ?? null) . ' / ' . self::formatBytes($current['memory_total'] ?? null), 'Equivalent to PMM RAM / Memory Available cards.'),
            self::buildStatusCard('Swap used', self::formatBytes($current['swap_used'] ?? null) . ' / ' . self::formatBytes($current['swap_total'] ?? null), 'Equivalent to PMM Swap Space.'),
            self::buildStatusCard('Connected users', self::formatScalar($current['user_connected'] ?? null), 'Equivalent to PMM processes/services user-connected summary is only partially covered.'),
        ];

        $sections = [
            [
                'title' => 'CPU & load',
                'description' => 'Equivalent to PMM Node Summary CPU section and CPU Utilization Details.',
                'cards' => [
                    self::buildMetricCard('Current CPU usage', $current['cpu_usage'] ?? null, 'ssh_stats::cpu_usage', 'CPU Usage stat / CPU Usage timeseries'),
                    self::buildMetricCard('1 min load', $current['load_average_1_min'] ?? null, 'ssh_stats::load_average_1_min', 'Load Average'),
                    self::buildMetricCard('5 min load', $current['load_average_5_min'] ?? null, 'ssh_stats::load_average_5_min', 'Load Average'),
                    self::buildMetricCard('15 min load', $current['load_average_15_min'] ?? null, 'ssh_stats::load_average_15_min', 'Load Average'),
                ],
                'charts' => [
                    self::buildRawChart('system_cpu_usage', 'CPU usage', 'line', 'percent', ['cpu_usage' => 'CPU %'], $historyRows, $range, 'CPU Usage', 'node exporter / PMM OS dashboards'),
                    self::buildRawChart('system_load_average', 'Load average', 'line', 'count', [
                        'load_average_1_min' => 'Load 1m',
                        'load_average_5_min' => 'Load 5m',
                        'load_average_15_min' => 'Load 15m',
                    ], $historyRows, $range, 'Load Average', 'node exporter / PMM OS dashboards'),
                ],
                'tables' => [
                    [
                        'title' => 'CPU detail (current snapshot)',
                        'columns' => ['Metric', 'Value'],
                        'rows' => self::rowsFromAssoc(self::parseJsonObject($current['cpu_detail'] ?? null)),
                    ],
                ],
                'notes' => [
                    'PMM also exposes per-core utilization, saturation, interrupts and context switches. Those detailed series are not yet collected in PmaControl.',
                ],
            ],
            [
                'title' => 'Memory',
                'description' => 'Equivalent to PMM Node Summary Memory and Memory Details.',
                'cards' => [
                    self::buildMetricCard('Physical memory', $current['memory_total'] ?? null, 'ssh_stats::memory_total', 'RAM'),
                    self::buildMetricCard('Memory used', $current['memory_used'] ?? null, 'ssh_stats::memory_used', 'Memory Utilization'),
                    self::buildMetricCard('Swap total', $current['swap_total'] ?? null, 'ssh_stats::swap_total', 'Swap Space'),
                    self::buildMetricCard('Swap used', $current['swap_used'] ?? null, 'ssh_stats::swap_used', 'Swap Space'),
                ],
                'charts' => [
                    self::buildRawChart('system_memory', 'Memory utilization', 'line', 'bytes', [
                        'memory_used' => 'Memory used',
                        'memory_total' => 'Physical memory',
                    ], $historyRows, $range, 'Memory Utilization', 'node exporter / PMM OS dashboards'),
                    self::buildRawChart('system_swap', 'Swap utilization', 'line', 'bytes', [
                        'swap_used' => 'Swap used',
                        'swap_total' => 'Swap total',
                    ], $historyRows, $range, 'Swap Space', 'node exporter / PMM OS dashboards'),
                    $processMemoryChart,
                ],
                'tables' => [],
                'notes' => [
                    'PMM Memory Details also includes page/zone breakdown and virtual memory internals. PmaControl currently keeps only high-level RAM/swap and per-process RSS snapshots.',
                ],
            ],
            [
                'title' => 'Disk & storage',
                'description' => 'Equivalent to PMM Node Summary Disk and Disk Details.',
                'cards' => [
                    self::buildMetricCard('Uptime', $current['uptime'] ?? null, 'ssh_stats::uptime', 'System Uptime'),
                    self::buildMetricCard('Current mounts', count($diskRows), 'ssh_stats::disks', 'Disk Space / Mountpoint Usage'),
                ],
                'charts' => [],
                'tables' => [
                    [
                        'title' => 'Current mountpoint usage',
                        'columns' => ['Filesystem', 'Mounted on', 'Used', 'Available', 'Use %'],
                        'rows' => $diskRows,
                    ],
                ],
                'notes' => [
                    'PMM provides per-device latency, IO bandwidth, queue depth and utilization time series.',
                    'PmaControl currently stores the current df snapshot but not historical per-device disk IO series.',
                ],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildInnoDB(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'version', 'version_comment', 'mysql_available',
            'innodb_buffer_pool_size', 'innodb_buffer_pool_bytes_data', 'innodb_buffer_pool_bytes_dirty',
            'innodb_buffer_pool_pages_total', 'innodb_buffer_pool_pages_free', 'innodb_buffer_pool_pages_dirty',
            'innodb_buffer_pool_read_requests', 'innodb_buffer_pool_reads', 'innodb_log_file_size',
            'innodb_log_buffer_size', 'innodb_checkpoint_age', 'innodb_checkpoint_max_age',
            'innodb_history_list_length', 'innodb_row_lock_waits', 'innodb_row_lock_time_avg',
            'innodb_ibuf_size', 'innodb_ibuf_free_list', 'innodb_adaptive_hash_hash_searches',
            'innodb_adaptive_hash_non_hash_searches', 'innodb_undo_tablespaces_total', 'innodb_onlineddl_pct_progress',
        ];
        $historyKeys = [
            'innodb_buffer_pool_pages_total', 'innodb_buffer_pool_pages_free', 'innodb_buffer_pool_pages_dirty',
            'innodb_buffer_pool_read_requests', 'innodb_buffer_pool_reads',
            'innodb_data_read', 'innodb_data_written', 'innodb_data_fsyncs',
            'innodb_log_write_requests', 'innodb_log_writes', 'innodb_os_log_fsyncs', 'innodb_os_log_written', 'innodb_log_waits',
            'innodb_checkpoint_age', 'innodb_checkpoint_max_age',
            'innodb_pages_created', 'innodb_pages_read', 'innodb_pages_written',
            'innodb_dblwr_pages_written', 'innodb_dblwr_writes',
            'innodb_rows_read', 'innodb_rows_inserted', 'innodb_rows_updated', 'innodb_rows_deleted',
            'innodb_history_list_length', 'innodb_row_lock_waits', 'innodb_row_lock_time', 'innodb_row_lock_time_avg',
            'innodb_ibuf_merges', 'innodb_ibuf_merged_inserts', 'innodb_ibuf_merged_deletes', 'innodb_ibuf_merged_delete_marks',
            'innodb_ibuf_size', 'innodb_ibuf_free_list',
            'innodb_adaptive_hash_hash_searches', 'innodb_adaptive_hash_non_hash_searches',
            'innodb_undo_truncations', 'innodb_undo_tablespaces_total', 'innodb_onlineddl_pct_progress', 'innodb_onlineddl_rowlog_pct_used',
        ];

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);
        $hitRatio = self::computeHitRatio($current['innodb_buffer_pool_reads'] ?? null, $current['innodb_buffer_pool_read_requests'] ?? null);

        $summaryCards = [
            self::buildStatusCard('Buffer pool', self::formatBytes($current['innodb_buffer_pool_size'] ?? null), 'Equivalent to PMM Buffer Pool Size.'),
            self::buildStatusCard('Buffer pool data', self::formatBytes($current['innodb_buffer_pool_bytes_data'] ?? null), 'Equivalent to PMM Buffer Pool Size of Data.'),
            self::buildStatusCard('Redo log file', self::formatBytes($current['innodb_log_file_size'] ?? null), 'Equivalent to PMM Total Redo Log Space.'),
            self::buildStatusCard('Checkpoint age', self::formatBytes($current['innodb_checkpoint_age'] ?? null), 'Equivalent to PMM Max Log Space Used / Checkpointing.'),
            self::buildStatusCard('Hit ratio', self::formatPercent($hitRatio), 'Equivalent to PMM buffer pool efficiency interpretation.'),
        ];

        $sections = [
            [
                'title' => 'Activity & throughput',
                'description' => 'Equivalent to PMM InnoDB Activity and Storage Summary.',
                'cards' => [
                    self::buildMetricCard('History list length', $current['innodb_history_list_length'] ?? null, 'status::innodb_history_list_length', 'Max Transaction History Length'),
                    self::buildMetricCard('Row lock waits', $current['innodb_row_lock_waits'] ?? null, 'status::innodb_row_lock_waits', 'Row Lock Blocking'),
                    self::buildMetricCard('Row lock avg time', $current['innodb_row_lock_time_avg'] ?? null, 'status::innodb_row_lock_time_avg', 'Row Lock Blocking'),
                ],
                'charts' => [
                    self::buildRateChart('innodb_data_bandwidth', 'InnoDB data bandwidth', 'bytes_per_second', [
                        'innodb_data_read' => 'Bytes read/s',
                        'innodb_data_written' => 'Bytes written/s',
                    ], $historyRows, $range, 'Data Bandwidth', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_row_ops', 'InnoDB row operations', 'ops_per_second', [
                        'innodb_rows_read' => 'Rows read/s',
                        'innodb_rows_inserted' => 'Rows inserted/s',
                        'innodb_rows_updated' => 'Rows updated/s',
                        'innodb_rows_deleted' => 'Rows deleted/s',
                    ], $historyRows, $range, 'InnoDB Activity', 'mysql exporter / SHOW GLOBAL STATUS'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'Buffer pool',
                'description' => 'Equivalent to PMM Buffer Pool and Replacement Management sections.',
                'cards' => [
                    self::buildMetricCard('Buffer pool size', $current['innodb_buffer_pool_size'] ?? null, 'variables::innodb_buffer_pool_size', 'Buffer Pool Size'),
                    self::buildMetricCard('Pages total', $current['innodb_buffer_pool_pages_total'] ?? null, 'status::innodb_buffer_pool_pages_total', 'Buffer Pool'),
                    self::buildMetricCard('Pages free', $current['innodb_buffer_pool_pages_free'] ?? null, 'status::innodb_buffer_pool_pages_free', 'Buffer Pool'),
                    self::buildMetricCard('Pages dirty', $current['innodb_buffer_pool_pages_dirty'] ?? null, 'status::innodb_buffer_pool_pages_dirty', 'Buffer Pool'),
                    self::buildMetricCard('Hit ratio', $hitRatio, 'status::innodb_buffer_pool_reads / innodb_buffer_pool_read_requests', 'Buffer Pool hit ratio'),
                ],
                'charts' => [
                    self::buildRawChart('innodb_buffer_pool_pages', 'Buffer pool pages', 'line', 'count', [
                        'innodb_buffer_pool_pages_total' => 'Pages total',
                        'innodb_buffer_pool_pages_free' => 'Pages free',
                        'innodb_buffer_pool_pages_dirty' => 'Pages dirty',
                    ], $historyRows, $range, 'Buffer Pool', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_buffer_pool_read_pressure', 'Buffer pool read pressure', 'ops_per_second', [
                        'innodb_buffer_pool_read_requests' => 'Read requests/s',
                        'innodb_buffer_pool_reads' => 'Disk reads/s',
                    ], $historyRows, $range, 'Buffer Pool', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildHitRatioChart('innodb_buffer_pool_hit_ratio', 'Buffer pool hit ratio', 'percent', 'innodb_buffer_pool_reads', 'innodb_buffer_pool_read_requests', $historyRows, $range, 'Buffer Pool hit ratio', 'calculated from cumulative InnoDB read counters'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'Logging & checkpointing',
                'description' => 'Equivalent to PMM Logging and Checkpointing sections.',
                'cards' => [
                    self::buildMetricCard('Redo log size', $current['innodb_log_file_size'] ?? null, 'variables::innodb_log_file_size', 'Total Redo Log Space'),
                    self::buildMetricCard('Log buffer size', $current['innodb_log_buffer_size'] ?? null, 'variables::innodb_log_buffer_size', 'Log Buffer Usage'),
                    self::buildMetricCard('Checkpoint age', $current['innodb_checkpoint_age'] ?? null, 'status::innodb_checkpoint_age', 'Checkpointing'),
                    self::buildMetricCard('Checkpoint max age', $current['innodb_checkpoint_max_age'] ?? null, 'status::innodb_checkpoint_max_age', 'Checkpointing'),
                ],
                'charts' => [
                    self::buildRateChart('innodb_log_ops', 'Redo log operations', 'ops_per_second', [
                        'innodb_log_write_requests' => 'Write requests/s',
                        'innodb_log_writes' => 'Writes/s',
                        'innodb_os_log_fsyncs' => 'Fsyncs/s',
                        'innodb_log_waits' => 'Log waits/s',
                    ], $historyRows, $range, 'Logging', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_os_log_written', 'Redo bytes written', 'bytes_per_second', [
                        'innodb_os_log_written' => 'Redo bytes/s',
                    ], $historyRows, $range, 'Logging', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRawChart('innodb_checkpoint', 'Checkpoint age', 'line', 'bytes', [
                        'innodb_checkpoint_age' => 'Checkpoint age',
                        'innodb_checkpoint_max_age' => 'Checkpoint max age',
                    ], $historyRows, $range, 'Checkpointing and Flushing', 'mysql exporter / SHOW GLOBAL STATUS'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'Flushing & page IO',
                'description' => 'Equivalent to PMM Disk IO, Page Operations and doublewrite related panels.',
                'cards' => [],
                'charts' => [
                    self::buildRateChart('innodb_pages_io', 'InnoDB page operations', 'ops_per_second', [
                        'innodb_pages_created' => 'Pages created/s',
                        'innodb_pages_read' => 'Pages read/s',
                        'innodb_pages_written' => 'Pages written/s',
                    ], $historyRows, $range, 'Page Operations', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_dblwr', 'Doublewrite activity', 'ops_per_second', [
                        'innodb_dblwr_pages_written' => 'Doublewrite pages/s',
                        'innodb_dblwr_writes' => 'Doublewrite writes/s',
                        'innodb_data_fsyncs' => 'Data fsyncs/s',
                    ], $historyRows, $range, 'Disk IO', 'mysql exporter / SHOW GLOBAL STATUS'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'Locking, purge & undo',
                'description' => 'Equivalent to PMM Locking and Undo Space / Purging sections.',
                'cards' => [
                    self::buildMetricCard('Undo tablespaces', $current['innodb_undo_tablespaces_total'] ?? null, 'status::innodb_undo_tablespaces_total', 'Undo Space and Purging'),
                    self::buildMetricCard('Online DDL progress', $current['innodb_onlineddl_pct_progress'] ?? null, 'status::innodb_onlineddl_pct_progress', 'Online Operations'),
                ],
                'charts' => [
                    self::buildRawChart('innodb_history_list_length', 'History list length', 'line', 'count', [
                        'innodb_history_list_length' => 'History list length',
                    ], $historyRows, $range, 'Undo Space and Purging', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_row_lock_waits', 'Row lock waits', 'ops_per_second', [
                        'innodb_row_lock_waits' => 'Waits/s',
                    ], $historyRows, $range, 'Locking', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_undo_truncations', 'Undo truncations', 'ops_per_second', [
                        'innodb_undo_truncations' => 'Undo truncations/s',
                    ], $historyRows, $range, 'Undo Space and Purging', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRawChart('innodb_online_ddl', 'Online DDL progress', 'line', 'percent', [
                        'innodb_onlineddl_pct_progress' => 'DDL progress %',
                        'innodb_onlineddl_rowlog_pct_used' => 'Row log used %',
                    ], $historyRows, $range, 'Online Operations', 'mysql exporter / SHOW GLOBAL STATUS'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'Change buffer & adaptive hash',
                'description' => 'Equivalent to PMM Change Buffer and Adaptive Hash Index sections.',
                'cards' => [
                    self::buildMetricCard('Change buffer size', $current['innodb_ibuf_size'] ?? null, 'status::innodb_ibuf_size', 'Change Buffer'),
                    self::buildMetricCard('Change buffer free list', $current['innodb_ibuf_free_list'] ?? null, 'status::innodb_ibuf_free_list', 'Change Buffer'),
                ],
                'charts' => [
                    self::buildRateChart('innodb_ibuf_activity', 'Change buffer activity', 'ops_per_second', [
                        'innodb_ibuf_merges' => 'Merges/s',
                        'innodb_ibuf_merged_inserts' => 'Merged inserts/s',
                        'innodb_ibuf_merged_deletes' => 'Merged deletes/s',
                        'innodb_ibuf_merged_delete_marks' => 'Merged delete-marks/s',
                    ], $historyRows, $range, 'Change Buffer', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRawChart('innodb_ibuf_size', 'Change buffer size', 'line', 'count', [
                        'innodb_ibuf_size' => 'Change buffer size',
                        'innodb_ibuf_free_list' => 'Change buffer free list',
                    ], $historyRows, $range, 'Change Buffer', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('innodb_ahi_searches', 'Adaptive hash index searches', 'ops_per_second', [
                        'innodb_adaptive_hash_hash_searches' => 'Hash searches/s',
                        'innodb_adaptive_hash_non_hash_searches' => 'Non-hash searches/s',
                    ], $historyRows, $range, 'Adaptive Hash Index', 'mysql exporter / SHOW GLOBAL STATUS'),
                ],
                'tables' => [],
                'notes' => [],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildBinlog(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'log_bin', 'sync_binlog', 'binlog_format', 'binlog_row_image', 'binlog_checksum',
            'binlog_cache_size', 'binlog_stmt_cache_size', 'max_binlog_size', 'binlog_space_limit',
            'binlog_nb_files', 'binlog_total_size', 'binlog_expire_logs_seconds', 'log_bin_basename',
            'mysql_binlog::binlog_file_last', 'gtid_current_pos', 'gtid_binlog_pos',
        ];
        $historyKeys = [
            'binlog_nb_files', 'binlog_total_size',
        ];

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);

        $summaryCards = [
            self::buildStatusCard('Binary logging', self::formatScalar($current['log_bin'] ?? null), 'Current log_bin status.'),
            self::buildStatusCard('Total binlog size', self::formatBytes($current['binlog_total_size'] ?? null), 'Collected from mysql_binlog inventory.'),
            self::buildStatusCard('Binlog files', self::formatScalar($current['binlog_nb_files'] ?? null), 'Collected from mysql_binlog inventory.'),
            self::buildStatusCard('Format', self::formatScalar($current['binlog_format'] ?? null), 'Current binlog_format value.'),
            self::buildStatusCard('Sync binlog', self::formatScalar($current['sync_binlog'] ?? null), 'Current sync_binlog value.'),
        ];

        $sections = [
            [
                'title' => 'Capacity & retention',
                'description' => 'Binary log file count, total size and retention-related configuration.',
                'cards' => [
                    self::buildMetricCard('Total binlog size', $current['binlog_total_size'] ?? null, 'mysql_binlog::binlog_total_size', 'Binary log inventory'),
                    self::buildMetricCard('Number of binlog files', $current['binlog_nb_files'] ?? null, 'mysql_binlog::binlog_nb_files', 'Binary log inventory'),
                    self::buildMetricCard('Max binlog size', $current['max_binlog_size'] ?? null, 'variables::max_binlog_size', 'Binary log size limit'),
                    self::buildMetricCard('Expire logs seconds', $current['binlog_expire_logs_seconds'] ?? null, 'variables::binlog_expire_logs_seconds', 'Binary log retention'),
                ],
                'charts' => [
                    self::buildRawChart('binlog_total_size', 'Total binlog size', 'line', 'bytes', [
                        'binlog_total_size' => 'Total size',
                    ], $historyRows, $range, 'Binary log size', 'PmaControl mysql_binlog collector'),
                    self::buildRawChart('binlog_nb_files', 'Number of binlog files', 'line', 'count', [
                        'binlog_nb_files' => 'Files',
                    ], $historyRows, $range, 'Binary log files', 'PmaControl mysql_binlog collector'),
                ],
                'tables' => [
                    [
                        'title' => 'Current binlog runtime snapshot',
                        'columns' => ['Metric', 'Value'],
                        'rows' => [
                            ['log_bin', self::formatScalar($current['log_bin'] ?? null)],
                            ['sync_binlog', self::formatScalar($current['sync_binlog'] ?? null)],
                            ['binlog_format', self::formatScalar($current['binlog_format'] ?? null)],
                            ['binlog_row_image', self::formatScalar($current['binlog_row_image'] ?? null)],
                            ['binlog_checksum', self::formatScalar($current['binlog_checksum'] ?? null)],
                            ['log_bin_basename', self::formatScalar($current['log_bin_basename'] ?? null)],
                            ['last_binlog_file', self::formatScalar($current['binlog_file_last'] ?? null)],
                            ['gtid_current_pos', self::formatScalar($current['gtid_current_pos'] ?? null)],
                            ['gtid_binlog_pos', self::formatScalar($current['gtid_binlog_pos'] ?? null)],
                        ],
                    ],
                ],
                'notes' => [
                    'This screen uses already collected binary log inventory from the mysql_binlog collector and current variable snapshots from Extraction2.',
                ],
            ],
            [
                'title' => 'Caches & buffers',
                'description' => 'Binary log cache sizing currently available in PmaControl.',
                'cards' => [
                    self::buildMetricCard('Binlog cache size', $current['binlog_cache_size'] ?? null, 'variables::binlog_cache_size', 'Binlog cache size'),
                    self::buildMetricCard('Binlog statement cache size', $current['binlog_stmt_cache_size'] ?? null, 'variables::binlog_stmt_cache_size', 'Binlog stmt cache size'),
                    self::buildMetricCard('Binlog space limit', $current['binlog_space_limit'] ?? null, 'variables::binlog_space_limit', 'Binlog space limit'),
                ],
                'charts' => [],
                'tables' => [],
                'notes' => [
                    'PMM replication dashboards also expose binlog commit/cache usage counters. Those counters are not yet wired here unless they are collected separately.',
                ],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildAria(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'aria_pagecache_buffer_size', 'aria_log_file_size', 'aria_block_size', 'aria_used_for_temp_tables',
            'aria_encrypt_tables', 'aria_recover', 'aria_page_checksum', 'aria_pagecache_blocks_used',
            'aria_pagecache_blocks_unused', 'aria_pagecache_blocks_not_flushed', 'aria_pagecache_reads',
            'aria_pagecache_read_requests', 'key_buffer_size', 'key_blocks_used', 'key_blocks_unused',
            'key_read_requests', 'key_reads', 'key_write_requests', 'key_writes', 'myisam_sort_buffer_size',
            'myisam_recover_options', 'delay_key_write',
        ];
        $historyKeys = [
            'aria_pagecache_blocks_used', 'aria_pagecache_blocks_unused', 'aria_pagecache_blocks_not_flushed',
            'aria_pagecache_reads', 'aria_pagecache_read_requests',
            'key_blocks_used', 'key_blocks_unused', 'key_reads', 'key_read_requests', 'key_write_requests', 'key_writes',
        ];

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);
        $ariaHitRatio = self::computeHitRatio($current['aria_pagecache_reads'] ?? null, $current['aria_pagecache_read_requests'] ?? null);

        $summaryCards = [
            self::buildStatusCard('Aria pagecache', self::formatBytes($current['aria_pagecache_buffer_size'] ?? null), 'Equivalent to PMM Aria Pagecache buffer.'),
            self::buildStatusCard('Aria hit ratio', self::formatPercent($ariaHitRatio), 'Equivalent to PMM Aria cache efficiency.'),
            self::buildStatusCard('Aria log file', self::formatBytes($current['aria_log_file_size'] ?? null), 'Equivalent to PMM Aria transaction log sizing.'),
        ];

        $sections = [
            [
                'title' => 'Aria pagecache',
                'description' => 'Equivalent to PMM Aria metrics and pagecache sections.',
                'cards' => [
                    self::buildMetricCard('Pagecache buffer size', $current['aria_pagecache_buffer_size'] ?? null, 'variables::aria_pagecache_buffer_size', 'Aria Pagecache'),
                    self::buildMetricCard('Aria log file size', $current['aria_log_file_size'] ?? null, 'variables::aria_log_file_size', 'Aria transaction log'),
                    self::buildMetricCard('Aria hit ratio', $ariaHitRatio, 'status::aria_pagecache_reads / aria_pagecache_read_requests', 'Aria cache hit ratio'),
                ],
                'charts' => [
                    self::buildRawChart('aria_pagecache_blocks', 'Aria pagecache blocks', 'line', 'count', [
                        'aria_pagecache_blocks_used' => 'Blocks used',
                        'aria_pagecache_blocks_unused' => 'Blocks unused',
                        'aria_pagecache_blocks_not_flushed' => 'Blocks not flushed',
                    ], $historyRows, $range, 'Aria Pagecache Blocks', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('aria_pagecache_ops', 'Aria pagecache read pressure', 'ops_per_second', [
                        'aria_pagecache_read_requests' => 'Read requests/s',
                        'aria_pagecache_reads' => 'Reads/s',
                    ], $historyRows, $range, 'Aria Pagecache Reads/Writes', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildHitRatioChart('aria_hit_ratio', 'Aria hit ratio', 'percent', 'aria_pagecache_reads', 'aria_pagecache_read_requests', $historyRows, $range, 'Aria cache hit ratio', 'calculated from Aria cumulative pagecache counters'),
                ],
                'tables' => [
                    [
                        'title' => 'Aria configuration snapshot',
                        'columns' => ['Metric', 'Value'],
                        'rows' => [
                            ['aria_block_size', self::formatScalar($current['aria_block_size'] ?? null)],
                            ['aria_used_for_temp_tables', self::formatScalar($current['aria_used_for_temp_tables'] ?? null)],
                            ['aria_encrypt_tables', self::formatScalar($current['aria_encrypt_tables'] ?? null)],
                            ['aria_recover', self::formatScalar($current['aria_recover'] ?? null)],
                            ['aria_page_checksum', self::formatScalar($current['aria_page_checksum'] ?? null)],
                        ],
                    ],
                ],
                'notes' => [],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildGalera(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'version', 'version_comment', 'mysql_available',
            'wsrep_on', 'wsrep_cluster_name', 'wsrep_cluster_address', 'wsrep_incoming_addresses',
            'wsrep_cluster_status', 'wsrep_cluster_size', 'wsrep_cluster_state_uuid', 'wsrep_gcomm_uuid',
            'wsrep_local_state', 'wsrep_local_state_comment', 'wsrep_local_state_uuid', 'wsrep_ready', 'wsrep_connected',
            'wsrep_desync', 'wsrep_sst_method', 'wsrep_sst_donor', 'wsrep_sst_receive_address', 'wsrep_sst_donor_rejects_queries',
            'wsrep_provider_version', 'wsrep_provider', 'wsrep_node_name', 'wsrep_node_address', 'wsrep_node_incoming_address',
            'wsrep_slave_threads', 'wsrep_cert_deps_distance', 'wsrep_apply_window', 'wsrep_commit_window', 'wsrep_cert_interval',
            'wsrep_last_committed', 'wsrep_flow_control_paused', 'wsrep_flow_control_paused_ns',
            'wsrep_flow_control_recv', 'wsrep_flow_control_sent',
            'wsrep_local_recv_queue', 'wsrep_local_send_queue', 'wsrep_local_bf_aborts', 'wsrep_local_cert_failures',
            'wsrep_received', 'wsrep_received_bytes', 'wsrep_replicated', 'wsrep_replicated_bytes',
            'wsrep_repl_data_bytes', 'wsrep_repl_keys', 'wsrep_repl_keys_bytes',
            'wsrep_ist_receive_seqno_start', 'wsrep_ist_receive_seqno_current', 'wsrep_ist_receive_seqno_end',
            'mysql_datadir_path', 'mysql_datadir_total_size', 'mysql_datadir_clean_size',
            'mysql_sst_elapsed_sec', 'mysql_sst_in_progress',
        ];
        $historyKeys = [
            'mysql_available', 'wsrep_cluster_size', 'wsrep_local_state',
            'wsrep_flow_control_paused', 'wsrep_flow_control_paused_ns',
            'wsrep_local_recv_queue', 'wsrep_local_send_queue',
            'wsrep_flow_control_recv', 'wsrep_flow_control_sent',
            'wsrep_received', 'wsrep_received_bytes', 'wsrep_replicated', 'wsrep_replicated_bytes',
            'wsrep_repl_data_bytes', 'wsrep_repl_keys', 'wsrep_repl_keys_bytes',
            'wsrep_ist_receive_seqno_start', 'wsrep_ist_receive_seqno_current', 'wsrep_ist_receive_seqno_end',
            'mysql_datadir_total_size', 'mysql_datadir_clean_size',
            'mysql_sst_elapsed_sec', 'mysql_sst_in_progress',
        ];

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);
        $clusterNodeIds = self::resolveGaleraClusterNodeIds(
            $idMysqlServer,
            (string)($current['wsrep_incoming_addresses'] ?? ''),
            (string)($current['wsrep_cluster_address'] ?? '')
        );
        $memberRows = self::buildGaleraMemberRows($clusterNodeIds);

        $summaryCards = [
            self::buildStatusCard('Cluster', (string)($current['wsrep_cluster_name'] ?? 'n/a'), 'Equivalent to PMM cluster selector context.'),
            self::buildStatusCard('Cluster size', self::formatScalar($current['wsrep_cluster_size'] ?? null), 'Equivalent to PMM wsrep_cluster_size.'),
            self::buildStatusCard('Cluster status', (string)($current['wsrep_cluster_status'] ?? 'n/a'), 'Equivalent to PMM cluster status summary.'),
            self::buildStatusCard('Local state', (string)($current['wsrep_local_state_comment'] ?? 'n/a'), 'Equivalent to PMM node state summary.'),
            self::buildStatusCard('Datadir size', self::formatBytes($current['mysql_datadir_total_size'] ?? null), 'PmaControl SSH collector for datadir size.'),
        ];

        $sections = [
            [
                'title' => 'Cluster summary',
                'description' => 'Equivalent to PMM PXC Galera Cluster Summary main wsrep state and quorum view.',
                'cards' => [
                    self::buildMetricCard('wsrep_on', $current['wsrep_on'] ?? null, 'variables::wsrep_on', 'Galera enabled'),
                    self::buildMetricCard('Cluster name', $current['wsrep_cluster_name'] ?? null, 'variables::wsrep_cluster_name', 'Cluster label'),
                    self::buildMetricCard('Cluster status', $current['wsrep_cluster_status'] ?? null, 'status::wsrep_cluster_status', 'Primary / Non-primary'),
                    self::buildMetricCard('Local state', $current['wsrep_local_state_comment'] ?? null, 'status::wsrep_local_state_comment', 'Node state'),
                    self::buildMetricCard('Ready', $current['wsrep_ready'] ?? null, 'status::wsrep_ready', 'Node ready'),
                    self::buildMetricCard('Connected', $current['wsrep_connected'] ?? null, 'status::wsrep_connected', 'Node connected'),
                    self::buildMetricCard('Cluster size', $current['wsrep_cluster_size'] ?? null, 'status::wsrep_cluster_size', 'Cluster size'),
                    self::buildMetricCard('Provider version', $current['wsrep_provider_version'] ?? null, 'status::wsrep_provider_version', 'Galera provider'),
                ],
                'charts' => [
                    self::buildRawChart('galera_cluster_size', 'Cluster size', 'line', 'count', [
                        'wsrep_cluster_size' => 'Cluster size',
                    ], $historyRows, $range, 'Cluster size', 'mysql_global_status_wsrep_cluster_size'),
                    self::buildRawChart('galera_node_state', 'Node state and availability', 'line', 'count', [
                        'wsrep_local_state' => 'Local state id',
                        'mysql_available' => 'mysql_available',
                    ], $historyRows, $range, 'Node state / availability', 'mysql_global_status_wsrep_local_state + mysql_available'),
                ],
                'tables' => [
                    [
                        'title' => 'Current cluster identity',
                        'columns' => ['Metric', 'Value'],
                        'rows' => self::rowsFromAssoc([
                            'wsrep_cluster_name' => $current['wsrep_cluster_name'] ?? 'n/a',
                            'wsrep_cluster_address' => $current['wsrep_cluster_address'] ?? 'n/a',
                            'wsrep_incoming_addresses' => $current['wsrep_incoming_addresses'] ?? 'n/a',
                            'wsrep_cluster_state_uuid' => $current['wsrep_cluster_state_uuid'] ?? 'n/a',
                            'wsrep_gcomm_uuid' => $current['wsrep_gcomm_uuid'] ?? 'n/a',
                            'wsrep_node_name' => $current['wsrep_node_name'] ?? 'n/a',
                            'wsrep_node_address' => $current['wsrep_node_address'] ?? 'n/a',
                        ]),
                    ],
                ],
                'notes' => [
                    'PMM also exposes EVS detail and some PXC-specific cluster comparison panels. PmaControl currently rebuilds the wsrep view from already collected metrics only.',
                ],
            ],
            [
                'title' => 'Flow control, queues and replication payload',
                'description' => 'Equivalent to PMM flow control, queue pressure, writeset and payload panels.',
                'cards' => [
                    self::buildMetricCard('Flow control paused', $current['wsrep_flow_control_paused'] ?? null, 'status::wsrep_flow_control_paused', 'Flow control paused'),
                    self::buildMetricCard('Recv queue', $current['wsrep_local_recv_queue'] ?? null, 'status::wsrep_local_recv_queue', 'Local recv queue'),
                    self::buildMetricCard('Send queue', $current['wsrep_local_send_queue'] ?? null, 'status::wsrep_local_send_queue', 'Local send queue'),
                    self::buildMetricCard('Cert deps distance', $current['wsrep_cert_deps_distance'] ?? null, 'status::wsrep_cert_deps_distance', 'Parallelization window'),
                    self::buildMetricCard('Apply window', $current['wsrep_apply_window'] ?? null, 'status::wsrep_apply_window', 'Apply window'),
                    self::buildMetricCard('Commit window', $current['wsrep_commit_window'] ?? null, 'status::wsrep_commit_window', 'Commit window'),
                ],
                'charts' => [
                    self::buildRawChart('galera_flow_queues', 'Flow control and queues', 'line', 'count', [
                        'wsrep_flow_control_paused' => 'Flow control paused',
                        'wsrep_local_recv_queue' => 'Recv queue',
                        'wsrep_local_send_queue' => 'Send queue',
                    ], $historyRows, $range, 'Flow control and queues', 'mysql_global_status_wsrep_flow_control_paused / wsrep_local_recv_queue / wsrep_local_send_queue'),
                    self::buildRateChart('galera_flow_events', 'Flow control events per second', 'count', [
                        'wsrep_flow_control_recv' => 'Flow control recv/s',
                        'wsrep_flow_control_sent' => 'Flow control sent/s',
                    ], $historyRows, $range, 'Flow control recv/sent', 'mysql_global_status_wsrep_flow_control_recv / wsrep_flow_control_sent'),
                    self::buildRateChart('galera_payload_bytes', 'Replication payload per second', 'bytes', [
                        'wsrep_received_bytes' => 'Received bytes/s',
                        'wsrep_replicated_bytes' => 'Replicated bytes/s',
                        'wsrep_repl_data_bytes' => 'Writeset data bytes/s',
                    ], $historyRows, $range, 'Replication payload', 'mysql_global_status_wsrep_* bytes'),
                ],
                'tables' => [
                    [
                        'title' => 'Current wsrep counters',
                        'columns' => ['Metric', 'Value'],
                        'rows' => self::rowsFromAssoc([
                            'wsrep_flow_control_paused_ns' => $current['wsrep_flow_control_paused_ns'] ?? 'n/a',
                            'wsrep_flow_control_recv' => $current['wsrep_flow_control_recv'] ?? 'n/a',
                            'wsrep_flow_control_sent' => $current['wsrep_flow_control_sent'] ?? 'n/a',
                            'wsrep_received' => $current['wsrep_received'] ?? 'n/a',
                            'wsrep_received_bytes' => self::formatBytes($current['wsrep_received_bytes'] ?? null),
                            'wsrep_replicated' => $current['wsrep_replicated'] ?? 'n/a',
                            'wsrep_replicated_bytes' => self::formatBytes($current['wsrep_replicated_bytes'] ?? null),
                            'wsrep_repl_data_bytes' => self::formatBytes($current['wsrep_repl_data_bytes'] ?? null),
                            'wsrep_repl_keys' => $current['wsrep_repl_keys'] ?? 'n/a',
                            'wsrep_repl_keys_bytes' => self::formatBytes($current['wsrep_repl_keys_bytes'] ?? null),
                            'wsrep_local_bf_aborts' => $current['wsrep_local_bf_aborts'] ?? 'n/a',
                            'wsrep_local_cert_failures' => $current['wsrep_local_cert_failures'] ?? 'n/a',
                        ]),
                    ],
                ],
                'notes' => [
                    'PMM often derives rates from cumulative wsrep counters. This rebuild uses PmaControl bucketed deltas on the selected range.',
                ],
            ],
            [
                'title' => 'SST and datadir',
                'description' => 'Additional operational view requested on top of PMM: SST donor/joiner progress and datadir size evolution.',
                'cards' => [
                    self::buildMetricCard('SST method', $current['wsrep_sst_method'] ?? null, 'variables::wsrep_sst_method', 'SST configuration'),
                    self::buildMetricCard('SST donor', $current['wsrep_sst_donor'] ?? null, 'variables::wsrep_sst_donor', 'SST donor setting'),
                    self::buildMetricCard('SST receiver', $current['wsrep_sst_receive_address'] ?? null, 'variables::wsrep_sst_receive_address', 'SST receiver address'),
                    self::buildMetricCard('SST in progress', $current['mysql_sst_in_progress'] ?? null, 'ssh_stats::mysql_sst_in_progress', 'PmaControl SST detector'),
                    self::buildMetricCard('SST elapsed', $current['mysql_sst_elapsed_sec'] ?? null, 'ssh_stats::mysql_sst_elapsed_sec', 'PmaControl SST elapsed seconds'),
                    self::buildMetricCard('Datadir total size', $current['mysql_datadir_total_size'] ?? null, 'ssh_stats::mysql_datadir_total_size', 'PmaControl datadir size'),
                    self::buildMetricCard('Datadir clean size', $current['mysql_datadir_clean_size'] ?? null, 'ssh_stats::mysql_datadir_clean_size', 'PmaControl datadir clean size'),
                ],
                'charts' => [
                    self::buildRawChart('galera_sst_progress', 'SST donor/joiner state', 'line', 'count', [
                        'mysql_sst_in_progress' => 'SST in progress',
                        'mysql_sst_elapsed_sec' => 'SST elapsed sec',
                        'wsrep_local_state' => 'Local state id',
                    ], $historyRows, $range, 'SST donor/joiner progression', 'ssh_stats::mysql_sst_* + mysql_global_status_wsrep_local_state'),
                    self::buildRawChart('galera_ist_progress', 'IST Progress', 'line', 'count', [
                        'wsrep_ist_receive_seqno_start' => 'IST first',
                        'wsrep_ist_receive_seqno_current' => 'IST current',
                        'wsrep_ist_receive_seqno_end' => 'IST last',
                    ], $historyRows, $range, 'IST Progress', 'PMM panel 56 / mysql_global_status_wsrep_ist_receive_seqno_start,current,end'),
                    self::buildRawChart('galera_datadir_size', 'Datadir size', 'line', 'bytes', [
                        'mysql_datadir_total_size' => 'Datadir total',
                        'mysql_datadir_clean_size' => 'Datadir clean',
                    ], $historyRows, $range, 'Datadir size', 'ssh_stats::mysql_datadir_*'),
                ],
                'tables' => [
                    [
                        'title' => 'Current SST and datadir snapshot',
                        'columns' => ['Metric', 'Value'],
                        'rows' => self::rowsFromAssoc([
                            'mysql_datadir_path' => $current['mysql_datadir_path'] ?? 'n/a',
                            'mysql_datadir_total_size' => self::formatBytes($current['mysql_datadir_total_size'] ?? null),
                            'mysql_datadir_clean_size' => self::formatBytes($current['mysql_datadir_clean_size'] ?? null),
                            'mysql_sst_in_progress' => $current['mysql_sst_in_progress'] ?? 'n/a',
                            'mysql_sst_elapsed_sec' => $current['mysql_sst_elapsed_sec'] ?? 'n/a',
                            'wsrep_sst_method' => $current['wsrep_sst_method'] ?? 'n/a',
                            'wsrep_sst_donor' => $current['wsrep_sst_donor'] ?? 'n/a',
                            'wsrep_sst_donor_rejects_queries' => $current['wsrep_sst_donor_rejects_queries'] ?? 'n/a',
                            'wsrep_sst_receive_address' => $current['wsrep_sst_receive_address'] ?? 'n/a',
                            'wsrep_ist_receive_seqno_start' => $current['wsrep_ist_receive_seqno_start'] ?? 'n/a',
                            'wsrep_ist_receive_seqno_current' => $current['wsrep_ist_receive_seqno_current'] ?? 'n/a',
                            'wsrep_ist_receive_seqno_end' => $current['wsrep_ist_receive_seqno_end'] ?? 'n/a',
                        ]),
                    ],
                ],
                'notes' => [
                    'PMM panel 56 "IST Progress" is rebuilt here from wsrep_ist_receive_seqno_start/current/end.',
                    'This section is not a one-to-one PMM panel. It extends the Galera screen with PmaControl-only SST and datadir telemetry already used by Dot3.',
                ],
            ],
            [
                'title' => 'Cluster members',
                'description' => 'Cluster nodes resolved from wsrep addresses through Dot3 mapping.',
                'cards' => [
                    self::buildMetricCard('Resolved members', count($memberRows), 'Dot3::getIdMysqlServerFromGalera + mysql_server', 'Cluster member discovery'),
                ],
                'charts' => [],
                'tables' => [
                    [
                        'title' => 'Current members',
                        'columns' => ['Server', 'IP', 'Port', 'mysql_available', 'Cluster status', 'Node state', 'Ready', 'Datadir', 'SST'],
                        'rows' => $memberRows,
                    ],
                ],
                'notes' => [
                    'Member resolution uses wsrep_incoming_addresses first, then falls back to wsrep_cluster_address, and maps addresses back to mysql_server via Dot3-compatible parsing.',
                ],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildMyISAM(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'key_buffer_size', 'key_blocks_used', 'key_blocks_unused',
            'key_read_requests', 'key_reads', 'key_write_requests', 'key_writes',
            'myisam_sort_buffer_size', 'myisam_recover_options', 'delay_key_write',
        ];
        $historyKeys = [
            'key_blocks_used', 'key_blocks_unused', 'key_read_requests', 'key_reads', 'key_write_requests', 'key_writes',
        ];

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);
        $myisamHitRatio = self::computeHitRatio($current['key_reads'] ?? null, $current['key_read_requests'] ?? null);

        $summaryCards = [
            self::buildStatusCard('MyISAM key buffer', self::formatBytes($current['key_buffer_size'] ?? null), 'Equivalent to PMM Key Buffer.'),
            self::buildStatusCard('MyISAM hit ratio', self::formatPercent($myisamHitRatio), 'Equivalent to PMM MyISAM cache efficiency.'),
            self::buildStatusCard('MyISAM sort buffer', self::formatBytes($current['myisam_sort_buffer_size'] ?? null), 'Current MyISAM sort buffer size.'),
        ];

        $sections = [
            [
                'title' => 'MyISAM key cache',
                'description' => 'Equivalent to PMM MyISAM metrics, indexes and key buffer performance.',
                'cards' => [
                    self::buildMetricCard('Key buffer size', $current['key_buffer_size'] ?? null, 'variables::key_buffer_size', 'Key Buffer'),
                    self::buildMetricCard('Key cache blocks used', $current['key_blocks_used'] ?? null, 'status::key_blocks_used', 'Key Cache'),
                    self::buildMetricCard('MyISAM hit ratio', $myisamHitRatio, 'status::key_reads / key_read_requests', 'MyISAM cache hit ratio'),
                ],
                'charts' => [
                    self::buildRawChart('myisam_key_blocks', 'MyISAM key blocks', 'line', 'count', [
                        'key_blocks_used' => 'Blocks used',
                        'key_blocks_unused' => 'Blocks unused',
                    ], $historyRows, $range, 'Key Cache', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildRateChart('myisam_key_ops', 'MyISAM key cache activity', 'ops_per_second', [
                        'key_read_requests' => 'Read requests/s',
                        'key_reads' => 'Physical reads/s',
                        'key_write_requests' => 'Write requests/s',
                        'key_writes' => 'Physical writes/s',
                    ], $historyRows, $range, 'Key Buffer Performance', 'mysql exporter / SHOW GLOBAL STATUS'),
                    self::buildHitRatioChart('myisam_hit_ratio', 'MyISAM key cache hit ratio', 'percent', 'key_reads', 'key_read_requests', $historyRows, $range, 'MyISAM cache hit ratio', 'calculated from MyISAM cumulative key cache counters'),
                ],
                'tables' => [
                    [
                        'title' => 'MyISAM configuration snapshot',
                        'columns' => ['Metric', 'Value'],
                        'rows' => [
                            ['myisam_sort_buffer_size', self::formatBytes($current['myisam_sort_buffer_size'] ?? null)],
                            ['myisam_recover_options', self::formatScalar($current['myisam_recover_options'] ?? null)],
                            ['delay_key_write', self::formatScalar($current['delay_key_write'] ?? null)],
                        ],
                    ],
                ],
                'notes' => [],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildPerformanceSchema(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'performance_schema', 'max_digest_length', 'performance_schema_max_digest_length',
            'performance_schema_digests_size', 'performance_schema_accounts_size', 'performance_schema_hosts_size',
            'performance_schema_users_size', 'performance_schema_max_table_handles', 'performance_schema_max_table_instances',
            'performance_schema_max_thread_instances', 'performance_schema_max_thread_classes',
            'performance_schema_max_file_handles', 'performance_schema_max_file_instances',
            'performance_schema_max_mutex_instances', 'performance_schema_max_rwlock_instances',
            'performance_schema_max_socket_instances', 'performance_schema_max_statement_classes',
            'performance_schema_max_stage_classes', 'performance_schema_session_connect_attrs_size',
            'performance_schema_setup_actors_size', 'performance_schema_setup_objects_size',
            'performance_schema_events_statements_history_size', 'performance_schema_events_statements_history_long_size',
            'performance_schema_events_stages_history_size', 'performance_schema_events_stages_history_long_size',
            'performance_schema_events_waits_history_size', 'performance_schema_events_waits_history_long_size',
        ];
        $historyKeys = $currentKeys;

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);

        $summaryCards = [
            self::buildStatusCard('Performance Schema', self::formatScalar($current['performance_schema'] ?? null), 'Equivalent to PMM Performance Schema enabled state.'),
            self::buildStatusCard('Digests size', self::formatScalar($current['performance_schema_digests_size'] ?? null), 'Equivalent to PMM digest sizing context.'),
            self::buildStatusCard('Stmt history', self::formatScalar($current['performance_schema_events_statements_history_size'] ?? null), 'Equivalent to PMM statement history sizing.'),
            self::buildStatusCard('Table handles', self::formatScalar($current['performance_schema_max_table_handles'] ?? null), 'Equivalent to PMM table-handle capacity view.'),
            self::buildStatusCard('Thread instances', self::formatScalar($current['performance_schema_max_thread_instances'] ?? null), 'Equivalent to PMM thread instrumentation capacity view.'),
        ];

        $sections = [
            [
                'title' => 'Enablement and digest sizing',
                'description' => 'Equivalent to PMM Performance Schema overview cards for enablement, digest support and digest sizing.',
                'cards' => [
                    self::buildMetricCard('performance_schema', $current['performance_schema'] ?? null, 'variables::performance_schema', 'P_S enabled'),
                    self::buildMetricCard('max_digest_length', $current['max_digest_length'] ?? null, 'variables::max_digest_length', 'Digest SQL truncation length'),
                    self::buildMetricCard('performance_schema_max_digest_length', $current['performance_schema_max_digest_length'] ?? null, 'variables::performance_schema_max_digest_length', 'P_S digest storage length'),
                    self::buildMetricCard('performance_schema_digests_size', $current['performance_schema_digests_size'] ?? null, 'variables::performance_schema_digests_size', 'Digest entries capacity'),
                ],
                'charts' => [
                    self::buildRawChart('ps_digest_caps', 'Digest capacities', 'line', 'count', [
                        'max_digest_length' => 'max_digest_length',
                        'performance_schema_max_digest_length' => 'performance_schema_max_digest_length',
                        'performance_schema_digests_size' => 'performance_schema_digests_size',
                    ], $historyRows, $range, 'Digest capacities', 'mysql_global_variables_* performance_schema digest settings'),
                ],
                'tables' => [
                    [
                        'title' => 'Digest and connect attrs snapshot',
                        'columns' => ['Metric', 'Value'],
                        'rows' => self::rowsFromAssoc([
                            'performance_schema' => $current['performance_schema'] ?? 'n/a',
                            'max_digest_length' => $current['max_digest_length'] ?? 'n/a',
                            'performance_schema_max_digest_length' => $current['performance_schema_max_digest_length'] ?? 'n/a',
                            'performance_schema_digests_size' => $current['performance_schema_digests_size'] ?? 'n/a',
                            'performance_schema_session_connect_attrs_size' => $current['performance_schema_session_connect_attrs_size'] ?? 'n/a',
                        ]),
                    ],
                ],
                'notes' => [
                    'PMM details dashboard also uses exporter collectors over Performance Schema statements and waits. This screen starts from the sizing and enablement data PmaControl already has.',
                ],
            ],
            [
                'title' => 'History buffers',
                'description' => 'Equivalent to PMM history-size panels for statements, waits and stages history buffers.',
                'cards' => [
                    self::buildMetricCard('Statements history', $current['performance_schema_events_statements_history_size'] ?? null, 'variables::performance_schema_events_statements_history_size', 'Statements history size'),
                    self::buildMetricCard('Statements history long', $current['performance_schema_events_statements_history_long_size'] ?? null, 'variables::performance_schema_events_statements_history_long_size', 'Statements history long size'),
                    self::buildMetricCard('Waits history', $current['performance_schema_events_waits_history_size'] ?? null, 'variables::performance_schema_events_waits_history_size', 'Waits history size'),
                    self::buildMetricCard('Stages history', $current['performance_schema_events_stages_history_size'] ?? null, 'variables::performance_schema_events_stages_history_size', 'Stages history size'),
                ],
                'charts' => [
                    self::buildRawChart('ps_history_sizes', 'History buffer sizes', 'line', 'count', [
                        'performance_schema_events_statements_history_size' => 'Stmt history',
                        'performance_schema_events_statements_history_long_size' => 'Stmt history long',
                        'performance_schema_events_waits_history_size' => 'Waits history',
                        'performance_schema_events_waits_history_long_size' => 'Waits history long',
                        'performance_schema_events_stages_history_size' => 'Stages history',
                        'performance_schema_events_stages_history_long_size' => 'Stages history long',
                    ], $historyRows, $range, 'History buffers', 'mysql_global_variables_* performance_schema history sizes'),
                ],
                'tables' => [],
                'notes' => [
                    'These are capacities, not current usage. PMM can also show actual sampled waits/events when exporter collectors are enabled.',
                ],
            ],
            [
                'title' => 'Instrumentation capacities',
                'description' => 'Equivalent to PMM tables describing Performance Schema memory footprint and instrumentation limits.',
                'cards' => [
                    self::buildMetricCard('Accounts size', $current['performance_schema_accounts_size'] ?? null, 'variables::performance_schema_accounts_size', 'Accounts instrumentation capacity'),
                    self::buildMetricCard('Hosts size', $current['performance_schema_hosts_size'] ?? null, 'variables::performance_schema_hosts_size', 'Hosts instrumentation capacity'),
                    self::buildMetricCard('Users size', $current['performance_schema_users_size'] ?? null, 'variables::performance_schema_users_size', 'Users instrumentation capacity'),
                    self::buildMetricCard('Setup actors', $current['performance_schema_setup_actors_size'] ?? null, 'variables::performance_schema_setup_actors_size', 'Setup actors size'),
                    self::buildMetricCard('Setup objects', $current['performance_schema_setup_objects_size'] ?? null, 'variables::performance_schema_setup_objects_size', 'Setup objects size'),
                ],
                'charts' => [
                    self::buildRawChart('ps_capacity_limits', 'Instrumentation limits', 'line', 'count', [
                        'performance_schema_max_table_handles' => 'Table handles',
                        'performance_schema_max_table_instances' => 'Table instances',
                        'performance_schema_max_thread_instances' => 'Thread instances',
                        'performance_schema_max_file_handles' => 'File handles',
                        'performance_schema_max_file_instances' => 'File instances',
                    ], $historyRows, $range, 'Instrumentation limits', 'mysql_global_variables_* performance_schema max_*'),
                ],
                'tables' => [
                    [
                        'title' => 'Current Performance Schema capacities',
                        'columns' => ['Metric', 'Value'],
                        'rows' => self::rowsFromAssoc([
                            'performance_schema_accounts_size' => $current['performance_schema_accounts_size'] ?? 'n/a',
                            'performance_schema_hosts_size' => $current['performance_schema_hosts_size'] ?? 'n/a',
                            'performance_schema_users_size' => $current['performance_schema_users_size'] ?? 'n/a',
                            'performance_schema_max_table_handles' => $current['performance_schema_max_table_handles'] ?? 'n/a',
                            'performance_schema_max_table_instances' => $current['performance_schema_max_table_instances'] ?? 'n/a',
                            'performance_schema_max_thread_classes' => $current['performance_schema_max_thread_classes'] ?? 'n/a',
                            'performance_schema_max_thread_instances' => $current['performance_schema_max_thread_instances'] ?? 'n/a',
                            'performance_schema_max_file_handles' => $current['performance_schema_max_file_handles'] ?? 'n/a',
                            'performance_schema_max_file_instances' => $current['performance_schema_max_file_instances'] ?? 'n/a',
                            'performance_schema_max_statement_classes' => $current['performance_schema_max_statement_classes'] ?? 'n/a',
                            'performance_schema_max_stage_classes' => $current['performance_schema_max_stage_classes'] ?? 'n/a',
                            'performance_schema_max_mutex_instances' => $current['performance_schema_max_mutex_instances'] ?? 'n/a',
                            'performance_schema_max_rwlock_instances' => $current['performance_schema_max_rwlock_instances'] ?? 'n/a',
                            'performance_schema_max_socket_instances' => $current['performance_schema_max_socket_instances'] ?? 'n/a',
                        ]),
                    ],
                ],
                'notes' => [
                    'Gap vs PMM: current PmaControl rebuild does not yet expose waits, file instances, or exporter-derived `mysql_perf_schema_*` activity panels.',
                ],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildRocksDb(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'rocksdb_block_cache_size', 'rocksdb_db_write_buffer_size', 'rocksdb_max_total_wal_size',
            'rocksdb_wal_size_limit_mb', 'rocksdb_flush_log_at_trx_commit', 'rocksdb_write_disable_wal',
            'rocksdb_info_log_level', 'rocksdb_enable_ttl', 'rocksdb_cache_index_and_filter_blocks',
            'rocksdb_use_direct_reads', 'rocksdb_use_direct_io_for_flush_and_compaction',
            'rocksdb_rows_read', 'rocksdb_rows_inserted', 'rocksdb_rows_updated', 'rocksdb_rows_deleted',
            'rocksdb_bytes_read', 'rocksdb_bytes_written', 'rocksdb_block_cache_hit', 'rocksdb_block_cache_miss',
            'rocksdb_block_cache_index_hit', 'rocksdb_block_cache_index_miss', 'rocksdb_block_cache_filter_hit',
            'rocksdb_block_cache_filter_miss', 'rocksdb_memtable_hit', 'rocksdb_memtable_miss',
            'rocksdb_wal_bytes', 'rocksdb_wal_group_syncs', 'rocksdb_wal_synced',
            'rocksdb_stall_total_slowdowns', 'rocksdb_stall_total_stops', 'rocksdb_stall_micros',
            'rocksdb_row_lock_deadlocks', 'rocksdb_row_lock_wait_timeouts', 'rocksdb_memtable_total', 'rocksdb_memtable_unflushed',
        ];
        $historyKeys = $currentKeys;

        [$current, $historyRows] = self::getMetricBundle($idMysqlServer, $currentKeys, $historyKeys, $range);

        $summaryCards = [
            self::buildStatusCard('Block cache', self::formatBytes($current['rocksdb_block_cache_size'] ?? null), 'Equivalent to PMM RocksDB cache section.'),
            self::buildStatusCard('Write buffer', self::formatBytes($current['rocksdb_db_write_buffer_size'] ?? null), 'Equivalent to PMM memtable/write buffer sizing.'),
            self::buildStatusCard('WAL limit', self::formatScalar($current['rocksdb_wal_size_limit_mb'] ?? null) . ' MiB', 'Equivalent to PMM WAL sizing context.'),
            self::buildStatusCard('TTL', self::formatScalar($current['rocksdb_enable_ttl'] ?? null), 'Current MyRocks TTL setting.'),
        ];

        $sections = [
            [
                'title' => 'Read/write activity',
                'description' => 'Equivalent to PMM DB ops, reads/writes and iteration bytes sections.',
                'cards' => [
                    self::buildMetricCard('Rows read', $current['rocksdb_rows_read'] ?? null, 'status::rocksdb_rows_read', 'DB ops'),
                    self::buildMetricCard('Rows inserted', $current['rocksdb_rows_inserted'] ?? null, 'status::rocksdb_rows_inserted', 'DB ops'),
                ],
                'charts' => [
                    self::buildRateChart('rocksdb_rows', 'RocksDB row operations', 'ops_per_second', [
                        'rocksdb_rows_read' => 'Rows read/s',
                        'rocksdb_rows_inserted' => 'Rows inserted/s',
                        'rocksdb_rows_updated' => 'Rows updated/s',
                        'rocksdb_rows_deleted' => 'Rows deleted/s',
                    ], $historyRows, $range, 'DB Ops', 'myrocks status counters'),
                    self::buildRateChart('rocksdb_bytes', 'RocksDB bytes throughput', 'bytes_per_second', [
                        'rocksdb_bytes_read' => 'Bytes read/s',
                        'rocksdb_bytes_written' => 'Bytes written/s',
                    ], $historyRows, $range, 'R/W', 'myrocks status counters'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'Cache & filters',
                'description' => 'Equivalent to PMM cache, index/filter and bloom efficiency panels.',
                'cards' => [
                    self::buildMetricCard('Block cache size', $current['rocksdb_block_cache_size'] ?? null, 'variables::rocksdb_block_cache_size', 'Block cache'),
                    self::buildMetricCard('Memtable total', $current['rocksdb_memtable_total'] ?? null, 'status::rocksdb_memtable_total', 'Memtable'),
                    self::buildMetricCard('Memtable unflushed', $current['rocksdb_memtable_unflushed'] ?? null, 'status::rocksdb_memtable_unflushed', 'Memtable'),
                ],
                'charts' => [
                    self::buildHitMissRatioChart('rocksdb_block_cache_hit_ratio', 'Block cache hit ratio', 'percent', 'rocksdb_block_cache_hit', 'rocksdb_block_cache_miss', $historyRows, $range, 'Block cache hit ratio', 'calculated from block cache hits and misses'),
                    self::buildHitMissRatioChart('rocksdb_index_hit_ratio', 'Index cache hit ratio', 'percent', 'rocksdb_block_cache_index_hit', 'rocksdb_block_cache_index_miss', $historyRows, $range, 'Index cache hit ratio', 'calculated from index cache hits and misses'),
                    self::buildHitMissRatioChart('rocksdb_filter_hit_ratio', 'Filter cache hit ratio', 'percent', 'rocksdb_block_cache_filter_hit', 'rocksdb_block_cache_filter_miss', $historyRows, $range, 'Filter cache hit ratio', 'calculated from filter cache hits and misses'),
                    self::buildHitMissRatioChart('rocksdb_memtable_hit_ratio', 'Memtable hit ratio', 'percent', 'rocksdb_memtable_hit', 'rocksdb_memtable_miss', $historyRows, $range, 'Memtable hit ratio', 'calculated from memtable hits and misses'),
                ],
                'tables' => [],
                'notes' => [],
            ],
            [
                'title' => 'WAL & write path',
                'description' => 'Equivalent to PMM WAL and flush/write-path panels.',
                'cards' => [
                    self::buildMetricCard('WAL size limit', $current['rocksdb_wal_size_limit_mb'] ?? null, 'variables::rocksdb_wal_size_limit_mb', 'WAL'),
                    self::buildMetricCard('Disable WAL', $current['rocksdb_write_disable_wal'] ?? null, 'variables::rocksdb_write_disable_wal', 'WAL'),
                ],
                'charts' => [
                    self::buildRateChart('rocksdb_wal', 'WAL activity', 'ops_per_second', [
                        'rocksdb_wal_group_syncs' => 'WAL group syncs/s',
                        'rocksdb_wal_synced' => 'WAL synced/s',
                    ], $historyRows, $range, 'WAL', 'myrocks status counters'),
                    self::buildRateChart('rocksdb_wal_bytes', 'WAL bytes', 'bytes_per_second', [
                        'rocksdb_wal_bytes' => 'WAL bytes/s',
                    ], $historyRows, $range, 'WAL', 'myrocks status counters'),
                ],
                'tables' => [
                    [
                        'title' => 'RocksDB configuration snapshot',
                        'columns' => ['Metric', 'Value'],
                        'rows' => [
                            ['rocksdb_flush_log_at_trx_commit', self::formatScalar($current['rocksdb_flush_log_at_trx_commit'] ?? null)],
                            ['rocksdb_info_log_level', self::formatScalar($current['rocksdb_info_log_level'] ?? null)],
                            ['rocksdb_cache_index_and_filter_blocks', self::formatScalar($current['rocksdb_cache_index_and_filter_blocks'] ?? null)],
                            ['rocksdb_use_direct_reads', self::formatScalar($current['rocksdb_use_direct_reads'] ?? null)],
                            ['rocksdb_use_direct_io_for_flush_and_compaction', self::formatScalar($current['rocksdb_use_direct_io_for_flush_and_compaction'] ?? null)],
                        ],
                    ],
                ],
                'notes' => [],
            ],
            [
                'title' => 'Stalls & locking',
                'description' => 'Equivalent to PMM stall, stop/slowdown and row-locking panels.',
                'cards' => [],
                'charts' => [
                    self::buildRateChart('rocksdb_stalls', 'RocksDB stalls', 'ops_per_second', [
                        'rocksdb_stall_total_slowdowns' => 'Slowdowns/s',
                        'rocksdb_stall_total_stops' => 'Stops/s',
                        'rocksdb_row_lock_deadlocks' => 'Deadlocks/s',
                        'rocksdb_row_lock_wait_timeouts' => 'Row lock timeouts/s',
                    ], $historyRows, $range, 'Stalls / Stops', 'myrocks status counters'),
                    self::buildRateChart('rocksdb_stall_time', 'RocksDB stall time', 'milliseconds', [
                        'rocksdb_stall_micros' => 'Stall microseconds/s',
                    ], $historyRows, $range, 'Stalls', 'myrocks status counters'),
                ],
                'tables' => [],
                'notes' => [
                    'PMM MyRocks dashboard also exposes bloom details, seek/reseek breakdown and compaction internals. Those are documented but not all are collected today in PmaControl.',
                ],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function buildProxySql(int $idMysqlServer, array $server, array $dashboard, array $range, array $menu): array
    {
        $currentKeys = [
            'version', 'version_comment', 'proxysql_available', 'proxysql_connect_error',
            'proxysql_runtime::global_variables', 'proxysql_runtime::mysql_servers',
            'proxysql_runtime::proxysql_servers', 'proxysql_runtime::mysql_query_rules', 'proxysql_runtime::mysql_users',
        ];
        [$current] = self::getMetricBundle($idMysqlServer, $currentKeys, [], $range);

        $globalVariables = self::parseJsonList($current['global_variables'] ?? null);
        $mysqlServers = self::parseJsonList($current['mysql_servers'] ?? null);
        $proxysqlServers = self::parseJsonList($current['proxysql_servers'] ?? null);
        $queryRules = self::parseJsonList($current['mysql_query_rules'] ?? null);
        $mysqlUsers = self::parseJsonList($current['mysql_users'] ?? null);

        $summaryCards = [
            self::buildStatusCard('ProxySQL availability', self::formatAvailability($current['proxysql_available'] ?? null), 'Equivalent to PMM ProxySQL Instance status.'),
            self::buildStatusCard('Version', self::formatProductBanner($current), 'Current ProxySQL version banner.'),
            self::buildStatusCard('Backend entries', count($mysqlServers), 'Equivalent to PMM Hostgroup Size / Endpoint Status current snapshot.'),
            self::buildStatusCard('Query rules', count($queryRules), 'Equivalent to PMM Query routing configuration context.'),
        ];

        $sections = [
            [
                'title' => 'Runtime snapshot',
                'description' => 'Current runtime payload equivalent to the static parts of PMM ProxySQL Instance Summary.',
                'cards' => [
                    self::buildMetricCard('ProxySQL availability', $current['proxysql_available'] ?? null, 'proxysql_available', 'ProxySQL Instance Stats'),
                    self::buildMetricCard('Connect error', $current['proxysql_connect_error'] ?? null, 'proxysql_connect_error', 'ProxySQL connect error'),
                ],
                'charts' => [],
                'tables' => [
                    [
                        'title' => 'Global variables',
                        'columns' => ['Variable', 'Value'],
                        'rows' => self::rowsFromAssoc(self::indexListByVariableName($globalVariables)),
                    ],
                ],
                'notes' => [
                    'PMM exposes historical frontend/backends connections, routed queries, latency, memory and query cache efficiency from the ProxySQL exporter.',
                    'PmaControl currently stores runtime JSON snapshots, not the full historical ProxySQL metric set.',
                ],
            ],
            [
                'title' => 'Backend topology',
                'description' => 'Equivalent to PMM Hostgroup Size and Endpoint Status tables.',
                'cards' => [],
                'charts' => [],
                'tables' => [
                    [
                        'title' => 'mysql_servers',
                        'columns' => ['Hostgroup', 'Hostname', 'Port', 'Status', 'Weight', 'Compression', 'Connections'],
                        'rows' => self::buildProxySqlBackendRows($mysqlServers),
                    ],
                    [
                        'title' => 'proxysql_servers',
                        'columns' => ['Hostname', 'Port', 'Status', 'Comment'],
                        'rows' => self::buildProxySqlPeerRows($proxysqlServers),
                    ],
                ],
                'notes' => [],
            ],
            [
                'title' => 'Routing configuration',
                'description' => 'Equivalent to PMM query routing / rules context, but without exporter time series.',
                'cards' => [],
                'charts' => [],
                'tables' => [
                    [
                        'title' => 'mysql_query_rules',
                        'columns' => ['Rule id', 'Active', 'Username', 'Schemaname', 'Destination hostgroup', 'Apply'],
                        'rows' => self::buildProxySqlRuleRows($queryRules),
                    ],
                    [
                        'title' => 'mysql_users',
                        'columns' => ['Username', 'Default hostgroup', 'Active', 'Transactions persistent'],
                        'rows' => self::buildProxySqlUserRows($mysqlUsers),
                    ],
                ],
                'notes' => [],
            ],
        ];

        return [
            'dashboard' => $dashboard,
            'server' => $server,
            'range' => $range,
            'menu' => $menu,
            'summary_cards' => $summaryCards,
            'sections' => $sections,
        ];
    }

    private static function getCurrentValues(int $idMysqlServer, array $keys): array
    {
        $raw = Extraction2::display($keys, [$idMysqlServer]);
        if (empty($raw) || !is_array($raw)) {
            return [];
        }

        $row = reset($raw);
        return is_array($row) ? $row : [];
    }

    private static function resolveGaleraClusterNodeIds(int $selfId, string $incomingAddresses, string $clusterAddress): array
    {
        $candidates = [];

        foreach ([$incomingAddresses, $clusterAddress] as $addressSource) {
            if (trim($addressSource) === '') {
                continue;
            }

            foreach (Dot3::getIdMysqlServerFromGalera($addressSource) as $address) {
                $address = trim((string)$address);
                if ($address !== '') {
                    $candidates[$address] = $address;
                }
            }
        }

        $nodeIds = [$selfId => $selfId];
        if (empty($candidates)) {
            return array_values($nodeIds);
        }

        $conditions = [];
        foreach ($candidates as $address) {
            [$ip, $port] = array_pad(explode(':', $address, 2), 2, '3306');
            $ip = trim((string)$ip);
            $port = (int)trim((string)$port);

            if ($ip === '') {
                continue;
            }

            $conditions[] = "(ip = '" . self::escapeSql($ip) . "' AND port = " . $port . ")";
        }

        if (empty($conditions)) {
            return array_values($nodeIds);
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id FROM mysql_server WHERE " . implode(' OR ', $conditions);
        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int)($row['id'] ?? 0);
            if ($id > 0) {
                $nodeIds[$id] = $id;
            }
        }

        sort($nodeIds);
        return array_values($nodeIds);
    }

    private static function buildGaleraMemberRows(array $clusterNodeIds): array
    {
        $clusterNodeIds = array_values(array_unique(array_filter(array_map('intval', $clusterNodeIds))));
        if (empty($clusterNodeIds)) {
            return [];
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, display_name, ip, port FROM mysql_server WHERE id IN (" . implode(',', $clusterNodeIds) . ") ORDER BY display_name, id";
        $res = $db->sql_query($sql);
        $serverRows = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $serverRows[(int)$row['id']] = $row;
        }

        $current = Extraction2::display([
            'mysql_available',
            'wsrep_cluster_status',
            'wsrep_local_state_comment',
            'wsrep_ready',
            'mysql_datadir_total_size',
            'mysql_sst_in_progress',
        ], $clusterNodeIds);

        $rows = [];
        foreach ($clusterNodeIds as $clusterNodeId) {
            $meta = $serverRows[$clusterNodeId] ?? ['display_name' => 'Server #' . $clusterNodeId, 'ip' => '', 'port' => ''];
            $metrics = $current[$clusterNodeId] ?? [];

            $rows[] = [
                $meta['display_name'] ?? ('Server #' . $clusterNodeId),
                $meta['ip'] ?? 'n/a',
                $meta['port'] ?? 'n/a',
                self::formatAvailability($metrics['mysql_available'] ?? null),
                (string)($metrics['wsrep_cluster_status'] ?? 'n/a'),
                (string)($metrics['wsrep_local_state_comment'] ?? 'n/a'),
                (string)($metrics['wsrep_ready'] ?? 'n/a'),
                self::formatBytes($metrics['mysql_datadir_total_size'] ?? null),
                self::formatScalar($metrics['mysql_sst_in_progress'] ?? null),
            ];
        }

        return $rows;
    }

    private static function getMetricBundle(int $idMysqlServer, array $currentKeys, array $historyKeys, array $range): array
    {
        $current = self::getCurrentValues($idMysqlServer, array_values(array_unique($currentKeys)));
        $history = [];

        if (!empty($historyKeys)) {
            $rawHistory = Extraction2::display(
                array_values(array_unique($historyKeys)),
                [$idMysqlServer],
                [$range['start']->format('Y-m-d H:i:s'), $range['end']->format('Y-m-d H:i:s')],
                true,
                false
            );

            $history = self::normalizeHistoryRows($rawHistory[$idMysqlServer] ?? []);
        }

        return [$current, $history];
    }

    private static function normalizeHistoryRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $date => $row) {
            if (!is_array($row)) {
                continue;
            }

            $timestamp = strtotime((string)$date);
            if ($timestamp === false) {
                continue;
            }

            $row['date'] = (string)$date;
            $row['timestamp'] = $timestamp;
            $normalized[] = $row;
        }

        usort($normalized, static function (array $a, array $b): int {
            return ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0);
        });

        return $normalized;
    }

    private static function buildRawChart(string $id, string $title, string $type, string $unit, array $metricLabels, array $historyRows, array $range, string $pmmPanel, string $pmmSource): array
    {
        $datasets = [];
        $colors = self::buildColorPalette(count($metricLabels));
        $bucketed = self::bucketizeRows($historyRows, $range['bucket_seconds']);
        $labels = array_column($bucketed, 'label');

        $index = 0;
        foreach ($metricLabels as $metric => $label) {
            $data = [];
            foreach ($bucketed as $bucket) {
                $data[] = self::toFloat($bucket['last'][$metric] ?? null);
            }

            $datasets[] = self::buildDataset($label, $data, $colors[$index]);
            $index++;
        }

        return [
            'id' => $id,
            'title' => $title,
            'type' => $type,
            'unit' => $unit,
            'labels' => $labels,
            'datasets' => $datasets,
            'meta' => [
                'pmm_panel' => $pmmPanel,
                'pmm_source' => $pmmSource,
                'equivalent_metrics' => implode(', ', array_keys($metricLabels)),
            ],
        ];
    }

    private static function buildRateChart(string $id, string $title, string $unit, array $metricLabels, array $historyRows, array $range, string $pmmPanel, string $pmmSource): array
    {
        $datasets = [];
        $colors = self::buildColorPalette(count($metricLabels));
        $bucketed = self::bucketizeRows($historyRows, $range['bucket_seconds']);
        $labels = array_column($bucketed, 'label');

        $index = 0;
        foreach ($metricLabels as $metric => $label) {
            $data = [];
            foreach ($bucketed as $bucket) {
                $data[] = self::computeBucketRate($bucket, $metric, $unit === 'milliseconds');
            }

            $datasets[] = self::buildDataset($label, $data, $colors[$index]);
            $index++;
        }

        return [
            'id' => $id,
            'title' => $title,
            'type' => 'line',
            'unit' => $unit,
            'labels' => $labels,
            'datasets' => $datasets,
            'meta' => [
                'pmm_panel' => $pmmPanel,
                'pmm_source' => $pmmSource,
                'equivalent_metrics' => implode(', ', array_keys($metricLabels)),
            ],
        ];
    }

    private static function buildHitRatioChart(string $id, string $title, string $unit, string $missMetric, string $baseMetric, array $historyRows, array $range, string $pmmPanel, string $pmmSource): array
    {
        $bucketed = self::bucketizeRows($historyRows, $range['bucket_seconds']);
        $labels = array_column($bucketed, 'label');
        $data = [];

        foreach ($bucketed as $bucket) {
            $baseDelta = self::computeBucketDelta($bucket, $baseMetric);
            $missDelta = self::computeBucketDelta($bucket, $missMetric);

            if ($baseDelta === null || $missDelta === null || $baseDelta <= 0) {
                $data[] = null;
                continue;
            }

            $ratio = 100 - (($missDelta / $baseDelta) * 100);
            $data[] = round(max(0.0, min(100.0, $ratio)), 2);
        }

        return [
            'id' => $id,
            'title' => $title,
            'type' => 'line',
            'unit' => $unit,
            'labels' => $labels,
            'datasets' => [
                self::buildDataset($title, $data, self::buildColorPalette(1)[0], true),
            ],
            'meta' => [
                'pmm_panel' => $pmmPanel,
                'pmm_source' => $pmmSource,
                'equivalent_metrics' => $missMetric . ', ' . $baseMetric,
            ],
        ];
    }

    private static function buildHitMissRatioChart(string $id, string $title, string $unit, string $hitMetric, string $missMetric, array $historyRows, array $range, string $pmmPanel, string $pmmSource): array
    {
        $bucketed = self::bucketizeRows($historyRows, $range['bucket_seconds']);
        $labels = array_column($bucketed, 'label');
        $data = [];

        foreach ($bucketed as $bucket) {
            $hitDelta = self::computeBucketDelta($bucket, $hitMetric);
            $missDelta = self::computeBucketDelta($bucket, $missMetric);

            if ($hitDelta === null || $missDelta === null || ($hitDelta + $missDelta) <= 0) {
                $data[] = null;
                continue;
            }

            $ratio = ($hitDelta / ($hitDelta + $missDelta)) * 100;
            $data[] = round(max(0.0, min(100.0, $ratio)), 2);
        }

        return [
            'id' => $id,
            'title' => $title,
            'type' => 'line',
            'unit' => $unit,
            'labels' => $labels,
            'datasets' => [
                self::buildDataset($title, $data, self::buildColorPalette(1)[0], true),
            ],
            'meta' => [
                'pmm_panel' => $pmmPanel,
                'pmm_source' => $pmmSource,
                'equivalent_metrics' => $hitMetric . ', ' . $missMetric,
            ],
        ];
    }

    private static function bucketizeRows(array $rows, int $bucketSeconds): array
    {
        if (empty($rows)) {
            return [];
        }

        $buckets = [];

        foreach ($rows as $row) {
            $timestamp = (int)($row['timestamp'] ?? 0);
            if ($timestamp <= 0) {
                continue;
            }

            $bucketTs = $timestamp - ($timestamp % $bucketSeconds);
            if (!isset($buckets[$bucketTs])) {
                $buckets[$bucketTs] = [
                    'bucket_ts' => $bucketTs,
                    'label' => $bucketSeconds >= 900 ? date('m-d H:i', $bucketTs) : date('H:i', $bucketTs),
                    'rows' => [],
                    'first' => [],
                    'last' => [],
                ];
            }

            $buckets[$bucketTs]['rows'][] = $row;
            foreach ($row as $key => $value) {
                if (!is_numeric($value)) {
                    continue;
                }

                if (!isset($buckets[$bucketTs]['first'][$key])) {
                    $buckets[$bucketTs]['first'][$key] = (float)$value;
                }
                $buckets[$bucketTs]['last'][$key] = (float)$value;
            }
        }

        ksort($buckets);
        return array_values($buckets);
    }

    private static function computeBucketDelta(array $bucket, string $metric): ?float
    {
        if (!isset($bucket['first'][$metric], $bucket['last'][$metric])) {
            return null;
        }

        $delta = (float)$bucket['last'][$metric] - (float)$bucket['first'][$metric];
        if ($delta < 0) {
            return null;
        }

        return $delta;
    }

    private static function computeBucketRate(array $bucket, string $metric, bool $microsecondsToMilliseconds = false): ?float
    {
        $delta = self::computeBucketDelta($bucket, $metric);
        if ($delta === null) {
            return null;
        }

        $rows = $bucket['rows'] ?? [];
        if (count($rows) < 2) {
            return 0.0;
        }

        $firstTs = (int)($rows[0]['timestamp'] ?? 0);
        $lastTs = (int)($rows[count($rows) - 1]['timestamp'] ?? 0);
        $seconds = max(1, $lastTs - $firstTs);

        $rate = $delta / $seconds;
        if ($microsecondsToMilliseconds) {
            $rate = $rate / 1000;
        }

        return round($rate, 4);
    }

    private static function buildDataset(string $label, array $data, array $color, bool $fill = false): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $color['border'],
            'backgroundColor' => $color['fill'],
            'fill' => $fill,
            'borderWidth' => 2,
            'tension' => 0.18,
            'pointRadius' => 0,
        ];
    }

    private static function buildColorPalette(int $count): array
    {
        $palette = [
            ['border' => 'rgba(37, 99, 235, 1)', 'fill' => 'rgba(37, 99, 235, 0.16)'],
            ['border' => 'rgba(5, 150, 105, 1)', 'fill' => 'rgba(5, 150, 105, 0.16)'],
            ['border' => 'rgba(220, 38, 38, 1)', 'fill' => 'rgba(220, 38, 38, 0.16)'],
            ['border' => 'rgba(245, 158, 11, 1)', 'fill' => 'rgba(245, 158, 11, 0.16)'],
            ['border' => 'rgba(124, 58, 237, 1)', 'fill' => 'rgba(124, 58, 237, 0.16)'],
            ['border' => 'rgba(8, 145, 178, 1)', 'fill' => 'rgba(8, 145, 178, 0.16)'],
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
    }

    private static function buildMetricCard(string $label, $value, string $source, string $pmmEquivalent): array
    {
        return [
            'label' => $label,
            'value' => self::formatMetricCardValue($label, $source, $value),
            'source' => $source,
            'pmm_equivalent' => $pmmEquivalent,
        ];
    }

    private static function buildStatusCard(string $label, string $value, string $note): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'note' => $note,
        ];
    }

    private static function getServer(int $idMysqlServer): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, name, display_name, ip, port
            FROM mysql_server
            WHERE id = " . (int)$idMysqlServer . "
            LIMIT 1";
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : ['id' => $idMysqlServer, 'display_name' => 'Server #' . $idMysqlServer];
    }

    private static function parseDateTimeInput($value, \DateTimeZone $timezone): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $normalized = str_replace('T', ' ', trim($value));
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $normalized, $timezone);

        return $date instanceof \DateTimeImmutable ? $date : null;
    }

    private static function getBucketSeconds(string $preset, string $mode): int
    {
        if ($mode === 'custom' || $preset === '24h') {
            return 900;
        }

        if ($preset === '6h') {
            return 300;
        }

        return 60;
    }

    private static function escapeSql(string $value): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
    }

    private static function formatAvailability($value): string
    {
        $normalized = null;

        if ($value !== null && $value !== '') {
            $normalized = is_numeric($value) ? (int)$value : null;
            if (!in_array($normalized, [0, 1, 2], true)) {
                $normalized = null;
            }
        }

        return match ($normalized) {
            1 => 'UP',
            2 => 'READ ONLY',
            0 => 'DOWN',
            default => 'N/A',
        };
    }

    private static function formatProductBanner(array $current): string
    {
        $version = trim((string)($current['version'] ?? ''));
        $comment = trim((string)($current['version_comment'] ?? ''));
        $banner = trim($comment . ' ' . $version);

        return $banner !== '' ? $banner : 'n/a';
    }

    private static function formatMetricCardValue(string $label, string $source, $value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }

        $haystack = strtolower($label . ' ' . $source);
        $isBytes = str_contains($haystack, 'bytes')
            || str_contains($haystack, 'buffer')
            || str_contains($haystack, 'memory')
            || str_contains($haystack, 'cache')
            || str_contains($haystack, 'swap')
            || str_contains($haystack, 'ram')
            || str_contains($haystack, 'wal')
            || str_contains($haystack, 'checkpoint age')
            || str_contains($haystack, 'log size');

        if ($isBytes && is_numeric($value)) {
            return self::formatBytes($value);
        }

        return self::formatScalar($value);
    }

    private static function computeHitRatio($reads, $requests): ?float
    {
        $reads = self::toFloat($reads);
        $requests = self::toFloat($requests);

        if ($reads === null || $requests === null || $requests <= 0) {
            return null;
        }

        return round(max(0.0, min(100.0, 100 - (($reads / $requests) * 100))), 2);
    }

    private static function formatLoadCard($one, $five, $fifteen): string
    {
        return trim(implode(' / ', array_filter([
            self::formatScalar($one),
            self::formatScalar($five),
            self::formatScalar($fifteen),
        ], static fn ($value) => $value !== 'n/a')));
    }

    private static function formatPercent($value): string
    {
        if (!is_numeric($value)) {
            return 'n/a';
        }

        return number_format((float)$value, 2) . '%';
    }

    private static function formatScalar($value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }

        if (is_float($value) || is_numeric($value)) {
            $value = (float)$value;
            if (fmod($value, 1.0) === 0.0) {
                return number_format($value, 0, '.', ' ');
            }

            return number_format($value, 2, '.', ' ');
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'n/a';
        }

        return trim((string)$value);
    }

    private static function formatBytes($value): string
    {
        if (!is_numeric($value)) {
            return 'n/a';
        }

        return EngineMemoryBreakdown::formatBytes((float)$value);
    }

    private static function toFloat($value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }

    private static function parseJsonObject($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function parseJsonList($value): array
    {
        $decoded = self::parseJsonObject($value);

        if (array_is_list($decoded)) {
            return $decoded;
        }

        if (isset($decoded['rows']) && is_array($decoded['rows'])) {
            return $decoded['rows'];
        }

        if (isset($decoded['items']) && is_array($decoded['items'])) {
            return $decoded['items'];
        }

        return $decoded === [] ? [] : [$decoded];
    }

    private static function rowsFromAssoc(array $data): array
    {
        $rows = [];
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $rows[] = [(string)$key, self::formatScalar($value)];
        }

        return $rows;
    }

    private static function buildDiskRows($value): array
    {
        $rows = self::parseJsonList($value);
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                (string)($row['Filesystem'] ?? $row['filesystem'] ?? ''),
                (string)($row['Mounted'] ?? $row['Mounted on'] ?? $row['mountpoint'] ?? ''),
                (string)($row['Used'] ?? ''),
                (string)($row['Available'] ?? ''),
                (string)($row['Use%'] ?? $row['Use %'] ?? ''),
            ];
        }

        return $result;
    }

    private static function buildProcessRows($value): array
    {
        $rows = self::parseJsonObject($value);
        if (empty($rows)) {
            return [];
        }

        arsort($rows);
        $result = [];
        foreach (array_slice($rows, 0, 12, true) as $process => $kb) {
            $result[] = [(string)$process, self::formatBytes(((float)$kb) * 1024)];
        }

        return $result;
    }

    private static function indexListByVariableName(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = (string)($row['Variable_name'] ?? $row['variable_name'] ?? $row['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $value = $row['Value'] ?? $row['variable_value'] ?? $row['value'] ?? null;
            $result[$name] = $value;
        }

        ksort($result);
        return $result;
    }

    private static function buildProxySqlBackendRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                self::formatScalar($row['hostgroup_id'] ?? $row['hostgroup'] ?? null),
                self::formatScalar($row['hostname'] ?? null),
                self::formatScalar($row['port'] ?? null),
                self::formatScalar($row['status'] ?? null),
                self::formatScalar($row['weight'] ?? null),
                self::formatScalar($row['compression'] ?? null),
                self::formatScalar($row['max_connections'] ?? $row['ConnUsed'] ?? null),
            ];
        }

        return $result;
    }

    private static function buildProxySqlPeerRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                self::formatScalar($row['hostname'] ?? null),
                self::formatScalar($row['port'] ?? null),
                self::formatScalar($row['status'] ?? null),
                self::formatScalar($row['comment'] ?? null),
            ];
        }

        return $result;
    }

    private static function buildProxySqlRuleRows(array $rows): array
    {
        $result = [];
        foreach (array_slice($rows, 0, 30) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                self::formatScalar($row['rule_id'] ?? null),
                self::formatScalar($row['active'] ?? null),
                self::formatScalar($row['username'] ?? null),
                self::formatScalar($row['schemaname'] ?? null),
                self::formatScalar($row['destination_hostgroup'] ?? null),
                self::formatScalar($row['apply'] ?? null),
            ];
        }

        return $result;
    }

    private static function buildProxySqlUserRows(array $rows): array
    {
        $result = [];
        foreach (array_slice($rows, 0, 30) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                self::formatScalar($row['username'] ?? null),
                self::formatScalar($row['default_hostgroup'] ?? null),
                self::formatScalar($row['active'] ?? null),
                self::formatScalar($row['transaction_persistent'] ?? null),
            ];
        }

        return $result;
    }
}
