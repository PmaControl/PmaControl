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
}
