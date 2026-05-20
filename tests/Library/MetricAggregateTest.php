<?php

namespace Tests\Library;

use App\Library\MetricAggregate;
use PHPUnit\Framework\TestCase;

final class MetricAggregateTest extends TestCase
{
    public function testResolveDisplayPolicyReturnsLastForCounterLikeMetrics(): void
    {
        $metric = [
            'name' => 'questions',
            'type' => 'INT',
            'from' => 'status',
        ];

        $this->assertSame(MetricAggregate::DISPLAY_LAST, MetricAggregate::resolveDisplayPolicy($metric));
    }

    public function testResolveDisplayPolicyReturnsAvgForGaugeLikeMetrics(): void
    {
        $metric = [
            'name' => 'threads_running',
            'type' => 'INT',
            'from' => 'status',
        ];

        $this->assertSame(MetricAggregate::DISPLAY_AVG, MetricAggregate::resolveDisplayPolicy($metric));
    }

    public function testResolveDisplayPolicyKeepsVariablesOnLast(): void
    {
        $metric = [
            'name' => 'innodb_buffer_pool_size',
            'type' => 'INT',
            'from' => 'variables',
        ];

        $this->assertSame(MetricAggregate::DISPLAY_LAST, MetricAggregate::resolveDisplayPolicy($metric));
    }

    public function testResolveStatisticsPolicyReturnsStddevForNumericMetrics(): void
    {
        $metric = [
            'name' => 'threads_running',
            'type' => 'DOUBLE',
        ];

        $this->assertSame(MetricAggregate::STATS_STDDEV_MIN_MAX, MetricAggregate::resolveStatisticsPolicy($metric));
    }

    public function testFloorBucketStartRoundsDownToTenSeconds(): void
    {
        $this->assertSame('2026-03-21 12:34:50', MetricAggregate::floorBucketStart('2026-03-21 12:34:56', 10));
    }

    public function testComputeRawStatsReturnsExpectedValues(): void
    {
        $stats = MetricAggregate::computeRawStats([2, 4, 4, 4, 5, 5, 7, 9]);

        $this->assertSame(8, $stats['sample_count']);
        $this->assertSame(9.0, $stats['value_last']);
        $this->assertEqualsWithDelta(5.0, (float) $stats['value_avg'], 0.0001);
        $this->assertEqualsWithDelta(2.0, (float) $stats['value_stddev'], 0.0001);
        $this->assertSame(2.0, $stats['value_min']);
        $this->assertSame(9.0, $stats['value_max']);
        $this->assertEqualsWithDelta(40.0, (float) $stats['value_sum'], 0.0001);
        $this->assertEqualsWithDelta(232.0, (float) $stats['value_sum_squares'], 0.0001);
    }

    public function testRollupAggregateRowsKeepsLastValueAndExactStddev(): void
    {
        $rows = [
            [
                'sample_count' => 3,
                'value_last' => 4.0,
                'value_min' => 2.0,
                'value_max' => 4.0,
                'value_sum' => 10.0,
                'value_sum_squares' => 36.0,
            ],
            [
                'sample_count' => 2,
                'value_last' => 7.0,
                'value_min' => 5.0,
                'value_max' => 7.0,
                'value_sum' => 12.0,
                'value_sum_squares' => 74.0,
            ],
        ];

        $stats = MetricAggregate::rollupAggregateRows($rows);

        $this->assertSame(5, $stats['sample_count']);
        $this->assertSame(7.0, $stats['value_last']);
        $this->assertEqualsWithDelta(4.4, (float) $stats['value_avg'], 0.0001);
        $this->assertEqualsWithDelta(1.6248077, (float) $stats['value_stddev'], 0.0001);
        $this->assertSame(2.0, $stats['value_min']);
        $this->assertSame(7.0, $stats['value_max']);
        $this->assertEqualsWithDelta(22.0, (float) $stats['value_sum'], 0.0001);
        $this->assertEqualsWithDelta(110.0, (float) $stats['value_sum_squares'], 0.0001);
    }
}
