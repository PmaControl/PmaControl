<?php

declare(strict_types=1);

use App\Library\BinlogGroupCommit;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1196 — pin the value→level + drift logic for the
 * /slave/show *Binlog group commit* card.
 */
final class BinlogGroupCommitTest extends TestCase
{
    public function testMariadbDefaultsLandOnMariadbVarNamesAndOkLevel(): void
    {
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '20', 'binlog_commit_wait_usec' => '5000'],
            ['binlog_commit_wait_count' => '20', 'binlog_commit_wait_usec' => '5000'],
            'mariadb',
            'mariadb'
        );
        $this->assertCount(2, $rows);

        $count = $rows[0];
        $this->assertSame('Count threshold', $count['label']);
        $this->assertSame('binlog_commit_wait_count', $count['slave']['var']);
        $this->assertSame('binlog_commit_wait_count', $count['master']['var']);
        $this->assertSame('20', $count['slave']['value']);
        $this->assertSame(BinlogGroupCommit::LEVEL_OK, $count['slave']['level']);
        $this->assertFalse($count['drift']);

        $usec = $rows[1];
        $this->assertSame('Max wait (μs)', $usec['label']);
        $this->assertSame('binlog_commit_wait_usec', $usec['slave']['var']);
        $this->assertSame('5000', $usec['slave']['value']);
        $this->assertSame(BinlogGroupCommit::LEVEL_OK, $usec['slave']['level']);
    }

    public function testMysqlFamilyUsesGroupCommitSyncVarNames(): void
    {
        $rows = BinlogGroupCommit::rows(
            ['binlog_group_commit_sync_no_delay_count' => '0', 'binlog_group_commit_sync_delay' => '0'],
            ['binlog_group_commit_sync_no_delay_count' => '0', 'binlog_group_commit_sync_delay' => '0'],
            'mysql',
            'mysql'
        );
        $this->assertSame('binlog_group_commit_sync_no_delay_count', $rows[0]['slave']['var']);
        $this->assertSame('binlog_group_commit_sync_delay',          $rows[1]['slave']['var']);
        // 0 = no batching → info, not warn.
        $this->assertSame(BinlogGroupCommit::LEVEL_INFO, $rows[0]['slave']['level']);
        $this->assertSame(BinlogGroupCommit::LEVEL_INFO, $rows[1]['slave']['level']);
    }

    public function testCountLevelBoundaries(): void
    {
        // count = 0 → info, 1..100 → ok, > 100 → warn.
        foreach ([
            '0'   => BinlogGroupCommit::LEVEL_INFO,
            '1'   => BinlogGroupCommit::LEVEL_OK,
            '20'  => BinlogGroupCommit::LEVEL_OK,
            '100' => BinlogGroupCommit::LEVEL_OK,
            '101' => BinlogGroupCommit::LEVEL_WARN,
            '500' => BinlogGroupCommit::LEVEL_WARN,
        ] as $val => $expected) {
            $rows = BinlogGroupCommit::rows(
                ['binlog_commit_wait_count' => $val],
                [],
                'mariadb',
                'unknown'
            );
            $this->assertSame($expected, $rows[0]['slave']['level'], "count=$val");
        }
    }

    public function testUsecLevelBoundaries(): void
    {
        // usec = 0 → info, 1..50 000 → ok, > 50 000 → warn.
        foreach ([
            '0'      => BinlogGroupCommit::LEVEL_INFO,
            '1'      => BinlogGroupCommit::LEVEL_OK,
            '5000'   => BinlogGroupCommit::LEVEL_OK,
            '50000'  => BinlogGroupCommit::LEVEL_OK,
            '50001'  => BinlogGroupCommit::LEVEL_WARN,
            '200000' => BinlogGroupCommit::LEVEL_WARN,
        ] as $val => $expected) {
            $rows = BinlogGroupCommit::rows(
                ['binlog_commit_wait_usec' => $val],
                [],
                'mariadb',
                'unknown'
            );
            $this->assertSame($expected, $rows[1]['slave']['level'], "usec=$val");
        }
    }

    public function testUnknownFamilyMarksCellNa(): void
    {
        $rows = BinlogGroupCommit::rows(
            [], [], 'unknown', 'unknown'
        );
        foreach ($rows as $r) {
            $this->assertSame(BinlogGroupCommit::LEVEL_UNKNOWN, $r['slave']['level']);
            $this->assertSame('n/a', $r['slave']['label']);
            $this->assertSame(BinlogGroupCommit::LEVEL_UNKNOWN, $r['master']['level']);
        }
    }

    public function testDriftFiresAtFiveTimesFactor(): void
    {
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '20'],
            ['binlog_commit_wait_count' => '100'],   // 5x → drift
            'mariadb',
            'mariadb'
        );
        $this->assertTrue($rows[0]['drift']);
        $this->assertStringContainsString('mis-aligned', $rows[0]['drift_reason']);

        // 4x → no drift (below the threshold).
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '20'],
            ['binlog_commit_wait_count' => '79'],
            'mariadb',
            'mariadb'
        );
        $this->assertFalse($rows[0]['drift']);
    }

    public function testZeroVsNonZeroFiresDriftWarning(): void
    {
        // batching ON one side, OFF the other ⇒ replica apply pace
        // diverges from source ⇒ drift.
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '0'],
            ['binlog_commit_wait_count' => '20'],
            'mariadb',
            'mariadb'
        );
        $this->assertTrue($rows[0]['drift']);
        $this->assertStringContainsString('disabled while the other does not', $rows[0]['drift_reason']);
    }

    public function testNoDriftWhenHalfTheDataIsMissing(): void
    {
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '20'],
            [],
            'mariadb',
            'mariadb'
        );
        // Master cell is unknown → don't fire a false-positive drift.
        $this->assertFalse($rows[0]['drift']);
    }

    public function testCrossFamilyAlwaysFiresDrift(): void
    {
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '20'],
            ['binlog_group_commit_sync_no_delay_count' => '20'],
            'mariadb',
            'mysql'
        );
        $this->assertTrue($rows[0]['drift']);
        $this->assertStringContainsString('different MySQL families', $rows[0]['drift_reason']);
    }

    public function testTooltipsExistAndMentionDefaults(): void
    {
        $rows = BinlogGroupCommit::rows(
            ['binlog_commit_wait_count' => '20', 'binlog_commit_wait_usec' => '5000'],
            [],
            'mariadb',
            'unknown'
        );
        foreach ($rows as $r) {
            $this->assertNotEmpty($r['tooltip']);
            $this->assertStringContainsString('default', $r['tooltip']);
        }
    }

    public function testVariableKeysCoverBothFamilies(): void
    {
        $keys = BinlogGroupCommit::variableKeys();
        $this->assertContains('binlog_commit_wait_count', $keys);
        $this->assertContains('binlog_commit_wait_usec', $keys);
        $this->assertContains('binlog_group_commit_sync_no_delay_count', $keys);
        $this->assertContains('binlog_group_commit_sync_delay', $keys);
    }
}
