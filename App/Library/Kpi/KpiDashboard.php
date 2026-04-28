<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class KpiDashboard
{
    public const DEFAULT_WINDOW_HOURS = 24;
    public const MAX_WINDOW_DAYS = 7;
    public const TOP_LIMIT = 10;
    public const EVENT_LIMIT = 8;
    public const REFRESH_INTERVAL_MS = 10000;

    private const CONNECTION_SLOT = 'kpi_dashboard';

    private const METRICS = [
        'aspirateur_attempts_total' => ['label' => 'Attempts', 'unit' => 'count', 'color' => '#2563eb'],
        'aspirateur_failures_total' => ['label' => 'Failures', 'unit' => 'count', 'color' => '#dc2626'],
        'aspirateur_readonly_total' => ['label' => 'Read only', 'unit' => 'count', 'color' => '#7c3aed'],
        'state_transitions_total' => ['label' => 'Transitions', 'unit' => 'count', 'color' => '#d97706'],
        'aspirateur_p50_ping_ms' => ['label' => 'p50', 'unit' => 'ms', 'color' => '#0891b2'],
        'aspirateur_p95_ping_ms' => ['label' => 'p95', 'unit' => 'ms', 'color' => '#0f766e'],
        'aspirateur_p99_ping_ms' => ['label' => 'p99', 'unit' => 'ms', 'color' => '#be123c'],
        'worker_busy_pct' => ['label' => 'Busy', 'unit' => '%', 'color' => '#16a34a'],
        'worker_queue_depth_max' => ['label' => 'Queue max', 'unit' => 'count', 'color' => '#9333ea'],
        'worker_stuck_count' => ['label' => 'Stuck', 'unit' => 'count', 'color' => '#ea580c'],
        'daemon_late_count' => ['label' => 'Late daemons', 'unit' => 'count', 'color' => '#f59e0b'],
        'event_log_open_count' => ['label' => 'Open events', 'unit' => 'count', 'color' => '#64748b'],
    ];

    private const CHARTS = [
        [
            'key' => 'attempts',
            'canvas_id' => 'kpi-dashboard-chart-attempts',
            'title' => 'Aspirateur attempts',
            'metrics' => ['aspirateur_attempts_total', 'aspirateur_failures_total'],
        ],
        [
            'key' => 'readonly',
            'canvas_id' => 'kpi-dashboard-chart-readonly',
            'title' => 'Read only and transitions',
            'metrics' => ['aspirateur_readonly_total', 'state_transitions_total'],
        ],
        [
            'key' => 'ping',
            'canvas_id' => 'kpi-dashboard-chart-ping',
            'title' => 'Ping percentiles',
            'metrics' => ['aspirateur_p50_ping_ms', 'aspirateur_p95_ping_ms', 'aspirateur_p99_ping_ms'],
        ],
        [
            'key' => 'worker',
            'canvas_id' => 'kpi-dashboard-chart-worker',
            'title' => 'Worker load',
            'metrics' => ['worker_busy_pct', 'worker_queue_depth_max', 'worker_stuck_count'],
        ],
        [
            'key' => 'daemon',
            'canvas_id' => 'kpi-dashboard-chart-daemon',
            'title' => 'Daemon late',
            'metrics' => ['daemon_late_count'],
        ],
        [
            'key' => 'events',
            'canvas_id' => 'kpi-dashboard-chart-events',
            'title' => 'Open events',
            'metrics' => ['event_log_open_count'],
        ],
    ];

    private static array $tableExistsCache = [];
    private static array $columnExistsCache = [];

    public static function buildInitialPayload(array $query = [], array $options = []): array
    {
        $realtime = self::buildRealtimePayload($options);
        $series = self::buildSeriesPayload($query, $options);
        $top = self::buildTopPayload($options);

        return [
            'generated_at' => self::dateTime(self::dateTimeObject($options['now'] ?? 'now')),
            'refresh_interval_ms' => self::REFRESH_INTERVAL_MS,
            'realtime' => $realtime,
            'series' => $series,
            'top' => $top,
            'warnings' => self::uniqueWarnings(array_merge(
                $realtime['warnings'] ?? [],
                $series['warnings'] ?? [],
                $top['warnings'] ?? []
            )),
        ];
    }

    public static function buildRealtimePayload(array $options = []): array
    {
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $warnings = [];
        $db = null;

        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT.'_realtime');

            $latest = null;
            if (self::tableExists($db, 'kpi_minute')) {
                $latest = self::fetchRow($db, self::buildLatestBucketSql());
            } else {
                $warnings[] = 'Table kpi_minute is missing; realtime KPI bucket is unavailable.';
            }

            return [
                'generated_at' => self::dateTime($now),
                'latest' => $latest,
                'worker' => self::buildWorkerStatus($db, $now, $warnings),
                'daemon' => self::buildDaemonStatus($db, $warnings),
                'events' => self::buildEventStatus($db, $warnings),
                'warnings' => self::uniqueWarnings($warnings),
            ];
        } catch (\Throwable $e) {
            return [
                'generated_at' => self::dateTime($now),
                'latest' => null,
                'worker' => self::emptyWorkerStatus(),
                'daemon' => self::emptyDaemonStatus(),
                'events' => self::emptyEventStatus(),
                'warnings' => [KpiSanitizer::errorMessage($e->getMessage(), 512)],
            ];
        } finally {
            self::closeDb($db);
        }
    }

    public static function buildSeriesPayload(array $query = [], array $options = []): array
    {
        $range = self::normalizeRange($query, $options);
        $warnings = [];
        $rows = [];
        $db = null;

        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT.'_series');
            if (self::tableExists($db, 'kpi_minute')) {
                $rows = self::fetchRows($db, self::buildSeriesSql($db, $range['from'], $range['to'], $range['limit']));
            } else {
                $warnings[] = 'Table kpi_minute is missing; KPI time series are unavailable.';
            }
        } catch (\Throwable $e) {
            $warnings[] = KpiSanitizer::errorMessage($e->getMessage(), 512);
        } finally {
            self::closeDb($db);
        }

        return self::seriesFromRows($rows, $range, $warnings);
    }

    public static function buildTopPayload(array $options = []): array
    {
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $since = self::dateTime($now->modify('-24 hours'));
        $limit = self::positiveLimit($options['limit'] ?? self::TOP_LIMIT, self::TOP_LIMIT);
        $warnings = [];
        $tables = self::emptyTopTables($since, $limit);
        $db = null;

        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT.'_top');
            if (!self::tableExists($db, 'aspirateur_attempt')) {
                $warnings[] = 'Table aspirateur_attempt is missing; KPI top-N tables are unavailable.';

                return ['since' => $since, 'limit' => $limit, 'tables' => $tables, 'warnings' => $warnings];
            }

            $tables['failures']['rows'] = self::decorateTopRows(
                self::fetchRows($db, self::buildTopFailuresSql($db, $since, $limit))
            );
            $tables['readonly']['rows'] = self::decorateTopRows(
                self::fetchRows($db, self::buildTopReadonlySql($db, $since, $limit))
            );
            $tables['transitions']['rows'] = self::decorateTopRows(
                self::fetchRows($db, self::buildTopTransitionsSql($db, $since, $limit))
            );

            return ['since' => $since, 'limit' => $limit, 'tables' => $tables, 'warnings' => $warnings];
        } catch (\Throwable $e) {
            $warnings[] = KpiSanitizer::errorMessage($e->getMessage(), 512);

            return ['since' => $since, 'limit' => $limit, 'tables' => $tables, 'warnings' => $warnings];
        } finally {
            self::closeDb($db);
        }
    }

    public static function normalizeRange(array $query = [], array $options = []): array
    {
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $defaultTo = self::minuteStart($now);
        $defaultFrom = $defaultTo->modify('-'.self::DEFAULT_WINDOW_HOURS.' hours');
        $from = self::dateTimeFromInput($query['from'] ?? null);
        $to = self::dateTimeFromInput($query['to'] ?? null);

        if ($from === null && isset($query['minutes']) && is_numeric($query['minutes'])) {
            $minutes = max(1, min(self::maxSeriesMinutes(), (int)$query['minutes']));
            $to = $defaultTo;
            $from = $to->modify('-'.$minutes.' minutes');
        }

        $from = $from === null ? $defaultFrom : self::minuteStart($from);
        $to = $to === null ? $defaultTo : self::minuteStart($to);

        if ($from > $to) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        $earliest = $to->modify('-'.self::MAX_WINDOW_DAYS.' days');
        if ($from < $earliest) {
            $from = $earliest;
        }

        $points = max(1, min(self::maxSeriesMinutes() + 1, self::minutesBetween($from, $to) + 1));

        return [
            'from' => self::dateTime($from),
            'to' => self::dateTime($to),
            'limit' => $points,
        ];
    }

    public static function seriesFromRows(array $rows, array $range, array $warnings = []): array
    {
        $pointsByMetric = [];
        foreach (array_keys(self::METRICS) as $metric) {
            $pointsByMetric[$metric] = [];
        }

        foreach ($rows as $row) {
            $bucket = (string)($row['bucket_start'] ?? '');
            if ($bucket === '') {
                continue;
            }

            foreach ($pointsByMetric as $metric => $points) {
                $value = $row[$metric] ?? null;
                $pointsByMetric[$metric][] = [
                    'x' => $bucket,
                    'y' => is_numeric($value) ? (float)$value : null,
                ];
            }
        }

        $charts = [];
        foreach (self::CHARTS as $chart) {
            $datasets = [];
            foreach ($chart['metrics'] as $metric) {
                $config = self::METRICS[$metric];
                $datasets[] = [
                    'metric' => $metric,
                    'label' => $config['label'],
                    'unit' => $config['unit'],
                    'color' => $config['color'],
                    'points' => $pointsByMetric[$metric] ?? [],
                ];
            }

            $charts[] = [
                'key' => $chart['key'],
                'canvas_id' => $chart['canvas_id'],
                'title' => $chart['title'],
                'datasets' => $datasets,
            ];
        }

        return [
            'range' => [
                'from' => (string)($range['from'] ?? ''),
                'to' => (string)($range['to'] ?? ''),
                'limit' => (int)($range['limit'] ?? 0),
            ],
            'row_count' => count($rows),
            'charts' => $charts,
            'warnings' => self::uniqueWarnings($warnings),
        ];
    }

    public static function buildLatestBucketSql(): string
    {
        return 'SELECT `bucket_start`, `aspirateur_attempts_total`, `aspirateur_failures_total`, '
            .'`aspirateur_readonly_total`, `aspirateur_p50_ping_ms`, `aspirateur_p95_ping_ms`, '
            .'`aspirateur_p99_ping_ms`, `worker_busy_pct`, `worker_queue_depth_max`, '
            .'`worker_stuck_count`, `daemon_late_count`, `state_transitions_total`, '
            .'`event_log_open_count` '
            .'FROM `kpi_minute` ORDER BY `bucket_start` DESC LIMIT 1;';
    }

    public static function buildSeriesSql($db, string $from, string $to, int $limit): string
    {
        $limit = max(1, min(self::maxSeriesMinutes() + 1, $limit));

        return 'SELECT `bucket_start`, `aspirateur_attempts_total`, `aspirateur_failures_total`, '
            .'`aspirateur_readonly_total`, `aspirateur_p50_ping_ms`, `aspirateur_p95_ping_ms`, '
            .'`aspirateur_p99_ping_ms`, `worker_busy_pct`, `worker_queue_depth_max`, '
            .'`worker_stuck_count`, `daemon_late_count`, `state_transitions_total`, '
            .'`event_log_open_count` '
            .'FROM `kpi_minute` '
            .'WHERE `bucket_start` >= '.self::sqlLiteral($db, $from).' '
            .'AND `bucket_start` <= '.self::sqlLiteral($db, $to).' '
            .'ORDER BY `bucket_start` ASC LIMIT '.$limit.';';
    }

    public static function buildTopFailuresSql($db, string $since, int $limit = self::TOP_LIMIT): string
    {
        return self::buildTopServerSql($db, $since, $limit, 'a.result = 0', 'failures_total');
    }

    public static function buildTopReadonlySql($db, string $since, int $limit = self::TOP_LIMIT): string
    {
        return self::buildTopServerSql($db, $since, $limit, 'a.result = 2', 'readonly_total');
    }

    public static function buildTopTransitionsSql($db, string $since, int $limit = self::TOP_LIMIT): string
    {
        return self::buildTopServerSql($db, $since, $limit, 'a.triggered_state_change = 1', 'transitions_total');
    }

    public static function buildWorkerRunStatusSql(): string
    {
        return 'SELECT COUNT(*) AS `running`, '
            .'COALESCE(SUM(CASE WHEN `is_safe_kill` = 1 THEN 1 ELSE 0 END), 0) AS `safe_kill` '
            .'FROM `worker_run` WHERE `is_working` = 1;';
    }

    public static function buildWorkerExecutionStatusSql($db, string $since, bool $hasStatusColumn): string
    {
        $statusSelect = $hasStatusColumn
            ? "COALESCE(SUM(CASE WHEN `status` = 'STUCK' THEN 1 ELSE 0 END), 0) AS `stuck`, "
                ."COALESCE(SUM(CASE WHEN `status` IN ('ERROR','TIMEOUT','KILLED') THEN 1 ELSE 0 END), 0) AS `errors`"
            : '0 AS `stuck`, 0 AS `errors`';

        return 'SELECT COUNT(*) AS `recent_total`, '
            .'COALESCE(SUM(CASE WHEN `date_end` IS NULL THEN 1 ELSE 0 END), 0) AS `active`, '
            .$statusSelect.' '
            .'FROM `worker_execution` '
            .'WHERE `date_started` >= '.self::sqlLiteral($db, $since).';';
    }

    public static function buildDaemonStatusSql(): string
    {
        return 'SELECT COUNT(*) AS `total`, '
            .'COALESCE(SUM(CASE WHEN `is_enabled` = 1 THEN 1 ELSE 0 END), 0) AS `enabled`, '
            .'COALESCE(SUM(CASE WHEN `is_enabled` = 1 AND `pid` > 0 THEN 1 ELSE 0 END), 0) AS `running`, '
            .'COALESCE(SUM(CASE WHEN `is_enabled` = 1 AND (`pid` = 0 OR `pid` IS NULL) THEN 1 ELSE 0 END), 0) AS `stopped` '
            .'FROM `daemon_main`;';
    }

    public static function buildOpenEventCountSql(): string
    {
        return 'SELECT COUNT(*) AS `open_count` FROM `event_log` WHERE `date_end` IS NULL;';
    }

    public static function buildRecentEventsSql(int $limit = self::EVENT_LIMIT): string
    {
        $limit = max(1, min(50, $limit));

        return 'SELECT `id`, `id_mysql_server`, `type`, `message`, `date_start`, `date_end` '
            .'FROM `event_log` ORDER BY `date_start` DESC LIMIT '.$limit.';';
    }

    private static function buildTopServerSql($db, string $since, int $limit, string $condition, string $valueAlias): string
    {
        $limit = max(1, min(100, $limit));

        return 'SELECT t.`id_mysql_server`, t.`value` AS `'.$valueAlias.'`, t.`last_seen_at`, '
            .'COALESCE(s.`display_name`, s.`name`, CONCAT(\'server #\', t.`id_mysql_server`)) AS `display_name`, '
            .'s.`name`, s.`ip`, s.`port`, c.`libelle` AS `client`, e.`libelle` AS `environment` '
            .'FROM ('
            .'SELECT a.`id_mysql_server`, COUNT(*) AS `value`, MAX(a.`started_at`) AS `last_seen_at` '
            .'FROM `aspirateur_attempt` a '
            .'WHERE a.`id_mysql_server` IS NOT NULL '
            .'AND a.`started_at` >= '.self::sqlLiteral($db, $since).' '
            .'AND '.$condition.' '
            .'GROUP BY a.`id_mysql_server` '
            .'ORDER BY `value` DESC, `last_seen_at` DESC LIMIT '.$limit
            .') t '
            .'LEFT JOIN `mysql_server` s ON s.`id` = t.`id_mysql_server` '
            .'LEFT JOIN `client` c ON c.`id` = s.`id_client` '
            .'LEFT JOIN `environment` e ON e.`id` = s.`id_environment` '
            .'ORDER BY t.`value` DESC, t.`last_seen_at` DESC;';
    }

    private static function buildWorkerStatus($db, \DateTimeImmutable $now, array &$warnings): array
    {
        $status = self::emptyWorkerStatus();

        if (self::tableExists($db, 'worker_run')) {
            $status = array_merge($status, self::normalizeNumericRow(self::fetchRow($db, self::buildWorkerRunStatusSql()) ?? []));
        } else {
            $warnings[] = 'Table worker_run is missing; worker realtime status is unavailable.';
        }

        if (self::tableExists($db, 'worker_execution')) {
            $since = self::dateTime($now->modify('-1 hour'));
            $hasStatusColumn = self::columnExists($db, 'worker_execution', 'status');
            $status = array_merge(
                $status,
                self::normalizeNumericRow(self::fetchRow($db, self::buildWorkerExecutionStatusSql($db, $since, $hasStatusColumn)) ?? [])
            );
        } else {
            $warnings[] = 'Table worker_execution is missing; worker execution status is unavailable.';
        }

        return $status;
    }

    private static function buildDaemonStatus($db, array &$warnings): array
    {
        if (!self::tableExists($db, 'daemon_main')) {
            $warnings[] = 'Table daemon_main is missing; daemon realtime status is unavailable.';

            return self::emptyDaemonStatus();
        }

        return array_merge(self::emptyDaemonStatus(), self::normalizeNumericRow(self::fetchRow($db, self::buildDaemonStatusSql()) ?? []));
    }

    private static function buildEventStatus($db, array &$warnings): array
    {
        if (!self::tableExists($db, 'event_log')) {
            $warnings[] = 'Table event_log is missing; event realtime status is unavailable.';

            return self::emptyEventStatus();
        }

        $count = self::normalizeNumericRow(self::fetchRow($db, self::buildOpenEventCountSql()) ?? []);
        $events = [];
        foreach (self::fetchRows($db, self::buildRecentEventsSql(self::EVENT_LIMIT)) as $row) {
            $row['message'] = KpiSanitizer::errorMessage($row['message'] ?? '', 180);
            $events[] = $row;
        }

        return [
            'open_count' => (int)($count['open_count'] ?? 0),
            'recent' => $events,
        ];
    }

    private static function emptyWorkerStatus(): array
    {
        return [
            'running' => 0,
            'safe_kill' => 0,
            'recent_total' => 0,
            'active' => 0,
            'stuck' => 0,
            'errors' => 0,
        ];
    }

    private static function emptyDaemonStatus(): array
    {
        return ['total' => 0, 'enabled' => 0, 'running' => 0, 'stopped' => 0];
    }

    private static function emptyEventStatus(): array
    {
        return ['open_count' => 0, 'recent' => []];
    }

    private static function emptyTopTables(string $since, int $limit): array
    {
        return [
            'failures' => ['title' => 'Top failures 24h', 'metric' => 'failures_total', 'since' => $since, 'limit' => $limit, 'rows' => []],
            'readonly' => ['title' => 'Top read only 24h', 'metric' => 'readonly_total', 'since' => $since, 'limit' => $limit, 'rows' => []],
            'transitions' => ['title' => 'Top transitions 24h', 'metric' => 'transitions_total', 'since' => $since, 'limit' => $limit, 'rows' => []],
        ];
    }

    private static function decorateTopRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $serverId = (int)($row['id_mysql_server'] ?? 0);
            $row['server_path'] = $serverId > 0 ? 'Kpi/server/'.$serverId : '';
        }
        unset($row);

        return $rows;
    }

    private static function normalizeNumericRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_numeric($value)) {
                $row[$key] = (float)$value == (int)$value ? (int)$value : (float)$value;
            }
        }

        return $row;
    }

    private static function tableExists($db, string $table): bool
    {
        $key = spl_object_id($db).':'.$table;
        if (array_key_exists($key, self::$tableExistsCache)) {
            return self::$tableExistsCache[$key];
        }

        $sql = 'SELECT COUNT(*) FROM information_schema.tables '
            .'WHERE table_schema = DATABASE() '
            .'AND table_name = '.self::sqlLiteral($db, $table).';';

        self::$tableExistsCache[$key] = (int)self::fetchScalar($db, $sql) > 0;

        return self::$tableExistsCache[$key];
    }

    private static function columnExists($db, string $table, string $column): bool
    {
        $key = spl_object_id($db).':'.$table.':'.$column;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }

        $sql = 'SELECT COUNT(*) FROM information_schema.columns '
            .'WHERE table_schema = DATABASE() '
            .'AND table_name = '.self::sqlLiteral($db, $table).' '
            .'AND column_name = '.self::sqlLiteral($db, $column).';';

        self::$columnExistsCache[$key] = (int)self::fetchScalar($db, $sql) > 0;

        return self::$columnExistsCache[$key];
    }

    private static function fetchRows($db, string $sql): array
    {
        $rows = [];
        $res = $db->sql_query($sql);
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private static function fetchRow($db, string $sql): ?array
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchScalar($db, string $sql)
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_NUM);

        return is_array($row) ? ($row[0] ?? null) : null;
    }

    private static function positiveLimit($value, int $default): int
    {
        if (!is_numeric($value)) {
            return $default;
        }

        return max(1, min(100, (int)$value));
    }

    private static function uniqueWarnings(array $warnings): array
    {
        $filtered = [];
        foreach ($warnings as $warning) {
            $warning = trim((string)$warning);
            if ($warning !== '') {
                $filtered[$warning] = true;
            }
        }

        return array_keys($filtered);
    }

    private static function dateTimeFromInput($value): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function dateTimeObject($value): \DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        return new \DateTimeImmutable(is_string($value) && trim($value) !== '' ? $value : 'now');
    }

    private static function minuteStart(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->setTime((int)$date->format('H'), (int)$date->format('i'), 0, 0);
    }

    private static function dateTime(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    private static function minutesBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return max(0, (int)floor(($to->getTimestamp() - $from->getTimestamp()) / 60));
    }

    private static function maxSeriesMinutes(): int
    {
        return self::MAX_WINDOW_DAYS * 24 * 60;
    }

    private static function sqlLiteral($db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string((string)$value)."'";
    }

    private static function closeDb($db): void
    {
        if (is_object($db) && method_exists($db, 'sql_close')) {
            $db->sql_close();
        }
    }
}
