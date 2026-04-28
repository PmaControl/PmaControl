<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class KpiDaemonDrilldown
{
    public const RUN_LIMIT = 100;
    public const RESTART_PID_LIMIT = 200000;

    private const CONNECTION_SLOT = 'kpi_daemon_drilldown';
    private static array $tableExistsCache = [];

    public static function buildPayload(int $daemonId, array $options = []): array
    {
        $daemonId = max(0, $daemonId);
        $now = self::dateTimeObject($options['now'] ?? 'now');
        $end = self::minuteStart($now);
        $start = $end->modify('-24 hours');
        $since = self::dateTime($start);
        $warnings = [];
        $degraded = false;
        $durationSeries = self::seriesFromRows([], $start, $end);
        $delaySeries = self::seriesFromRows([], $start, $end);
        $runs = [];
        $lastCycle = null;
        $restartCount = 0;

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $daemon = self::fetchDaemon($db, $daemonId);
            if ($daemon === null) {
                return self::emptyPayload($daemonId, $now, ['Daemon not found.']);
            }

            $workerQueue = self::fetchWorkerQueue($db, $daemonId);
            $maxDelayMs = max(0, (int)($daemon['max_delay'] ?? 0)) * 1000;

            if (self::tableExists($db, 'daemon_run')) {
                try {
                    $lastCycle = self::fetchLastCycle($db, $daemonId);
                    $runs = self::fetchRuns($db, $daemonId, self::RUN_LIMIT);
                    $durationSeries = self::fetchSeries($db, $daemonId, $since, 'duration_ms', $start, $end);
                    $delaySeries = self::fetchSeries($db, $daemonId, $since, 'delay_ms', $start, $end);
                    $restartCount = self::countPidRestarts(self::fetchRestartPids($db, $daemonId, $since));
                } catch (\Throwable $e) {
                    $degraded = true;
                    $warnings[] = 'Daemon run KPI is unavailable: '.KpiSanitizer::errorMessage($e->getMessage(), 512);
                }
            } else {
                $degraded = true;
                $warnings[] = 'Table daemon_run is missing; daemon KPI history is not available yet.';
            }

            return [
                'daemon_id' => $daemonId,
                'daemon' => $daemon,
                'status' => [
                    'label' => self::daemonStatusLabel($daemon),
                    'class' => self::daemonStatusClass($daemon),
                ],
                'last_cycle' => $lastCycle,
                'duration_series' => $durationSeries,
                'delay_series' => $delaySeries,
                'runs' => $runs,
                'restart_count_24h' => $restartCount,
                'worker_queue' => $workerQueue,
                'worker_link' => $workerQueue === null ? null : 'worker/list',
                'max_delay_ms' => $maxDelayMs,
                'generated_at' => self::dateTime($now),
                'degraded' => $degraded,
                'warnings' => $warnings,
            ];
        } catch (\Throwable $e) {
            return self::emptyPayload($daemonId, $now, [KpiSanitizer::errorMessage($e->getMessage(), 512)], true);
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildDaemonSql(int $daemonId): string
    {
        return 'SELECT `id`, `name`, `date`, `pid`, `refresh_time`, `max_delay`, '
            .'`class`, `method`, `params`, `debug`, `is_enabled` '
            .'FROM `daemon_main` '
            .'WHERE `id` = '.$daemonId.' LIMIT 1;';
    }

    public static function buildWorkerQueueSql(int $daemonId): string
    {
        return 'SELECT `id`, `id_daemon_main`, `table`, `name`, `nb_worker`, `timeout`, '
            .'`queue_number`, `worker_class`, `worker_method`, `max_execution_time`, `query` '
            .'FROM `worker_queue` '
            .'WHERE `id_daemon_main` = '.$daemonId.' LIMIT 1;';
    }

    public static function buildLastCycleSql(int $daemonId): string
    {
        return 'SELECT `id`, `id_daemon_main`, `pid`, `cycle_started_at`, `cycle_ended_at`, '
            .'`status`, `duration_ms`, `delay_ms`, `skipped`, `over_max_delay`, '
            .'`cpu_user_pct`, `cpu_sys_pct`, `rss_kb` '
            .'FROM `daemon_run` '
            .'WHERE `id_daemon_main` = '.$daemonId.' '
            .'ORDER BY `cycle_started_at` DESC LIMIT 1;';
    }

    public static function buildRunsSql(int $daemonId, int $limit = self::RUN_LIMIT): string
    {
        $limit = max(1, min(500, $limit));

        return 'SELECT `id`, `id_daemon_main`, `pid`, `cycle_started_at`, `cycle_ended_at`, '
            .'`status`, `duration_ms`, `delay_ms`, `skipped`, `over_max_delay`, '
            .'`cpu_user_pct`, `cpu_sys_pct`, `rss_kb` '
            .'FROM `daemon_run` '
            .'WHERE `id_daemon_main` = '.$daemonId.' '
            .'ORDER BY `cycle_started_at` DESC LIMIT '.$limit.';';
    }

    public static function buildSeriesSql($db, int $daemonId, string $since, string $metric): string
    {
        $metric = self::validatedMetric($metric);

        return 'SELECT DATE_FORMAT(`cycle_started_at`, \'%Y-%m-%d %H:%i:00\') AS `bucket_at`, '
            .'AVG(`'.$metric.'`) AS `value` '
            .'FROM `daemon_run` '
            .'WHERE `id_daemon_main` = '.$daemonId.' '
            .'AND `cycle_started_at` >= '.self::sqlLiteral($db, $since).' '
            .'AND `'.$metric.'` IS NOT NULL '
            .'GROUP BY `bucket_at` '
            .'ORDER BY `bucket_at` ASC;';
    }

    public static function buildRestartPidSql($db, int $daemonId, string $since): string
    {
        return 'SELECT `pid`, `cycle_started_at` '
            .'FROM `daemon_run` '
            .'WHERE `id_daemon_main` = '.$daemonId.' '
            .'AND `cycle_started_at` >= '.self::sqlLiteral($db, $since).' '
            .'ORDER BY `cycle_started_at` ASC LIMIT '.self::RESTART_PID_LIMIT.';';
    }

    public static function seriesFromRows(array $rows, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $bucketValues = [];
        foreach ($rows as $row) {
            $timestamp = strtotime((string)($row['bucket_at'] ?? ''));
            if ($timestamp === false || !is_numeric($row['value'] ?? null)) {
                continue;
            }

            $bucketKey = date('Y-m-d H:i:00', $timestamp - ($timestamp % 60));
            $bucketValues[$bucketKey] = round((float)$row['value'], 3);
        }

        $labels = [];
        $values = [];
        $points = [];
        $cursor = self::minuteStart($start);
        $end = self::minuteStart($end);

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d H:i:s');
            $value = $bucketValues[$key] ?? null;
            $labels[] = $cursor->format('H:i');
            $values[] = $value;
            $points[] = ['x' => $key, 'y' => $value];
            $cursor = $cursor->modify('+1 minute');
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'points' => $points,
            'summary' => self::summarizeSeries($values),
        ];
    }

    public static function countPidRestarts(array $rows): int
    {
        $previousPid = null;
        $restarts = 0;

        foreach ($rows as $row) {
            if (!is_numeric($row['pid'] ?? null)) {
                continue;
            }

            $pid = (int)$row['pid'];
            if ($pid <= 0) {
                // A zero PID means unknown/stopped; do not infer a restart from an unknown state.
                continue;
            }

            if ($previousPid !== null && $pid !== $previousPid) {
                $restarts++;
            }

            $previousPid = $pid;
        }

        return $restarts;
    }

    public static function daemonStatusLabel(array $daemon): string
    {
        if ((int)($daemon['is_enabled'] ?? 0) !== 1) {
            return 'DISABLED';
        }

        if ((int)($daemon['pid'] ?? 0) > 0) {
            return 'RUNNING';
        }

        return 'ENABLED';
    }

    public static function daemonStatusClass(array $daemon): string
    {
        if ((int)($daemon['is_enabled'] ?? 0) !== 1) {
            return 'unknown';
        }

        if ((int)($daemon['pid'] ?? 0) > 0) {
            return 'up';
        }

        return 'readonly';
    }

    public static function runStatusClass($status): string
    {
        if ((string)$status === 'OK') {
            return 'up';
        }

        if ((string)$status === 'CRASHED') {
            return 'down';
        }

        return 'unknown';
    }

    private static function fetchDaemon($db, int $daemonId): ?array
    {
        $res = $db->sql_query(self::buildDaemonSql($daemonId));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchWorkerQueue($db, int $daemonId): ?array
    {
        $res = $db->sql_query(self::buildWorkerQueueSql($daemonId));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        return is_array($row) ? $row : null;
    }

    private static function fetchLastCycle($db, int $daemonId): ?array
    {
        $res = $db->sql_query(self::buildLastCycleSql($daemonId));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        $row['status_class'] = self::runStatusClass($row['status'] ?? null);

        return $row;
    }

    private static function fetchRuns($db, int $daemonId, int $limit): array
    {
        $rows = [];
        $res = $db->sql_query(self::buildRunsSql($daemonId, $limit));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['status_class'] = self::runStatusClass($row['status'] ?? null);
            $rows[] = $row;
        }

        return $rows;
    }

    private static function fetchSeries($db, int $daemonId, string $since, string $metric, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $rows = [];
        $res = $db->sql_query(self::buildSeriesSql($db, $daemonId, $since, $metric));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return self::seriesFromRows($rows, $start, $end);
    }

    private static function fetchRestartPids($db, int $daemonId, string $since): array
    {
        $rows = [];
        $res = $db->sql_query(self::buildRestartPidSql($db, $daemonId, $since));
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private static function tableExists($db, string $table): bool
    {
        if (array_key_exists($table, self::$tableExistsCache)) {
            return self::$tableExistsCache[$table];
        }

        $sql = 'SELECT COUNT(*) FROM information_schema.tables '
            .'WHERE table_schema = DATABASE() '
            .'AND table_name = '.self::sqlLiteral($db, $table).';';

        self::$tableExistsCache[$table] = (int)self::fetchScalar($db, $sql) > 0;

        return self::$tableExistsCache[$table];
    }

    private static function fetchScalar($db, string $sql)
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_NUM);

        return is_array($row) ? ($row[0] ?? null) : null;
    }

    private static function summarizeSeries(array $values): array
    {
        $numeric = array_values(array_filter($values, 'is_numeric'));
        if (empty($numeric)) {
            return ['count' => 0, 'min' => null, 'max' => null, 'avg' => null];
        }

        $sum = array_sum($numeric);

        return [
            'count' => count($numeric),
            'min' => min($numeric),
            'max' => max($numeric),
            'avg' => round($sum / count($numeric), 3),
        ];
    }

    private static function emptyPayload(int $daemonId, \DateTimeImmutable $now, array $warnings, bool $degraded = true): array
    {
        $end = self::minuteStart($now);
        $start = $end->modify('-24 hours');

        return [
            'daemon_id' => $daemonId,
            'daemon' => null,
            'status' => ['label' => 'UNKNOWN', 'class' => 'unknown'],
            'last_cycle' => null,
            'duration_series' => self::seriesFromRows([], $start, $end),
            'delay_series' => self::seriesFromRows([], $start, $end),
            'runs' => [],
            'restart_count_24h' => 0,
            'worker_queue' => null,
            'worker_link' => null,
            'max_delay_ms' => 0,
            'generated_at' => self::dateTime($now),
            'degraded' => $degraded,
            'warnings' => $warnings,
        ];
    }

    private static function validatedMetric(string $metric): string
    {
        if ($metric !== 'duration_ms' && $metric !== 'delay_ms') {
            throw new \InvalidArgumentException('Unsupported daemon_run metric.');
        }

        return $metric;
    }

    private static function minuteStart(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->setTime((int)$date->format('H'), (int)$date->format('i'), 0, 0);
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

    private static function dateTime(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    private static function sqlLiteral($db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string((string)$value)."'";
    }
}
