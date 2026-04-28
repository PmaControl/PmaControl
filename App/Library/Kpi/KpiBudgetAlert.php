<?php

namespace App\Library\Kpi;

final class KpiBudgetAlert
{
    private const METRICS = [
        'worker_busy_pct' => true,
        'worker_stuck_count' => true,
        'daemon_late_count' => true,
        'state_transitions_total' => true,
        'aspirateur_failures_total' => true,
    ];

    private static bool $configLoaded = false;

    public static function evaluateBucket($db, string $bucketStart, array $overrides = []): array
    {
        $bucketStart = self::dateTimeMinute(self::dateTimeObject($bucketStart));
        $results = [];

        foreach (self::rules($overrides) as $type => $rule) {
            $evaluation = self::evaluateRule($db, $rule, $bucketStart);
            if ($evaluation['breaching']) {
                $event = EventLog::recordEvent($type, self::message($rule, $evaluation), null, $bucketStart, $db);
            } else {
                $event = EventLog::closeEvent($type, null, $bucketStart, $db);
            }

            $results[$type] = [
                'breaching' => $evaluation['breaching'],
                'bucket_count' => $evaluation['bucket_count'],
                'breach_count' => $evaluation['breach_count'],
                'event' => $event,
            ];
        }

        return $results;
    }

    public static function rules(array $overrides = []): array
    {
        self::loadConfig();

        return [
            'kpi_workers_saturated' => [
                'metric' => 'worker_busy_pct',
                'operator' => '>',
                'threshold' => self::floatOption($overrides, 'worker_busy_pct', 'KPI_BUDGET_WORKER_BUSY_PCT', 90.0),
                'duration_minutes' => self::intOption($overrides, 'worker_busy_min', 'KPI_BUDGET_WORKER_BUSY_MIN', 5),
                'label' => 'Worker saturation',
                'unit' => '%',
            ],
            'kpi_worker_stuck' => [
                'metric' => 'worker_stuck_count',
                'operator' => '>',
                'threshold' => 0,
                'duration_minutes' => self::intOption($overrides, 'worker_stuck_min', 'KPI_BUDGET_WORKER_STUCK_MIN', 2),
                'label' => 'Worker stuck',
                'unit' => '',
            ],
            'kpi_daemon_late' => [
                'metric' => 'daemon_late_count',
                'operator' => '>',
                'threshold' => 0,
                'duration_minutes' => self::intOption($overrides, 'daemon_late_min', 'KPI_BUDGET_DAEMON_LATE_MIN', 5),
                'label' => 'Daemon late',
                'unit' => '',
            ],
            'kpi_flapping' => [
                'metric' => 'state_transitions_total',
                'operator' => '>',
                'threshold' => self::intOption($overrides, 'state_transitions_per_min', 'KPI_BUDGET_STATE_TRANSITIONS_PER_MIN', 2),
                'duration_minutes' => self::intOption($overrides, 'flapping_min', 'KPI_BUDGET_FLAPPING_MIN', 5),
                'label' => 'State flapping',
                'unit' => '/min',
            ],
            'kpi_collect_degraded' => [
                'metric' => 'aspirateur_failures_total',
                'operator' => '>',
                'threshold' => self::intOption($overrides, 'aspirateur_failures_total', 'KPI_BUDGET_ASPIRATEUR_FAILURES_TOTAL', 10),
                'duration_minutes' => self::intOption($overrides, 'aspirateur_failures_min', 'KPI_BUDGET_ASPIRATEUR_FAILURES_MIN', 1),
                'label' => 'Aspirateur collect degraded',
                'unit' => '',
            ],
        ];
    }

    public static function buildRuleWindowSql($db, array $rule, string $bucketStart): string
    {
        // Missing buckets keep the rule inactive, which avoids false positives during rollup backfill gaps.
        $metric = self::metricIdentifier((string)$rule['metric']);
        $duration = max(1, (int)$rule['duration_minutes']);
        $bucketStart = self::dateTimeMinute(self::dateTimeObject($bucketStart));
        $windowStart = self::dateTimeMinute(self::dateTimeObject($bucketStart)->modify('-'.($duration - 1).' minutes'));
        $condition = $metric.' '.self::operator((string)$rule['operator']).' '.self::numberSql($rule['threshold']);

        return 'SELECT COUNT(*) AS `bucket_count`, '
            .'COALESCE(SUM(CASE WHEN '.$condition.' THEN 1 ELSE 0 END), 0) AS `breach_count`, '
            .'MIN(`bucket_start`) AS `first_bucket`, '
            .'MAX(`bucket_start`) AS `last_bucket`, '
            .'MAX('.$metric.') AS `max_value` '
            .'FROM `kpi_minute` '
            .'WHERE `bucket_start` >= '.self::sqlLiteral($db, $windowStart).' '
            .'AND `bucket_start` <= '.self::sqlLiteral($db, $bucketStart).';';
    }

    private static function evaluateRule($db, array $rule, string $bucketStart): array
    {
        $res = $db->sql_query(self::buildRuleWindowSql($db, $rule, $bucketStart));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        $bucketCount = (int)($row['bucket_count'] ?? 0);
        $breachCount = (int)($row['breach_count'] ?? 0);
        $duration = max(1, (int)$rule['duration_minutes']);

        return [
            'breaching' => $bucketCount >= $duration && $breachCount >= $duration,
            'bucket_count' => $bucketCount,
            'breach_count' => $breachCount,
            'first_bucket' => $row['first_bucket'] ?? null,
            'last_bucket' => $row['last_bucket'] ?? null,
            'max_value' => $row['max_value'] ?? null,
        ];
    }

    private static function message(array $rule, array $evaluation): string
    {
        $value = $evaluation['max_value'] === null ? 'n/a' : (string)$evaluation['max_value'];
        $unit = (string)($rule['unit'] ?? '');

        return sprintf(
            '%s: %s %s %s%s for %d minute(s), window %s -> %s.',
            $rule['label'],
            $rule['metric'],
            $rule['operator'],
            $value,
            $unit,
            (int)$rule['duration_minutes'],
            $evaluation['first_bucket'] ?? 'n/a',
            $evaluation['last_bucket'] ?? 'n/a'
        );
    }

    private static function metricIdentifier(string $metric): string
    {
        if (!isset(self::METRICS[$metric])) {
            throw new \InvalidArgumentException('Unsupported KPI metric: '.$metric);
        }

        return '`'.$metric.'`';
    }

    private static function operator(string $operator): string
    {
        if ($operator !== '>') {
            throw new \InvalidArgumentException('Unsupported KPI operator: '.$operator);
        }

        return $operator;
    }

    private static function numberSql($value): string
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('KPI threshold must be numeric.');
        }

        return strpos((string)$value, '.') === false ? (string)(int)$value : number_format((float)$value, 6, '.', '');
    }

    private static function intOption(array $overrides, string $key, string $constant, int $default): int
    {
        if (array_key_exists($key, $overrides)) {
            return max(1, (int)$overrides[$key]);
        }

        if (defined($constant)) {
            return max(1, (int)constant($constant));
        }

        return $default;
    }

    private static function floatOption(array $overrides, string $key, string $constant, float $default): float
    {
        if (array_key_exists($key, $overrides)) {
            return (float)$overrides[$key];
        }

        if (defined($constant)) {
            return (float)constant($constant);
        }

        return $default;
    }

    private static function loadConfig(): void
    {
        if (self::$configLoaded) {
            return;
        }

        self::$configLoaded = true;
        $paths = [];
        if (defined('CONFIG')) {
            $paths[] = rtrim((string)CONFIG, '/').'/kpi.config.php';
        }

        if (defined('ROOT')) {
            $paths[] = rtrim((string)ROOT, '/').'/configuration/kpi.config.php';
        } else {
            $paths[] = dirname(__DIR__, 3).'/configuration/kpi.config.php';
        }

        foreach (array_unique($paths) as $path) {
            if (is_file($path)) {
                require_once $path;
                return;
            }
        }
    }

    private static function dateTimeMinute(\DateTimeImmutable $date): string
    {
        return $date->setTime((int)$date->format('H'), (int)$date->format('i'), 0, 0)->format('Y-m-d H:i:s');
    }

    private static function dateTimeObject(string $value): \DateTimeImmutable
    {
        foreach (['Y-m-d H:i:s.u', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, trim($value));
            if ($date instanceof \DateTimeImmutable) {
                return $date;
            }
        }

        return new \DateTimeImmutable(trim($value) !== '' ? $value : 'now');
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
