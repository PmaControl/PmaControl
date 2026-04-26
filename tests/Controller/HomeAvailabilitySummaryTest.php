<?php

declare(strict_types=1);

use App\Controller\Home;
use PHPUnit\Framework\TestCase;

final class HomeAvailabilitySummaryTest extends TestCase
{
    public function testAvailabilitySummaryCountsEveryMonitoredServer(): void
    {
        $summary = Home::buildAvailabilitySummary(
            [10 => true, 11 => true, 12 => true],
            [
                10 => ['mysql_available' => '1'],
                11 => ['mysql_available' => '0', 'mysql_error' => 'Connection refused'],
            ]
        );

        $this->assertSame(1, $summary['available']);
        $this->assertSame(2, $summary['unavailable']);
        $this->assertSame('Connection refused', $summary['unavailable_servers'][11]);
        $this->assertSame('No metric reported', $summary['unavailable_servers'][12]);
    }

    public function testAvailabilitySummaryIgnoresUnmonitoredExtractionRows(): void
    {
        $summary = Home::buildAvailabilitySummary(
            [10 => true],
            [
                10 => ['mysql_available' => '1'],
                99 => ['mysql_available' => '0', 'mysql_error' => 'Ignored'],
            ]
        );

        $this->assertSame(1, $summary['available']);
        $this->assertSame(0, $summary['unavailable']);
        $this->assertSame([], $summary['unavailable_servers']);
    }
}
