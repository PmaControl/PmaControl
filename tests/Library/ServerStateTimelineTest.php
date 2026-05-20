<?php

namespace App\Tests\Library;

use App\Library\ServerStateTimeline;
use PHPUnit\Framework\TestCase;

class ServerStateTimelineTest extends TestCase
{
    public function testAggregateMinuteValuesReturnsZeroWhenAnyZeroIsPresent(): void
    {
        $this->assertSame(0, ServerStateTimeline::aggregateMinuteValues([1, 1, 0, null]));
    }

    public function testAggregateMinuteValuesReturnsOneWhenOnlyOneValuesExist(): void
    {
        $this->assertSame(1, ServerStateTimeline::aggregateMinuteValues([1, 1, null]));
    }

    public function testAggregateMinuteValuesReturnsTwoWhenReadOnlyExistsWithoutZero(): void
    {
        $this->assertSame(2, ServerStateTimeline::aggregateMinuteValues([1, 2, null]));
    }

    public function testAggregateMinuteValuesReturnsNullWhenNoValueExists(): void
    {
        $this->assertNull(ServerStateTimeline::aggregateMinuteValues([null, '', null]));
    }

    public function testAggregateRowsByFiveSecondBucketsPrioritizesZeroWithinBucket(): void
    {
        $rows = [
            ['date' => '2026-03-19 10:00:01', 'value' => '1'],
            ['date' => '2026-03-19 10:00:03', 'value' => '0'],
            ['date' => '2026-03-19 10:00:07', 'value' => '1'],
        ];

        $this->assertSame(0, ServerStateTimeline::aggregateRowsByFiveSecondBuckets($rows));
    }

    public function testAggregateRowsByFiveSecondBucketsAggregatesCurrentTenSecondWindow(): void
    {
        $rows = [
            ['date' => '2026-03-19 10:00:12', 'value' => '1'],
            ['date' => '2026-03-19 10:00:17', 'value' => '1'],
        ];

        $this->assertSame(1, ServerStateTimeline::aggregateRowsByFiveSecondBuckets($rows));
    }

    public function testNormalizeRangeKeepsPresetModeByDefault(): void
    {
        $range = ServerStateTimeline::normalizeRange(['range' => '6h']);

        $this->assertSame('preset', $range['mode']);
        $this->assertSame('6h', $range['preset']);
        $this->assertTrue($range['live_enabled']);
    }

    public function testNormalizeRangeEnablesCustomModeForValidDateRange(): void
    {
        $range = ServerStateTimeline::normalizeRange([
            'range_mode' => 'custom',
            'range' => '1h',
            'start' => '2026-03-19T08:00',
            'end' => '2026-03-19T09:00',
        ]);

        $this->assertSame('custom', $range['mode']);
        $this->assertFalse($range['live_enabled']);
        $this->assertSame('2026-03-19T08:00', $range['start_value']);
        $this->assertSame('2026-03-19T09:00', $range['end_value']);
    }

    public function testNormalizeRangeKeepsPresetWhenPrefilledDatesAreSubmittedWithoutCustomMode(): void
    {
        $range = ServerStateTimeline::normalizeRange([
            'range' => '6h',
            'range_mode' => 'preset',
            'start' => '2026-03-19T08:00',
            'end' => '2026-03-19T09:00',
        ]);

        $this->assertSame('preset', $range['mode']);
        $this->assertSame('6h', $range['preset']);
        $this->assertTrue($range['live_enabled']);
    }

    public function testComputeServerRatioExposesCoverageAndMissingBuckets(): void
    {
        $ratio = $this->invokePrivate('computeServerRatio', [[1, 1, 1, null, null]]);

        $this->assertSame(0, $ratio['zero']);
        $this->assertSame(3, $ratio['one']);
        $this->assertSame(0, $ratio['two']);
        $this->assertSame(3, $ratio['signal']);
        $this->assertSame(3, $ratio['availability_signal']);
        $this->assertSame(2, $ratio['missing']);
        $this->assertSame(5, $ratio['total']);
        $this->assertSame('3 / 3', $ratio['availability_label']);
        $this->assertSame('3 / 5', $ratio['coverage_label']);
        $this->assertSame('2 missing', $ratio['missing_label']);
    }

    public function testComputeServerRatioSeparatesAvailabilityFromCoverage(): void
    {
        $ratio = $this->invokePrivate('computeServerRatio', [[1, 1, 0, null]]);

        $this->assertSame(1, $ratio['zero']);
        $this->assertSame(2, $ratio['one']);
        $this->assertSame(3, $ratio['signal']);
        $this->assertSame(3, $ratio['availability_signal']);
        $this->assertSame(1, $ratio['missing']);
        $this->assertSame(4, $ratio['total']);
        $this->assertSame('2 / 3', $ratio['availability_label']);
        $this->assertSame('3 / 4', $ratio['coverage_label']);
    }

    public function testComputeServerRatioReportsEmptyCoverageWhenAllBucketsAreMissing(): void
    {
        $ratio = $this->invokePrivate('computeServerRatio', [[null, null, null]]);

        $this->assertSame(0, $ratio['signal']);
        $this->assertSame(0, $ratio['availability_signal']);
        $this->assertSame(3, $ratio['missing']);
        $this->assertSame(3, $ratio['total']);
        $this->assertSame('0 / 0', $ratio['availability_label']);
        $this->assertSame('0 / 3', $ratio['coverage_label']);
    }

    public function testDetectStaleReturnsTrueWhenLatestExpectedBucketsAreMissing(): void
    {
        $this->assertTrue($this->invokePrivate('detectStale', [[1, 1, null, null, null]]));
    }

    public function testDetectStaleReturnsFalseWhenOnlyTwoLatestBucketsAreMissing(): void
    {
        $this->assertFalse($this->invokePrivate('detectStale', [[1, 1, 1, null, null]]));
    }

    public function testDetectStaleReturnsFalseWhenLatestBucketHasSignal(): void
    {
        $this->assertFalse($this->invokePrivate('detectStale', [[null, null, null, 1]]));
    }

    public function testComputeStatsExposesMissingBucketCount(): void
    {
        $stats = $this->invokePrivate('computeStats', [[
            ['values' => [1, 0, null]],
            ['values' => [2, null]],
        ]]);

        $this->assertSame(1, $stats['zero']);
        $this->assertSame(1, $stats['one']);
        $this->assertSame(1, $stats['two']);
        $this->assertSame(3, $stats['signal']);
        $this->assertSame(2, $stats['missing']);
        $this->assertSame(5, $stats['total']);
    }

    private function invokePrivate(string $methodName, array $arguments)
    {
        $reflection = new \ReflectionClass(ServerStateTimeline::class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs(null, $arguments);
    }
}
