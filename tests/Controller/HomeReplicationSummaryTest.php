<?php

declare(strict_types=1);

use App\Controller\Home;
use PHPUnit\Framework\TestCase;

final class HomeReplicationSummaryTest extends TestCase
{
    public function testReplicationLagPrefersSecondsBehindSource(): void
    {
        $lag = Home::getReplicationLag([
            'seconds_behind_master' => '0',
            'seconds_behind_source' => '12',
        ]);

        $this->assertSame(12, $lag);
    }

    public function testReplicationLagFallsBackToSecondsBehindMaster(): void
    {
        $lag = Home::getReplicationLag([
            'seconds_behind_master' => '7',
        ]);

        $this->assertSame(7, $lag);
    }

    public function testReplicationLagFallsBackWhenSourceLagIsEmpty(): void
    {
        $lag = Home::getReplicationLag([
            'seconds_behind_master' => '5',
            'seconds_behind_source' => ' ',
        ]);

        $this->assertSame(5, $lag);
    }

    public function testReplicationLagIgnoresNullLagValue(): void
    {
        $lag = Home::getReplicationLag([
            'seconds_behind_source' => 'NULL',
        ]);

        $this->assertNull($lag);
    }

    public function testReplicationClassificationUsesSourceLag(): void
    {
        $status = Home::classifyReplicationChannel([
            'slave_io_running' => 'Yes',
            'slave_sql_running' => 'Yes',
            'seconds_behind_master' => '0',
            'seconds_behind_source' => '3',
        ]);

        $this->assertSame('lag', $status);
    }

    public function testReplicationClassificationKeepsErrorAndStoppedStates(): void
    {
        $this->assertSame('error', Home::classifyReplicationChannel([
            'slave_io_running' => 'Yes',
            'slave_sql_running' => 'No',
        ]));

        $this->assertSame('stopped', Home::classifyReplicationChannel([
            'slave_io_running' => 'No',
            'slave_sql_running' => 'No',
        ]));
    }
}
