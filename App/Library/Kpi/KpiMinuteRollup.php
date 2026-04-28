<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class KpiMinuteRollup
{
    public const DEFAULT_MAX_BUCKETS = 60;

    private const CONNECTION_SLOT = 'kpi_rollup';
    private const LOCK_NAME = 'pmacontrol_kpi_minute_rollup';

    public static function rollupMinute(array $input = []): array
    {
        $maxBuckets = self::positiveInt($input['max_buckets'] ?? self::DEFAULT_MAX_BUCKETS) ?? self::DEFAULT_MAX_BUCKETS;
        $now = self::dateTime($input['now'] ?? microtime(true));
        $processedBuckets = [];
        $locked = false;
        $db = null;

        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $lockResult = self::fetchScalar($db, self::buildAcquireLockSql($db));
            if ((int)$lockResult !== 1) {
                return [
                    'status' => 'LOCKED',
                    'processed' => 0,
                    'buckets' => [],
                    'now' => $now,
                ];
            }

            $locked = true;
            $lastBucket = self::normalizeNullableDateTime(self::fetchScalar($db, self::buildLastBucketSql()));
            $buckets = self::buildDueBuckets($lastBucket, $now, $maxBuckets);

            foreach ($buckets as $bucketStart) {
                $db->sql_query(self::buildRollupSql($db, $bucketStart));
                $processedBuckets[] = $bucketStart;
            }

            return [
                'status' => 'OK',
                'processed' => count($processedBuckets),
                'buckets' => $processedBuckets,
                'now' => $now,
                'last_bucket' => $lastBucket,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'ERROR',
                'processed' => count($processedBuckets),
                'buckets' => $processedBuckets,
                'now' => $now,
                'error_class' => get_class($e),
                'error_message' => KpiSanitizer::errorMessage($e->getMessage(), 512),
            ];
        } finally {
            if ($locked && is_object($db)) {
                try {
                    $db->sql_query(self::buildReleaseLockSql($db));
                } catch (\Throwable $e) {
                    // Rollup results are already committed; lock release failure must not mask them.
                }
            }

            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildDueBuckets(?string $lastBucketStart, string $now, int $maxBuckets = self::DEFAULT_MAX_BUCKETS): array
    {
        $maxBuckets = max(1, $maxBuckets);
        $latestComplete = self::dateTimeMinute(self::dateTimeObject($now)->modify('-1 minute'));

        if ($lastBucketStart === null || trim($lastBucketStart) === '') {
            $next = self::dateTimeObject($latestComplete);
        } else {
            $next = self::dateTimeObject($lastBucketStart)->modify('+1 minute');
        }

        $end = self::dateTimeObject($latestComplete);
        if ($next > $end) {
            return [];
        }

        $buckets = [];
        while ($next <= $end && count($buckets) < $maxBuckets) {
            $buckets[] = self::dateTimeMinute($next);
            $next = $next->modify('+1 minute');
        }

        return $buckets;
    }

    public static function buildLastBucketSql(): string
    {
        return 'SELECT MAX(`bucket_start`) AS `last_bucket` FROM `kpi_minute`;';
    }

    public static function buildAcquireLockSql($db): string
    {
        return 'SELECT GET_LOCK('.self::sqlLiteral($db, self::LOCK_NAME).', 0) AS `locked`;';
    }

    public static function buildReleaseLockSql($db): string
    {
        return 'SELECT RELEASE_LOCK('.self::sqlLiteral($db, self::LOCK_NAME).') AS `released`;';
    }

    public static function buildRollupSql($db, string $bucketStart): string
    {
        $bucketStart = self::dateTimeMinute(self::dateTimeObject($bucketStart));
        $bucketEnd = self::dateTimeMinute(self::dateTimeObject($bucketStart)->modify('+1 minute'));
        $start = self::sqlLiteral($db, $bucketStart);
        $end = self::sqlLiteral($db, $bucketEnd);

        $columns = [
            'bucket_start',
            'aspirateur_attempts_total',
            'aspirateur_failures_total',
            'aspirateur_readonly_total',
            'aspirateur_p50_ping_ms',
            'aspirateur_p95_ping_ms',
            'aspirateur_p99_ping_ms',
            'worker_busy_pct',
            'worker_queue_depth_max',
            'worker_stuck_count',
            'daemon_late_count',
            'state_transitions_total',
            'event_log_open_count',
        ];

        $selects = [
            $start.' AS `bucket_start`',
            self::attemptCountSql($start, $end).' AS `aspirateur_attempts_total`',
            self::attemptSumSql($start, $end, '`result` = 0').' AS `aspirateur_failures_total`',
            self::attemptSumSql($start, $end, '`result` = 2').' AS `aspirateur_readonly_total`',
            self::pingPercentileSql($start, $end, '0.50').' AS `aspirateur_p50_ping_ms`',
            self::pingPercentileSql($start, $end, '0.95').' AS `aspirateur_p95_ping_ms`',
            self::pingPercentileSql($start, $end, '0.99').' AS `aspirateur_p99_ping_ms`',
            self::workerBusyPctSql($start, $end).' AS `worker_busy_pct`',
            'NULL AS `worker_queue_depth_max`',
            self::workerStuckCountSql($start, $end).' AS `worker_stuck_count`',
            self::daemonLateCountSql($start, $end).' AS `daemon_late_count`',
            self::attemptSumSql($start, $end, '`triggered_state_change` = 1').' AS `state_transitions_total`',
            self::eventLogOpenCountSql($end).' AS `event_log_open_count`',
        ];

        $updates = [];
        foreach (array_slice($columns, 1) as $column) {
            $updates[] = '`'.$column.'` = VALUES(`'.$column.'`)';
        }

        return 'INSERT INTO `kpi_minute` (`'
            .implode('`, `', $columns)
            .'`) SELECT '
            .implode(",\n       ", $selects)
            .' ON DUPLICATE KEY UPDATE '
            .implode(', ', $updates)
            .';';
    }

    private static function attemptCountSql(string $start, string $end): string
    {
        return '(SELECT COUNT(*) FROM `aspirateur_attempt` '
            .'WHERE `started_at` >= '.$start.' AND `started_at` < '.$end.')';
    }

    private static function attemptSumSql(string $start, string $end, string $condition): string
    {
        return '(SELECT COALESCE(SUM(CASE WHEN '.$condition.' THEN 1 ELSE 0 END), 0) '
            .'FROM `aspirateur_attempt` '
            .'WHERE `started_at` >= '.$start.' AND `started_at` < '.$end.')';
    }

    private static function pingPercentileSql(string $start, string $end, string $percentile): string
    {
        return '(SELECT CAST(ROUND(MAX(`p`)) AS UNSIGNED) FROM ('
            .'SELECT PERCENTILE_CONT('.$percentile.') WITHIN GROUP (ORDER BY `ping_ms`) OVER () AS `p` '
            .'FROM ('
            .'SELECT (`ping_seconds` * 1000) AS `ping_ms` '
            .'FROM `aspirateur_attempt` '
            .'WHERE `started_at` >= '.$start.' AND `started_at` < '.$end.' '
            .'AND `ping_seconds` IS NOT NULL'
            .') `kpi_ping_values`'
            .') `kpi_ping_percentile`)';
    }

    private static function workerBusyPctSql(string $start, string $end): string
    {
        return '(SELECT CASE '
            .'WHEN COALESCE(SUM(`nb_worker`), 0) <= 0 THEN NULL '
            .'ELSE LEAST(100.00, ROUND(('
            .'SELECT COALESCE(SUM(GREATEST(0, TIMESTAMPDIFF(SECOND, '
            .'GREATEST(`worker_execution`.`date_started`, '.$start.'), '
            .'LEAST(COALESCE(`worker_execution`.`date_end`, '.$end.'), '.$end.')))), 0) '
            .'FROM `worker_execution` '
            .'WHERE `worker_execution`.`date_started` < '.$end.' '
            .'AND COALESCE(`worker_execution`.`date_end`, '.$end.') > '.$start
            .') * 100 / (SUM(`nb_worker`) * 60), 2)) '
            .'END FROM `worker_queue`)';
    }

    private static function workerStuckCountSql(string $start, string $end): string
    {
        return "(SELECT COUNT(*) FROM `worker_execution` "
            ."WHERE `status` = 'STUCK' "
            .'AND `date_started` < '.$end.' '
            .'AND COALESCE(`date_end`, '.$end.') >= '.$start.')';
    }

    private static function daemonLateCountSql(string $start, string $end): string
    {
        return '(SELECT COUNT(*) FROM `daemon_run` '
            .'WHERE `over_max_delay` = 1 '
            .'AND `cycle_started_at` >= '.$start.' AND `cycle_started_at` < '.$end.')';
    }

    private static function eventLogOpenCountSql(string $end): string
    {
        return '(SELECT COUNT(*) FROM `event_log` '
            .'WHERE `date_start` < '.$end.' '
            .'AND (`date_end` IS NULL OR `date_end` >= '.$end.'))';
    }

    private static function fetchScalar($db, string $sql)
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_NUM);

        return is_array($row) ? ($row[0] ?? null) : null;
    }

    private static function normalizeNullableDateTime($value): ?string
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        return self::dateTimeMinute(self::dateTimeObject((string)$value));
    }

    private static function positiveInt($value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $int = (int)$value;

        return $int > 0 ? $int : null;
    }

    private static function dateTime($value): string
    {
        return self::dateTimeObject($value)->format('Y-m-d H:i:s');
    }

    private static function dateTimeMinute(\DateTimeImmutable $date): string
    {
        return $date->setTime(
            (int)$date->format('H'),
            (int)$date->format('i'),
            0,
            0
        )->format('Y-m-d H:i:s');
    }

    private static function dateTimeObject($value): \DateTimeImmutable
    {
        if (is_int($value) || is_float($value)) {
            return (new \DateTimeImmutable())->setTimestamp((int)$value);
        }

        $value = trim((string)$value);
        foreach (['Y-m-d H:i:s.u', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }
        }

        return new \DateTimeImmutable($value !== '' ? $value : 'now');
    }

    private static function sqlLiteral($db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (is_float($value)) {
            return number_format($value, 6, '.', '');
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string((string)$value)."'";
    }
}
