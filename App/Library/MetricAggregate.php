<?php

namespace App\Library;

final class MetricAggregate
{
    public const DISPLAY_LAST = 'last';
    public const DISPLAY_AVG = 'avg';
    public const STATS_NONE = 'none';
    public const STATS_STDDEV = 'stddev';
    public const STATS_STDDEV_MIN_MAX = 'stddev_min_max';

    /**
     * @return array<string,array<string,int|string>>
     */
    public static function getResolutions(): array
    {
        return [
            '10s' => [
                'seconds' => 10,
                'table' => 'aggregate_metric_10s',
                'bootstrap_lookback' => 600,
                'overlap' => 120,
            ],
            '1m' => [
                'seconds' => 60,
                'table' => 'aggregate_metric_1m',
                'bootstrap_lookback' => 86400,
                'overlap' => 3600,
            ],
            '10m' => [
                'seconds' => 600,
                'table' => 'aggregate_metric_10m',
                'bootstrap_lookback' => 30 * 86400,
                'overlap' => 12 * 3600,
            ],
            '1h' => [
                'seconds' => 3600,
                'table' => 'aggregate_metric_1h',
                'bootstrap_lookback' => 30 * 86400,
                'overlap' => 2 * 86400,
            ],
        ];
    }

    /**
     * @return array<int,array<string,string>>
     */
    public static function getRawNumericSources(): array
    {
        return [
            ['scope' => 'general', 'table' => 'ts_value_general_int', 'series_field' => "''", 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'general', 'table' => 'ts_value_general_double', 'series_field' => "''", 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'calculated', 'table' => 'ts_value_calculated_int', 'series_field' => "''", 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'calculated', 'table' => 'ts_value_calculated_double', 'series_field' => "''", 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'slave', 'table' => 'ts_value_slave_int', 'series_field' => '`connection_name`', 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'slave', 'table' => 'ts_value_slave_double', 'series_field' => '`connection_name`', 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'digest', 'table' => 'ts_value_digest_int', 'series_field' => 'CAST(`id_ts_mysql_query` AS CHAR)', 'value_expr' => 'CAST(`value` AS DOUBLE)'],
            ['scope' => 'digest', 'table' => 'ts_value_digest_double', 'series_field' => 'CAST(`id_ts_mysql_query` AS CHAR)', 'value_expr' => 'CAST(`value` AS DOUBLE)'],
        ];
    }

    /**
     * @param array<string,mixed> $metric
     */
    public static function resolveDisplayPolicy(array $metric): string
    {
        $type = strtoupper(trim((string)($metric['type'] ?? '')));
        $from = strtolower(trim((string)($metric['from'] ?? '')));
        $name = strtolower(trim((string)($metric['name'] ?? '')));

        if (!in_array($type, ['INT', 'DOUBLE'], true)) {
            return self::DISPLAY_LAST;
        }

        if ($from === 'variables') {
            return self::DISPLAY_LAST;
        }

        if (preg_match('/(^com_|^handler_|_total$|^bytes_|^questions$|^queries$|^connections$|^aborted_|^created_tmp_|^opened_|^sort_|^select_|^innodb_.*(reads|writes|pages|requests|fsyncs|row_lock|rows|waits)|^binlog_|^network_.*_total$|^disk_.*_total$|^system_.*_total$)/', $name) === 1) {
            return self::DISPLAY_LAST;
        }

        return self::DISPLAY_AVG;
    }

    /**
     * @param array<string,mixed> $metric
     */
    public static function resolveStatisticsPolicy(array $metric): string
    {
        $type = strtoupper(trim((string)($metric['type'] ?? '')));

        if (!in_array($type, ['INT', 'DOUBLE'], true)) {
            return self::STATS_NONE;
        }

        return self::STATS_STDDEV_MIN_MAX;
    }

    public static function floorBucketStart(string $datetime, int $bucketSeconds): string
    {
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return $datetime;
        }

        $floored = (int)(floor($timestamp / $bucketSeconds) * $bucketSeconds);

        return date('Y-m-d H:i:s', $floored);
    }

    /**
     * @param array<int,float|int> $samples
     * @return array<string,float|int|null>
     */
    public static function computeRawStats(array $samples): array
    {
        if (empty($samples)) {
            return [
                'sample_count' => 0,
                'value_last' => null,
                'value_avg' => null,
                'value_stddev' => null,
                'value_min' => null,
                'value_max' => null,
                'value_sum' => null,
                'value_sum_squares' => null,
            ];
        }

        $count = count($samples);
        $sum = 0.0;
        $sumSquares = 0.0;
        $min = null;
        $max = null;

        foreach ($samples as $sample) {
            $value = (float)$sample;
            $sum += $value;
            $sumSquares += $value * $value;
            $min = $min === null ? $value : min($min, $value);
            $max = $max === null ? $value : max($max, $value);
        }

        $avg = $sum / $count;
        $variance = max(($sumSquares / $count) - ($avg * $avg), 0.0);

        return [
            'sample_count' => $count,
            'value_last' => (float)end($samples),
            'value_avg' => $avg,
            'value_stddev' => sqrt($variance),
            'value_min' => $min,
            'value_max' => $max,
            'value_sum' => $sum,
            'value_sum_squares' => $sumSquares,
        ];
    }

    /**
     * @param array<int,array<string,float|int|null>> $rows
     * @return array<string,float|int|null>
     */
    public static function rollupAggregateRows(array $rows): array
    {
        if (empty($rows)) {
            return [
                'sample_count' => 0,
                'value_last' => null,
                'value_avg' => null,
                'value_stddev' => null,
                'value_min' => null,
                'value_max' => null,
                'value_sum' => null,
                'value_sum_squares' => null,
            ];
        }

        $sampleCount = 0;
        $sum = 0.0;
        $sumSquares = 0.0;
        $min = null;
        $max = null;
        $last = null;

        foreach ($rows as $row) {
            $count = (int)($row['sample_count'] ?? 0);
            $sampleCount += $count;
            $sum += (float)($row['value_sum'] ?? 0.0);
            $sumSquares += (float)($row['value_sum_squares'] ?? 0.0);
            $min = $min === null ? ($row['value_min'] ?? null) : min((float)$min, (float)($row['value_min'] ?? $min));
            $max = $max === null ? ($row['value_max'] ?? null) : max((float)$max, (float)($row['value_max'] ?? $max));
            $last = $row['value_last'] ?? $last;
        }

        if ($sampleCount === 0) {
            return [
                'sample_count' => 0,
                'value_last' => $last,
                'value_avg' => null,
                'value_stddev' => null,
                'value_min' => $min,
                'value_max' => $max,
                'value_sum' => null,
                'value_sum_squares' => null,
            ];
        }

        $avg = $sum / $sampleCount;
        $variance = max(($sumSquares / $sampleCount) - ($avg * $avg), 0.0);

        return [
            'sample_count' => $sampleCount,
            'value_last' => $last,
            'value_avg' => $avg,
            'value_stddev' => sqrt($variance),
            'value_min' => $min,
            'value_max' => $max,
            'value_sum' => $sum,
            'value_sum_squares' => $sumSquares,
        ];
    }
}
