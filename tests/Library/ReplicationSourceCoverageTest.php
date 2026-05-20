<?php

declare(strict_types=1);

use App\Library\ReplicationSourceCoverage;
use PHPUnit\Framework\TestCase;

final class ReplicationSourceCoverageTest extends TestCase
{
    public function testFileModeSafeWhenReplicaFileStillOnMaster(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            ['Master_Log_File' => 'mysql-bin.000042', 'Relay_Master_Log_File' => 'mysql-bin.000041'],
            ['mysql-bin.000040', 'mysql-bin.000041', 'mysql-bin.000042', 'mysql-bin.000043'],
            null
        );
        $this->assertTrue($r['safe']);
        $this->assertSame(ReplicationSourceCoverage::STATUS_SAFE, $r['status']);
        $this->assertSame(ReplicationSourceCoverage::MODE_FILE, $r['mode']);
    }

    public function testFileModeAtRiskWhenReadFilePurgedOnMaster(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            ['Master_Log_File' => 'mysql-bin.000005'],
            ['mysql-bin.000040', 'mysql-bin.000041', 'mysql-bin.000042'],
            null
        );
        $this->assertFalse($r['safe']);
        $this->assertSame(ReplicationSourceCoverage::STATUS_AT_RISK, $r['status']);
        $this->assertStringContainsString('mysql-bin.000005', $r['reason']);
    }

    public function testFileModeAtRiskWhenExecFilePurgedButReadStillThere(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            [
                'Master_Log_File'       => 'mysql-bin.000042',
                'Relay_Master_Log_File' => 'mysql-bin.000005',
            ],
            ['mysql-bin.000040', 'mysql-bin.000041', 'mysql-bin.000042'],
            null
        );
        $this->assertFalse($r['safe']);
        $this->assertStringContainsString('mysql-bin.000005', $r['reason']);
    }

    public function testFileModeUnknownWhenMasterFilesEmpty(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            ['Master_Log_File' => 'mysql-bin.000042'],
            [],
            null
        );
        $this->assertFalse($r['safe']);
        $this->assertSame(ReplicationSourceCoverage::STATUS_UNKNOWN, $r['status']);
    }

    public function testFileModeUnknownWhenReplicaHasNoFile(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            [],
            ['mysql-bin.000042'],
            null
        );
        $this->assertSame(ReplicationSourceCoverage::STATUS_UNKNOWN, $r['status']);
    }

    public function testGtidModeIsActivatedByAutoPosition(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            [
                'Auto_Position'      => '1',
                'Retrieved_Gtid_Set' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-100',
            ],
            [],
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-5'
        );
        $this->assertSame(ReplicationSourceCoverage::MODE_GTID, $r['mode']);
    }

    public function testGtidModeIsActivatedByUsingGtid(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            [
                'Using_Gtid'         => 'Slave_Pos',
                'Retrieved_Gtid_Set' => '0-1-100',
            ],
            [],
            '0-1-5'
        );
        $this->assertSame(ReplicationSourceCoverage::MODE_GTID, $r['mode']);
    }

    public function testGtidSafeWhenRetrievedNotOverlappingPurged(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            [
                'Auto_Position'      => '1',
                'Retrieved_Gtid_Set' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:100-200',
            ],
            [],
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-50'
        );
        $this->assertTrue($r['safe']);
        $this->assertSame(ReplicationSourceCoverage::STATUS_SAFE, $r['status']);
    }

    public function testGtidAtRiskWhenRetrievedIntersectsPurged(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            [
                'Auto_Position'      => '1',
                'Retrieved_Gtid_Set' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-100',
            ],
            [],
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-50'
        );
        $this->assertFalse($r['safe']);
        $this->assertSame(ReplicationSourceCoverage::STATUS_AT_RISK, $r['status']);
        $this->assertNotNull($r['overlap_with_purged']);
        $this->assertStringContainsString('1-50', $r['overlap_with_purged']);
    }

    public function testGtidUnknownWhenRetrievedEmpty(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            ['Auto_Position' => '1', 'Retrieved_Gtid_Set' => ''],
            [],
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-50'
        );
        $this->assertSame(ReplicationSourceCoverage::STATUS_UNKNOWN, $r['status']);
    }

    public function testGtidUnknownWhenPurgedUnavailable(): void
    {
        $r = ReplicationSourceCoverage::evaluate(
            ['Auto_Position' => '1', 'Retrieved_Gtid_Set' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa:1-50'],
            [],
            null
        );
        $this->assertSame(ReplicationSourceCoverage::STATUS_UNKNOWN, $r['status']);
    }

    public function testMariadbGtidGrammarHandledCleanly(): void
    {
        // MariaDB grammar: `domain-server-lo-hi` for ranges. Retrieved
        // spans 1..1000 on (0,1) and (0,2):1..50. Purged on (0,1)
        // covers 1..100 — overlap on (0,1) must be detected.
        $r = ReplicationSourceCoverage::evaluate(
            [
                'Using_Gtid'         => 'Slave_Pos',
                'Retrieved_Gtid_Set' => '0-1-1-1000,0-2-1-50',
            ],
            [],
            '0-1-1-100'
        );
        $this->assertFalse($r['safe']);
        $this->assertNotNull($r['overlap_with_purged']);
    }
}
