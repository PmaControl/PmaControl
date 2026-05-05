<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class DaemonRunLogger
{
    private const CONNECTION_SLOT = 'kpi';

    public static function startCycle(array $input): ?array
    {
        $cycle = self::buildCycle($input);
        if ($cycle === null) {
            return null;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $previousEndedAt = self::fetchPreviousEndedAt($db, $cycle['id_daemon_main']);
            $cycle['delay_ms'] = self::computeDelayMs(
                $previousEndedAt,
                $cycle['cycle_started_at_epoch'],
                $cycle['refresh_time']
            );
            $cycle['over_max_delay'] = self::isOverMaxDelay($cycle['delay_ms'], $cycle['max_delay']);
            $cycle['resource_start'] = self::sampleResourceStart();

            $db->sql_query(self::buildInsertSql($db, $cycle));
            $cycle['id'] = (int)$db->sql_insert_id();

            return $cycle;
        } catch (\Throwable $e) {
            return null;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function finishCycle(?array $cycle, array $input = []): bool
    {
        if ($cycle === null || empty($cycle['id'])) {
            return false;
        }

        $endedAt = self::timestamp($input['cycle_ended_at'] ?? $input['ended_at'] ?? microtime(true));
        $startedAt = (float)($cycle['cycle_started_at_epoch'] ?? $endedAt);
        $resources = self::finishResourceSample($cycle['resource_start'] ?? null, $endedAt);

        $payload = [
            'id' => (int)$cycle['id'],
            'id_daemon_main' => (int)$cycle['id_daemon_main'],
            'cycle_ended_at' => self::dateTime($endedAt),
            'duration_ms' => self::durationMs($startedAt, $endedAt),
            'status' => self::status($input['status'] ?? 'OK'),
            'skipped' => self::boolInt($input['skipped'] ?? false),
            'over_max_delay' => self::boolInt($input['over_max_delay'] ?? $cycle['over_max_delay'] ?? false),
            'cpu_user_pct' => $resources['cpu_user_pct'],
            'cpu_sys_pct' => $resources['cpu_sys_pct'],
            'rss_kb' => $resources['rss_kb'],
        ];

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildFinishSql($db, $payload));

            return true;
        } catch (\Throwable $e) {
            return false;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function markCrashedRuns(int $idDaemonMain): int
    {
        if ($idDaemonMain <= 0) {
            return 0;
        }

        $db = null;
        try {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
            $db->sql_query(self::buildMarkCrashedSql($idDaemonMain));

            return method_exists($db, 'sql_affected_rows') ? max(0, (int)$db->sql_affected_rows()) : 0;
        } catch (\Throwable $e) {
            return 0;
        } finally {
            if (is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildCycle(array $input): ?array
    {
        $idDaemonMain = self::positiveInt($input['id_daemon_main'] ?? null);
        $pid = self::positiveInt($input['pid'] ?? null);
        if ($idDaemonMain === null || $pid === null) {
            return null;
        }

        $startedAt = self::timestamp($input['cycle_started_at'] ?? $input['started_at'] ?? microtime(true));
        $refreshTime = self::nonNegativeInt($input['refresh_time'] ?? 0);
        $maxDelay = self::nonNegativeInt($input['max_delay'] ?? 0);

        $delayMs = array_key_exists('delay_ms', $input) ? self::nullableInt($input['delay_ms']) : null;

        return [
            'id' => self::positiveInt($input['id'] ?? null),
            'id_daemon_main' => $idDaemonMain,
            'pid' => $pid,
            'cycle_started_at' => self::dateTime($startedAt),
            'cycle_started_at_epoch' => $startedAt,
            'refresh_time' => $refreshTime,
            'max_delay' => $maxDelay,
            'delay_ms' => $delayMs,
            'skipped' => self::boolInt($input['skipped'] ?? false),
            'over_max_delay' => array_key_exists('over_max_delay', $input)
                ? self::boolInt($input['over_max_delay'])
                : self::isOverMaxDelay($delayMs, $maxDelay),
            'resource_start' => null,
        ];
    }

    public static function buildInsertSql($db, array $cycle): string
    {
        $columns = [
            'id_daemon_main',
            'pid',
            'cycle_started_at',
            'cycle_ended_at',
            'status',
            'duration_ms',
            'delay_ms',
            'skipped',
            'over_max_delay',
            'cpu_user_pct',
            'cpu_sys_pct',
            'rss_kb',
        ];

        $values = [
            $cycle['id_daemon_main'],
            $cycle['pid'],
            $cycle['cycle_started_at'],
            null,
            'OK',
            null,
            $cycle['delay_ms'],
            $cycle['skipped'],
            $cycle['over_max_delay'],
            null,
            null,
            null,
        ];

        return 'INSERT INTO `daemon_run` (`'
            .implode('`, `', $columns)
            .'`) VALUES ('
            .implode(', ', array_map(static fn($value): string => self::sqlLiteral($db, $value), $values))
            .');';
    }

    public static function buildFinishSql($db, array $payload): string
    {
        $assignments = [
            '`cycle_ended_at` = '.self::sqlLiteral($db, $payload['cycle_ended_at'] ?? null),
            '`status` = '.self::sqlLiteral($db, self::status($payload['status'] ?? 'OK')),
            '`duration_ms` = '.self::sqlLiteral($db, self::nonNegativeInt($payload['duration_ms'] ?? 0)),
            '`skipped` = '.self::sqlLiteral($db, self::boolInt($payload['skipped'] ?? false)),
            '`over_max_delay` = '.self::sqlLiteral($db, self::boolInt($payload['over_max_delay'] ?? false)),
            '`cpu_user_pct` = '.self::sqlLiteral($db, self::nullableDecimal($payload['cpu_user_pct'] ?? null)),
            '`cpu_sys_pct` = '.self::sqlLiteral($db, self::nullableDecimal($payload['cpu_sys_pct'] ?? null)),
            '`rss_kb` = '.self::sqlLiteral($db, self::nullableInt($payload['rss_kb'] ?? null)),
        ];

        return 'UPDATE `daemon_run` SET '
            .implode(', ', $assignments)
            .' WHERE `id` = '.(int)$payload['id']
            .' AND `id_daemon_main` = '.(int)$payload['id_daemon_main']
            .';';
    }

    public static function buildMarkCrashedSql(int $idDaemonMain): string
    {
        return 'UPDATE `daemon_run` SET '
            .'`cycle_ended_at` = NOW(6), '
            .'`status` = \'CRASHED\', '
            .'`duration_ms` = GREATEST(0, ROUND(TIMESTAMPDIFF(MICROSECOND, `cycle_started_at`, NOW(6)) / 1000)) '
            .'WHERE `id_daemon_main` = '.$idDaemonMain.' '
            .'AND `cycle_ended_at` IS NULL;';
    }

    public static function computeDelayMs(?float $previousEndedAt, float $cycleStartedAt, int $refreshTime): ?int
    {
        if ($previousEndedAt === null) {
            return null;
        }

        return (int)round(($cycleStartedAt - $previousEndedAt) * 1000) - ($refreshTime * 1000);
    }

    public static function isOverMaxDelay(?int $delayMs, int $maxDelay): int
    {
        return $delayMs !== null && $delayMs > ($maxDelay * 1000) ? 1 : 0;
    }

    private static function fetchPreviousEndedAt($db, int $idDaemonMain): ?float
    {
        $sql = "SELECT UNIX_TIMESTAMP(`cycle_ended_at`) AS `ended_at` "
            ."FROM `daemon_run` "
            ."WHERE `id_daemon_main` = ".$idDaemonMain." "
            ."AND `cycle_ended_at` IS NOT NULL "
            ."ORDER BY `cycle_started_at` DESC LIMIT 1;";
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (!is_array($row) || !is_numeric($row['ended_at'] ?? null)) {
            return null;
        }

        return (float)$row['ended_at'];
    }

    private static function sampleResourceStart(): ?array
    {
        if (getenv('KPI_SAMPLE_RESOURCES') !== '1') {
            return null;
        }

        return [
            'time' => microtime(true),
            'user_seconds' => self::usageSeconds(getrusage(), 'ru_utime'),
            'sys_seconds' => self::usageSeconds(getrusage(), 'ru_stime'),
        ];
    }

    private static function finishResourceSample(?array $start, float $endedAt): array
    {
        if ($start === null) {
            return [
                'cpu_user_pct' => null,
                'cpu_sys_pct' => null,
                'rss_kb' => null,
            ];
        }

        $usage = getrusage();
        $durationSeconds = max(0.001, $endedAt - (float)$start['time']);

        return [
            'cpu_user_pct' => round(max(0.0, self::usageSeconds($usage, 'ru_utime') - (float)$start['user_seconds']) / $durationSeconds * 100, 2),
            'cpu_sys_pct' => round(max(0.0, self::usageSeconds($usage, 'ru_stime') - (float)$start['sys_seconds']) / $durationSeconds * 100, 2),
            'rss_kb' => self::readRssKb(getmypid()),
        ];
    }

    private static function readRssKb(int $pid): ?int
    {
        $statusPath = '/proc/'.$pid.'/status';
        if (!is_readable($statusPath)) {
            return null;
        }

        $status = (string)@file_get_contents($statusPath);
        if (preg_match('/^VmRSS:\s+(\d+)\s+kB$/m', $status, $matches) !== 1) {
            return null;
        }

        return (int)$matches[1];
    }

    private static function usageSeconds(array $usage, string $prefix): float
    {
        return (float)($usage[$prefix.'.tv_sec'] ?? 0) + ((float)($usage[$prefix.'.tv_usec'] ?? 0) / 1000000);
    }

    private static function durationMs(float $startedAt, float $endedAt): int
    {
        return max(0, (int)round(($endedAt - $startedAt) * 1000));
    }

    private static function timestamp($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }

        $value = trim((string)$value);
        if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d{1,6})?$/', $value) === 1) {
            $format = strpos($value, '.') === false ? 'Y-m-d H:i:s' : 'Y-m-d H:i:s.u';
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return (float)$date->format('U.u');
            }
        }

        return microtime(true);
    }

    private static function dateTime(float $timestamp): string
    {
        $seconds = (int)$timestamp;
        $microseconds = (int)round(($timestamp - $seconds) * 1000000);

        if ($microseconds >= 1000000) {
            $seconds++;
            $microseconds = 0;
        }

        return date('Y-m-d H:i:s', $seconds).'.'.sprintf('%06d', $microseconds);
    }

    private static function positiveInt($value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $int = (int)$value;

        return $int > 0 ? $int : null;
    }

    private static function nonNegativeInt($value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }

        return max(0, (int)$value);
    }

    private static function nullableInt($value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int)$value;
    }

    private static function nullableDecimal($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return round((float)$value, 2);
    }

    private static function boolInt($value): int
    {
        return !empty($value) ? 1 : 0;
    }

    private static function status($value): string
    {
        return $value === 'CRASHED' ? 'CRASHED' : 'OK';
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
            return number_format($value, 2, '.', '');
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string((string)$value)."'";
    }
}
