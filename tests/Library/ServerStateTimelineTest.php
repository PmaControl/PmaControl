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

    public function testFillLatestMissingBucketFromCurrentStatusOnlyTouchesLastNullBucket(): void
    {
        $reflection = new \ReflectionClass(ServerStateTimeline::class);
        $method = $reflection->getMethod('fillLatestMissingBucketFromCurrentStatus');
        $method->setAccessible(true);

        $values = [1, null, null];
        $result = $method->invoke(null, $values, 1);

        $this->assertSame([1, null, 1], $result);
    }
}
