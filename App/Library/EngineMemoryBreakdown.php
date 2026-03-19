<?php

namespace App\Library;

use Glial\Sgbd\Sgbd;

class EngineMemoryBreakdown
{
    private const PROCESS_MEMORY_TOP_LIMIT = 20;
    private const RANGE_PRESETS = [
        '1h' => 3600,
        '6h' => 21600,
        '24h' => 86400,
    ];

    private const ENGINE_ORDER = [
        'InnoDB',
        'RocksDB',
        'Aria',
        'MyISAM',
        'ColumnStore',
        'Spider',
        'TokuDB',
        'MEMORY',
        'TempTable',
        'Performance Schema',
        'Other',
    ];

    public static function build(int $idMysqlServer, array $options = []): array
    {
        $definitions = self::getMetricDefinitions();
        $keys = self::buildExtractionKeys($definitions);

        $raw = Extraction2::display($keys, [$idMysqlServer]);
        $row = [];

        if (is_array($raw) && !empty($raw)) {
            $candidate = reset($raw);
            if (is_array($candidate)) {
                $row = $candidate;
            }
        }

        $engines = self::parseJsonList($row['engines'] ?? null);
        $plugins = self::parseJsonList($row['plugins'] ?? null);
        $performanceSchemaMemory = self::parseJsonList($row['memory_summary_global_by_event_name'] ?? null);
        $memoryDetail = self::parseJsonObject($row['memory_detail_kb'] ?? null);

        $engineSupport = self::buildEngineSupportMap($engines);
        $enginePlugins = self::buildEnginePluginMap($plugins);
        $performanceSchemaGroups = self::aggregatePerformanceSchemaMemory($performanceSchemaMemory);
        $groupedMetrics = self::groupMetricsByEngine($definitions, $row);

        $summary = self::buildSummary($row, $engineSupport, $performanceSchemaGroups);
        $sections = self::buildSections($row, $engineSupport, $enginePlugins, $groupedMetrics, $performanceSchemaGroups);
        $recap = self::buildRecap($row, $memoryDetail, $performanceSchemaGroups);
        $range = self::normalizeProcessMemoryRange($options);
        $processMemoryChart = self::buildProcessMemoryChart($idMysqlServer, $range);
        $processMemoryChart['memory_total_kb'] = self::toFloat($row['memory_total'] ?? null) / 1024;

        return [
            'id_mysql_server' => $idMysqlServer,
            'mysql_available' => (string)($row['mysql_available'] ?? '0'),
            'summary' => $summary,
            'sections' => $sections,
            'recap' => $recap,
            'process_memory_chart' => $processMemoryChart,
            'process_memory_range' => $range,
        ];
    }

    public static function normalizeProcessMemoryRange(array $options = []): array
    {
        $timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        $now = new \DateTimeImmutable('now', $timezone);

        $preset = (string)($options['range'] ?? '6h');
        $rangeMode = (string)($options['range_mode'] ?? 'preset');
        if (!isset(self::RANGE_PRESETS[$preset])) {
            $preset = '6h';
        }

        $mode = 'preset';
        $start = $now->sub(new \DateInterval('PT'.self::RANGE_PRESETS[$preset].'S'));
        $end = $now;

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
            'mode' => $mode,
            'preset' => $preset,
            'range_mode' => $mode,
            'start' => $start,
            'end' => $end,
            'start_value' => $start->format('Y-m-d\TH:i'),
            'end_value' => $end->format('Y-m-d\TH:i'),
            'title' => $mode === 'custom'
                ? 'Process memory (top 20 + Others, custom range)'
                : 'Process memory (top 20 + Others, '.$preset.')',
        ];
    }

    public static function aggregateProcessMemorySeries(array $snapshots, int $topLimit = self::PROCESS_MEMORY_TOP_LIMIT): array
    {
        $averages = [];

        foreach ($snapshots as $snapshot) {
            foreach (($snapshot['processes'] ?? []) as $processName => $valueKb) {
                if (!isset($averages[$processName])) {
                    $averages[$processName] = [
                        'sum' => 0.0,
                        'count' => 0,
                    ];
                }

                $averages[$processName]['sum'] += (float) $valueKb;
                $averages[$processName]['count']++;
            }
        }

        $means = [];
        foreach ($averages as $processName => $stats) {
            if ($stats['count'] === 0) {
                continue;
            }

            $means[$processName] = $stats['sum'] / $stats['count'];
        }

        arsort($means);
        $selected = array_slice(array_keys($means), 0, $topLimit);
        $labels = [];
        $seriesData = [];

        foreach ($selected as $processName) {
            $seriesData[$processName] = [];
        }
        $seriesData['Others'] = [];

        foreach ($snapshots as $snapshot) {
            $labels[] = $snapshot['label'] ?? '';
            $processes = $snapshot['processes'] ?? [];

            foreach ($selected as $processName) {
                $seriesData[$processName][] = isset($processes[$processName]) ? (float) $processes[$processName] : 0.0;
            }

            $others = 0.0;
            foreach ($processes as $processName => $valueKb) {
                if (in_array($processName, $selected, true)) {
                    continue;
                }

                $others += (float) $valueKb;
            }
            $seriesData['Others'][] = $others;
        }

        $datasets = [];
        $colorCount = max(count($selected), 1);
        $index = 0;

        foreach ($selected as $processName) {
            $datasets[] = [
                'label' => $processName,
                'data' => $seriesData[$processName],
                'avg_kb' => $means[$processName] ?? 0.0,
                'backgroundColor' => self::buildSeriesColor($index, $colorCount, 0.45),
                'borderColor' => self::buildSeriesColor($index, $colorCount, 0.98),
            ];
            $index++;
        }

        $hasOthers = false;
        foreach ($seriesData['Others'] as $value) {
            if ($value > 0) {
                $hasOthers = true;
                break;
            }
        }

        if ($hasOthers) {
            $datasets[] = [
                'label' => 'Others',
                'data' => $seriesData['Others'],
                'avg_kb' => array_sum($seriesData['Others']) / max(count($seriesData['Others']), 1),
                'backgroundColor' => 'rgba(140, 140, 140, 0.45)',
                'borderColor' => 'rgba(90, 90, 90, 0.98)',
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'top_processes' => $selected,
        ];
    }

    public static function normalizePerformanceSchemaEventName(string $eventName): string
    {
        return str_replace('\\/', '/', $eventName);
    }

    public static function classifyPerformanceSchemaEvent(string $eventName): string
    {
        $eventName = strtolower(self::normalizePerformanceSchemaEventName($eventName));

        $map = [
            'memory/innodb/' => 'InnoDB',
            'memory/rocksdb/' => 'RocksDB',
            'memory/aria/' => 'Aria',
            'memory/myisam/' => 'MyISAM',
            'memory/columnstore/' => 'ColumnStore',
            'memory/spider/' => 'Spider',
            'memory/tokudb/' => 'TokuDB',
            'memory/memory/' => 'MEMORY',
            'memory/temptable/' => 'TempTable',
            'memory/performance_schema/' => 'Performance Schema',
        ];

        foreach ($map as $prefix => $label) {
            if (strpos($eventName, $prefix) === 0) {
                return $label;
            }
        }

        return 'Other';
    }

    public static function aggregatePerformanceSchemaMemory(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $eventName = (string)($row['event_name'] ?? '');
            if ($eventName === '') {
                continue;
            }

            $engine = self::classifyPerformanceSchemaEvent($eventName);
            $bytes = self::toFloat($row['high_number_of_bytes_used'] ?? $row['sum_number_of_bytes_alloc'] ?? null);

            if (!isset($groups[$engine])) {
                $groups[$engine] = [
                    'bytes' => 0.0,
                    'events' => [],
                ];
            }

            $groups[$engine]['bytes'] += $bytes;
            $groups[$engine]['events'][] = [
                'event_name' => self::normalizePerformanceSchemaEventName($eventName),
                'bytes' => $bytes,
            ];
        }

        foreach ($groups as &$group) {
            usort($group['events'], static function (array $left, array $right): int {
                return $right['bytes'] <=> $left['bytes'];
            });
        }
        unset($group);

        return $groups;
    }

    private static function buildSummary(array $row, array $engineSupport, array $performanceSchemaGroups): array
    {
        $engineCount = 0;
        foreach ($engineSupport as $engine) {
            if (!empty($engine['enabled'])) {
                $engineCount++;
            }
        }

        $performanceSchemaTotal = 0.0;
        foreach ($performanceSchemaGroups as $group) {
            $performanceSchemaTotal += (float)($group['bytes'] ?? 0);
        }

        return [
            'MySQL available' => self::formatBoolean($row['mysql_available'] ?? null),
            'Default storage engine' => self::formatScalar($row['default_storage_engine'] ?? null),
            'Physical memory' => self::formatBytes($row['memory_total'] ?? null),
            'Max connections' => self::formatScalar($row['max_connections'] ?? null),
            'Max used connections' => self::formatScalar($row['max_used_connections'] ?? null),
            'MariaDB internal memory_used' => self::formatBytes($row['memory_used'] ?? null),
            'tmp_table_size' => self::formatBytes($row['tmp_table_size'] ?? null),
            'max_heap_table_size' => self::formatBytes($row['max_heap_table_size'] ?? null),
            'Known engines enabled' => (string)$engineCount,
            'Performance Schema tracked memory' => self::formatBytes($performanceSchemaTotal),
        ];
    }

    private static function buildRecap(array $row, array $memoryDetail, array $performanceSchemaGroups): array
    {
        $recap = [];
        $mariadbdRssBytes = null;

        if (isset($memoryDetail['mariadbd']) && is_numeric($memoryDetail['mariadbd'])) {
            $mariadbdRssBytes = (float)$memoryDetail['mariadbd'] * 1024;
            $recap[] = self::makeRecapRow(
                'mariadbd RSS (system)',
                $mariadbdRssBytes,
                'measured',
                'ssh_stats::memory_detail_kb',
                'Resident memory seen by the operating system.'
            );
        }

        $memoryUsed = self::toFloat($row['memory_used'] ?? null);
        if ($memoryUsed > 0) {
            $recap[] = self::makeRecapRow(
                'MariaDB memory_used',
                $memoryUsed,
                'measured',
                'status::memory_used',
                'MariaDB allocator memory. Official docs note that global allocations are included from MariaDB 10.6.16.'
            );
        }

        $psTotal = 0.0;
        foreach ($performanceSchemaGroups as $group) {
            $psTotal += (float)($group['bytes'] ?? 0);
        }
        if ($psTotal > 0) {
            $recap[] = self::makeRecapRow(
                'Performance Schema tracked memory',
                $psTotal,
                'measured',
                'performance_schema::memory_summary_global_by_event_name',
                'Aggregated HIGH_NUMBER_OF_BYTES_USED across tracked memory events.'
            );
        }

        $innodbActual = self::toFloat($row['innodb_mem_total'] ?? null);
        if ($innodbActual > 0) {
            $recap[] = self::makeRecapRow(
                'InnoDB total memory',
                $innodbActual,
                'measured',
                'status::innodb_mem_total',
                'Best native InnoDB total currently collected.'
            );
        } else {
            $innodbConfigured = self::toFloat($row['innodb_buffer_pool_size'] ?? null)
                + self::toFloat($row['innodb_log_buffer_size'] ?? null)
                + self::toFloat($row['innodb_additional_mem_pool_size'] ?? null);

            if ($innodbConfigured > 0) {
                $recap[] = self::makeRecapRow(
                    'InnoDB configured shared memory',
                    $innodbConfigured,
                    'estimated',
                    'variables::innodb_*',
                    'Buffer pool + log buffer + additional pool. This is a configured footprint, not live RSS.'
                );
            }
        }

        $ariaPageCache = self::toFloat($row['aria_pagecache_buffer_size'] ?? null);
        if ($ariaPageCache > 0) {
            $recap[] = self::makeRecapRow(
                'Aria pagecache',
                $ariaPageCache,
                'estimated',
                'variables::aria_pagecache_buffer_size',
                'Configured Aria page cache.'
            );
        }

        $myisamKeyBuffer = self::toFloat($row['key_buffer_size'] ?? null);
        if ($myisamKeyBuffer > 0) {
            $recap[] = self::makeRecapRow(
                'MyISAM key buffer',
                $myisamKeyBuffer,
                'estimated',
                'variables::key_buffer_size',
                'Configured MyISAM key cache.'
            );
        }

        $rocksConfigured = self::toFloat($row['rocksdb_block_cache_size'] ?? null)
            + self::toFloat($row['rocksdb_db_write_buffer_size'] ?? null);
        if ($rocksConfigured > 0) {
            $recap[] = self::makeRecapRow(
                'RocksDB configured caches',
                $rocksConfigured,
                'configured cap',
                'variables::rocksdb_block_cache_size + rocksdb_db_write_buffer_size',
                'This is not live usage. MariaDB docs recommend SHOW ENGINE ROCKSDB STATUS and INFORMATION_SCHEMA.ROCKSDB_DBSTATS for actual cache usage.'
            );
        }

        $columnStoreConfigured = self::toFloat($row['columnstore_um_mem_limit'] ?? null)
            + self::toFloat($row['columnstore_pm_mem_limit'] ?? null);
        if ($columnStoreConfigured > 0) {
            $recap[] = self::makeRecapRow(
                'ColumnStore memory limits',
                $columnStoreConfigured,
                'configured cap',
                'variables::columnstore_um_mem_limit + columnstore_pm_mem_limit',
                'Configured limits, not measured runtime usage.'
            );
        }

        $tempCaps = self::toFloat($row['tmp_table_size'] ?? null) + self::toFloat($row['max_heap_table_size'] ?? null);
        if ($tempCaps > 0) {
            $recap[] = self::makeRecapRow(
                'Temporary table memory caps',
                $tempCaps,
                'configured cap',
                'variables::tmp_table_size + max_heap_table_size',
                'These are ceilings for MEMORY/TempTable allocations, not a shared allocation.'
            );
        }

        if ($mariadbdRssBytes !== null && $memoryUsed > 0) {
            $rssGap = max(0.0, $mariadbdRssBytes - $memoryUsed);
            $recap[] = self::makeRecapRow(
                'Unattributed RSS vs memory_used',
                $rssGap,
                'deduced',
                'system RSS - status::memory_used',
                'Approximate gap: allocator overhead, code, plugin memory not counted the same way, mmap, and non-MariaDB allocations inside the process.'
            );
        }

        $knownEstimated = 0.0;
        foreach ($recap as $rowRecap) {
            if (in_array($rowRecap['nature'], ['estimated', 'configured cap'], true)) {
                $knownEstimated += (float) $rowRecap['bytes'];
            }
        }

        if ($memoryUsed > 0 && $knownEstimated > 0) {
            $internalGap = $memoryUsed - $knownEstimated;
            if ($internalGap < 0) {
                $internalGap = 0.0;
            }

            $recap[] = self::makeRecapRow(
                'Internal memory still not mapped to an engine',
                $internalGap,
                'deduced',
                'status::memory_used - known engine estimates',
                'Residual MariaDB memory not covered by the engine-specific metrics collected here.'
            );
        }

        return $recap;
    }

    private static function buildProcessMemoryChart(int $idMysqlServer, array $range): array
    {
        $detailMetricId = self::getMetricId('ssh_stats', 'memory_detail_kb');
        $memoryUsedMetricId = self::getMetricId('ssh_stats', 'memory_used');

        if ($detailMetricId === null) {
            return [
                'title' => $range['title'] ?? 'Process memory',
                'labels' => [],
                'datasets' => [],
                'memory_used_kb' => [],
            ];
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $detailMetricId = (int) $detailMetricId;
        $idMysqlServer = (int) $idMysqlServer;

        $sql = "SELECT date, value
                FROM ts_value_general_json
                WHERE id_mysql_server = ".$idMysqlServer."
                AND id_ts_variable = ".$detailMetricId."
                AND date BETWEEN '".$db->sql_real_escape_string($range['start']->format('Y-m-d H:i:s'))."'
                AND '".$db->sql_real_escape_string($range['end']->format('Y-m-d H:i:s'))."'
                ORDER BY date ASC";

        $res = $db->sql_query($sql);
        $snapshots = [];
        $memoryUsedByLabel = [];

        if ($memoryUsedMetricId !== null) {
            $memoryUsedSql = "SELECT date, value
                FROM ts_value_general_int
                WHERE id_mysql_server = ".$idMysqlServer."
                AND id_ts_variable = ".((int) $memoryUsedMetricId)."
                AND date BETWEEN '".$db->sql_real_escape_string($range['start']->format('Y-m-d H:i:s'))."'
                AND '".$db->sql_real_escape_string($range['end']->format('Y-m-d H:i:s'))."'
                ORDER BY date ASC";

            $memoryUsedRes = $db->sql_query($memoryUsedSql);

            while ($memoryUsedRow = $db->sql_fetch_array($memoryUsedRes, MYSQLI_ASSOC)) {
                $label = substr((string) ($memoryUsedRow['date'] ?? ''), 11, 8);
                $memoryUsedByLabel[$label] = self::toFloat($memoryUsedRow['value'] ?? null) / 1024;
            }
        }

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $processes = self::parseJsonObject($row['value'] ?? null);
            if (empty($processes)) {
                continue;
            }

            $snapshots[] = [
                'label' => substr((string) ($row['date'] ?? ''), 11, 8),
                'processes' => $processes,
            ];
        }

        $chart = self::aggregateProcessMemorySeries($snapshots, self::PROCESS_MEMORY_TOP_LIMIT);
        $chart['title'] = $range['title'] ?? 'Process memory';
        $chart['memory_used_kb'] = [];

        foreach ($chart['labels'] as $label) {
            $chart['memory_used_kb'][] = $memoryUsedByLabel[$label] ?? null;
        }

        return $chart;
    }

    private static function buildSections(
        array $row,
        array $engineSupport,
        array $enginePlugins,
        array $groupedMetrics,
        array $performanceSchemaGroups
    ): array {
        $sections = [];

        foreach (self::ENGINE_ORDER as $engineName) {
            $metrics = $groupedMetrics[$engineName] ?? [];
            $ps = $performanceSchemaGroups[$engineName] ?? ['bytes' => 0.0, 'events' => []];

            if (empty($metrics) && empty($ps['events']) && empty($engineSupport[$engineName]) && empty($enginePlugins[$engineName])) {
                continue;
            }

            $sections[] = [
                'title' => $engineName,
                'meta' => [
                    'supported' => $engineSupport[$engineName]['support'] ?? 'n/a',
                    'enabled' => $engineSupport[$engineName]['enabled'] ?? false,
                    'plugin_status' => $enginePlugins[$engineName]['status'] ?? 'n/a',
                    'plugin_version' => $enginePlugins[$engineName]['version'] ?? 'n/a',
                    'performance_schema_memory' => self::formatBytes($ps['bytes'] ?? null),
                ],
                'metrics' => self::sortMetrics($metrics),
                'performance_schema_events' => array_slice($ps['events'], 0, 20),
                'notes' => self::buildEngineNotes($engineName, $row, $metrics, $ps),
            ];
        }

        return $sections;
    }

    private static function buildEngineNotes(string $engineName, array $row, array $metrics, array $performanceSchemaGroup): array
    {
        $notes = [];

        if (empty($metrics) && empty($performanceSchemaGroup['events'])) {
            $notes[] = 'No dedicated metric collected for this engine on this server.';
        }

        if ($engineName === 'Spider') {
            $notes[] = 'Spider exposes mostly connectivity and routing settings in the current collector.';
            $notes[] = 'MariaDB also provides INFORMATION_SCHEMA.SPIDER_ALLOC_MEM for Spider memory usage, but it is not collected by PmaControl yet.';
        }

        if ($engineName === 'MEMORY') {
            $notes[] = 'The MEMORY engine uses session and table memory; max_heap_table_size is the main hard cap exposed here.';
        }

        if ($engineName === 'TempTable') {
            $notes[] = 'Temporary table memory is bounded by tmp_table_size and max_heap_table_size, then spills to disk.';
        }

        if ($engineName === 'InnoDB' && isset($row['innodb_mem_total'])) {
            $notes[] = 'innodb_mem_total is the broadest native InnoDB memory counter available in the collected metrics.';
        }

        if ($engineName === 'RocksDB') {
            $notes[] = 'The current collector only has configuration-oriented RocksDB memory knobs.';
            $notes[] = 'MariaDB documents SHOW ENGINE ROCKSDB STATUS, INFORMATION_SCHEMA.ROCKSDB_DBSTATS and RocksDB perf context for deeper live memory analysis.';
            $notes[] = 'ROCKSDB_PERF_CONTEXT and ROCKSDB_PERF_CONTEXT_GLOBAL require rocksdb_perf_context_level to be enabled.';
        }

        return $notes;
    }

    private static function sortMetrics(array $metrics): array
    {
        usort($metrics, static function (array $left, array $right): int {
            return strcmp($left['name'], $right['name']);
        });

        return $metrics;
    }

    private static function getMetricDefinitions(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT `from`, name
                FROM ts_variable
                WHERE (
                    name REGEXP '^(innodb_|rocksdb_|aria_|myisam_|key_|spider_|columnstore_|tokudb_|tmp_memory_table_size$|tmp_table_size$|max_heap_table_size$)'
                    OR name IN ('memory_summary_global_by_event_name', 'engines', 'plugins', 'memory_total', 'memory_used', 'memory_detail_kb', 'mysql_available', 'default_storage_engine', 'max_connections', 'max_used_connections', 'query_cache_size', 'sort_buffer_size', 'join_buffer_size', 'read_buffer_size', 'read_rnd_buffer_size', 'thread_stack', 'binlog_cache_size')
                )
                AND `from` IN ('variables', 'status', 'innodb_metrics', 'information_schema', 'performance_schema', 'mysql_server', 'ssh_stats')
                ORDER BY `from`, name";

        return $db->sql_fetch_all($sql);
    }

    private static function getMetricId(string $from, string $name): ?int
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $from = $db->sql_real_escape_string($from);
        $name = $db->sql_real_escape_string($name);

        $sql = "SELECT id
                FROM ts_variable
                WHERE `from` = '".$from."'
                AND name = '".$name."'
                LIMIT 1";

        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($row['id'])) {
            return null;
        }

        return (int) $row['id'];
    }

    private static function buildExtractionKeys(array $definitions): array
    {
        $keys = [];

        foreach ($definitions as $definition) {
            if (empty($definition['from']) || empty($definition['name'])) {
                continue;
            }

            $keys[] = $definition['from'].'::'.$definition['name'];
        }

        return array_values(array_unique($keys));
    }

    private static function buildEngineSupportMap(array $engines): array
    {
        $result = [];

        foreach ($engines as $engine) {
            if (!is_array($engine) || empty($engine['ENGINE'])) {
                continue;
            }

            $name = self::normalizeEngineName((string)$engine['ENGINE']);
            $support = strtoupper((string)($engine['SUPPORT'] ?? ''));

            $result[$name] = [
                'support' => $support === '' ? 'n/a' : $support,
                'enabled' => !in_array($support, ['NO', 'DISABLED'], true),
                'comment' => (string)($engine['COMMENT'] ?? ''),
            ];
        }

        return $result;
    }

    private static function buildEnginePluginMap(array $plugins): array
    {
        $result = [];

        foreach ($plugins as $plugin) {
            if (!is_array($plugin)) {
                continue;
            }

            if (strtoupper((string)($plugin['PLUGIN_TYPE'] ?? '')) !== 'STORAGE ENGINE') {
                continue;
            }

            $name = self::normalizeEngineName((string)($plugin['PLUGIN_NAME'] ?? ''));
            if ($name === '') {
                continue;
            }

            $result[$name] = [
                'status' => (string)($plugin['PLUGIN_STATUS'] ?? 'n/a'),
                'version' => (string)($plugin['PLUGIN_VERSION'] ?? 'n/a'),
            ];
        }

        return $result;
    }

    private static function groupMetricsByEngine(array $definitions, array $row): array
    {
        $grouped = [];

        foreach ($definitions as $definition) {
            $source = (string)($definition['from'] ?? '');
            $name = (string)($definition['name'] ?? '');

            if ($name === '' || in_array($name, ['engines', 'plugins', 'memory_summary_global_by_event_name'], true)) {
                continue;
            }

            if (!array_key_exists($name, $row)) {
                continue;
            }

            $engine = self::mapMetricToEngine($name, $source);
            $rawValue = self::extractMetricValue($row[$name]);

            $grouped[$engine][] = [
                'name' => $name,
                'source' => $source,
                'raw_value' => $rawValue,
                'raw_value_display' => self::formatRawValue($rawValue),
                'display_value' => self::formatMetricValue($name, $rawValue),
            ];
        }

        return $grouped;
    }

    private static function mapMetricToEngine(string $name, string $source): string
    {
        $name = strtolower($name);

        if (strpos($name, 'innodb_') === 0 || strpos($name, 'buffer_pool_') === 0) {
            return 'InnoDB';
        }

        if (strpos($name, 'rocksdb_') === 0) {
            return 'RocksDB';
        }

        if (strpos($name, 'aria_') === 0) {
            return 'Aria';
        }

        if (strpos($name, 'myisam_') === 0 || strpos($name, 'key_') === 0) {
            return 'MyISAM';
        }

        if (strpos($name, 'columnstore_') === 0) {
            return 'ColumnStore';
        }

        if (strpos($name, 'spider_') === 0) {
            return 'Spider';
        }

        if (strpos($name, 'tokudb_') === 0) {
            return 'TokuDB';
        }

        if (in_array($name, ['max_heap_table_size'], true)) {
            return 'MEMORY';
        }

        if (in_array($name, ['tmp_table_size', 'tmp_memory_table_size'], true)) {
            return 'TempTable';
        }

        if ($source === 'performance_schema') {
            return 'Performance Schema';
        }

        return 'Other';
    }

    private static function extractMetricValue($value)
    {
        if (is_array($value) && array_key_exists('count', $value)) {
            return $value['count'];
        }

        return $value;
    }

    private static function formatMetricValue(string $name, $value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }

        if ($name === 'memory_detail_kb' && is_array($value)) {
            $mysqlProcessKb = self::extractMysqlProcessKb($value);

            if ($mysqlProcessKb !== null) {
                return self::formatBytes($mysqlProcessKb * 1024);
            }
        }

        if (is_array($value)) {
            return self::formatRawValue($value);
        }

        if (self::looksLikeBytesMetric($name) && is_numeric($value)) {
            return self::formatBytes($value);
        }

        if (self::looksLikeBooleanMetric($name)) {
            return self::formatBoolean($value);
        }

        if (is_numeric($value)) {
            return (string)$value;
        }

        return (string)$value;
    }

    private static function formatRawValue($value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }

        if (is_array($value)) {
            $mysqlProcessKb = self::extractMysqlProcessKb($value);
            if ($mysqlProcessKb !== null) {
                return (string) $mysqlProcessKb;
            }

            $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $json === false ? 'n/a' : $json;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string)$value;
    }

    private static function looksLikeBytesMetric(string $name): bool
    {
        $patterns = ['_size', '_bytes', '_memory', '_buffer', '_cache', '_limit', '_chunk', 'page_size'];

        foreach ($patterns as $pattern) {
            if (strpos($name, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function looksLikeBooleanMetric(string $name): bool
    {
        $patterns = ['have_', '_enable', '_enabled', '_use_', '_write_', '_encrypt_', '_checksum', '_support_xa', '_internal_xa'];

        foreach ($patterns as $pattern) {
            if (strpos($name, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function parseJsonList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string)$value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function parseJsonObject($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string)$value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function normalizeEngineName(string $engineName): string
    {
        $normalized = strtoupper(trim($engineName));

        return match ($normalized) {
            'INNODB' => 'InnoDB',
            'ROCKSDB' => 'RocksDB',
            'ARIA' => 'Aria',
            'MYISAM', 'MRG_MYISAM' => 'MyISAM',
            'COLUMNSTORE' => 'ColumnStore',
            'SPIDER' => 'Spider',
            'TOKUDB' => 'TokuDB',
            'MEMORY' => 'MEMORY',
            'PERFORMANCE_SCHEMA' => 'Performance Schema',
            default => trim($engineName),
        };
    }

    public static function formatBytes($bytes): string
    {
        if (!is_numeric($bytes)) {
            return 'n/a';
        }

        $bytes = (float)$bytes;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $power = (int)floor(log($bytes, 1024));
        $power = max(0, min($power, count($units) - 1));

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }

    private static function formatScalar($value): string
    {
        if ($value === null || $value === '') {
            return 'n/a';
        }

        return (string)$value;
    }

    private static function formatBoolean($value): string
    {
        $value = strtoupper((string)$value);

        if (in_array($value, ['1', 'ON', 'YES', 'TRUE', 'ACTIVE', 'DEFAULT'], true)) {
            return 'Yes';
        }

        if (in_array($value, ['0', 'OFF', 'NO', 'FALSE', 'DISABLED'], true)) {
            return 'No';
        }

        return self::formatScalar($value);
    }

    private static function toFloat($value): float
    {
        return is_numeric($value) ? (float)$value : 0.0;
    }

    private static function makeRecapRow(string $component, float $bytes, string $nature, string $source, string $note): array
    {
        return [
            'component' => $component,
            'bytes' => $bytes,
            'display_bytes' => self::formatBytes($bytes),
            'nature' => $nature,
            'source' => $source,
            'note' => $note,
        ];
    }

    private static function extractMysqlProcessKb(array $memoryDetail): ?float
    {
        foreach (['mariadbd', 'mysqld', 'mysql'] as $processName) {
            if (isset($memoryDetail[$processName]) && is_numeric($memoryDetail[$processName])) {
                return (float) $memoryDetail[$processName];
            }
        }

        return null;
    }

    private static function buildSeriesColor(int $index, int $count, float $alpha): string
    {
        $hue = (int) round(($index / max($count, 1)) * 260);
        $lightness = 38 + (int) round(($index / max($count, 1)) * 30);

        return 'hsla('.$hue.', 65%, '.$lightness.'%, '.$alpha.')';
    }

    private static function parseDateTimeInput($value, \DateTimeZone $timezone): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value, $timezone);
            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }
        }

        return null;
    }
}
