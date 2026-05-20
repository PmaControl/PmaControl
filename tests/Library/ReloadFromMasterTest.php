<?php

declare(strict_types=1);

use App\Library\ReloadFromMaster;
use PHPUnit\Framework\TestCase;

final class ReloadFromMasterTest extends TestCase
{
    /**
     * Shared "everything passes" fixtures — individual tests override the
     * one field they want to fail / warn on so the expected behaviour is
     * obvious by diff.
     *
     * @return array<string,mixed>
     */
    private function happyInputs(): array
    {
        return [
            'slaveRow'  => ['is_out_of_service' => 1, 'replica_lag_sla_seconds' => 30],
            'masterRow' => ['id' => 7],
            'slaveStatus' => [
                'Slave_SQL_Running' => 'No',
                'last_sql_error_timestamp' => date('Y-m-d H:i:s', time() - 3600),
                'Seconds_Behind_Source' => null,
            ],
            'sourceCoverage' => ['status' => 'safe'],
            'diskStats' => ['slave_free_bytes' => 200 * 1024 * 1024 * 1024, 'master_datadir_bytes' => 100 * 1024 * 1024 * 1024],
            'sshProbe' => ['ok' => true, 'command' => "ssh -o BatchMode=yes mysql@10.0.0.2 'echo ok'", 'stderr' => ''],
            'versions' => ['slave' => '10.11.5-MariaDB', 'master' => '10.11.5-MariaDB', 'slave_type' => 'mariadb', 'master_type' => 'mariadb'],
        ];
    }

    private function evalPreflight(array $in): array
    {
        return ReloadFromMaster::evaluatePreflight(
            $in['slaveRow'],
            $in['masterRow'],
            $in['slaveStatus'],
            $in['sourceCoverage'],
            $in['diskStats'],
            $in['sshProbe'],
            $in['versions']
        );
    }

    private function statusOf(array $result, string $key): string
    {
        foreach ($result['conditions'] as $c) {
            if ($c['key'] === $key) {
                return $c['status'];
            }
        }
        $this->fail("missing condition key={$key}");
    }

    public function testHappyPathAllGreenBothProceduresEligible(): void
    {
        $r = $this->evalPreflight($this->happyInputs());
        $this->assertTrue($r['physical_eligible']);
        $this->assertTrue($r['logical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'out_of_service'));
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'version_match'));
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'replica_stale'));
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'ssh_reachable'));
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'disk_space'));
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'source_coverage'));
    }

    public function testOutOfServiceFlagMissingDisablesEverything(): void
    {
        $in = $this->happyInputs();
        $in['slaveRow']['is_out_of_service'] = 0;
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertFalse($r['logical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'out_of_service'));
    }

    public function testMajorVersionMismatchDisablesPhysicalOnly(): void
    {
        $in = $this->happyInputs();
        $in['versions']['master'] = '8.0.36-MySQL';
        $in['versions']['master_type'] = 'mysql';
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertTrue($r['logical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'version_match'));
    }

    public function testMajorVersionMatchAcrossDifferentMinorVersionsIsOk(): void
    {
        $in = $this->happyInputs();
        $in['versions']['master'] = '10.11.2-MariaDB';
        $in['versions']['slave']  = '10.11.9-MariaDB';
        $r = $this->evalPreflight($in);
        $this->assertTrue($r['physical_eligible']);
    }

    public function testServerTypeMismatchSameMajorStillBlocksPhysical(): void
    {
        $in = $this->happyInputs();
        $in['versions']['slave_type']  = 'mariadb';
        $in['versions']['master_type'] = 'mysql';
        // major "10.11" doesn't even exist for upstream MySQL but be defensive
        $in['versions']['master'] = '10.11.5';
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertTrue($r['logical_eligible']);
    }

    public function testHealthyReplicaIsRefusedNothingToReload(): void
    {
        $in = $this->happyInputs();
        $in['slaveStatus'] = [
            'Slave_SQL_Running' => 'Yes',
            'Seconds_Behind_Source' => 2,
        ];
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertFalse($r['logical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'replica_stale'));
    }

    public function testReplicaFarBehindMasterIsAValidReloadTrigger(): void
    {
        $in = $this->happyInputs();
        $in['slaveStatus'] = [
            'Slave_SQL_Running' => 'Yes',
            'Seconds_Behind_Source' => 30 * ReloadFromMaster::LAG_BLOCKER_MULTIPLIER + 1,
        ];
        $r = $this->evalPreflight($in);
        $this->assertTrue($r['physical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'replica_stale'));
    }

    public function testSqlStoppedShorterThanFiveMinutesIsNotABlockerButStillRefused(): void
    {
        $in = $this->happyInputs();
        $in['slaveStatus'] = [
            'Slave_SQL_Running' => 'No',
            'last_sql_error_timestamp' => date('Y-m-d H:i:s', time() - 30),
            'Seconds_Behind_Source' => 5,
        ];
        $r = $this->evalPreflight($in);
        // not stale-enough to justify reload → fail (consistent with
        // "no reason to wipe a datadir")
        $this->assertFalse($r['physical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'replica_stale'));
    }

    public function testSshProbeFailureDisablesEverythingAndSurfacesCommand(): void
    {
        $in = $this->happyInputs();
        $in['sshProbe'] = ['ok' => false, 'command' => "ssh -o BatchMode=yes mysql@10.0.0.2 'echo ok'", 'stderr' => 'Permission denied (publickey).'];
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertFalse($r['logical_eligible']);
        $cond = $this->findCondition($r, 'ssh_reachable');
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $cond['status']);
        $this->assertStringContainsString('Permission denied', $cond['message']);
        $this->assertStringContainsString('ssh -o BatchMode=yes', $cond['message']);
    }

    public function testDiskSpaceShortBlocksReload(): void
    {
        $in = $this->happyInputs();
        $in['diskStats']['slave_free_bytes'] = 50 * 1024 * 1024 * 1024;
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertFalse($r['logical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'disk_space'));
    }

    public function testDiskSpaceExactlyAtRatioIsAllowed(): void
    {
        $in = $this->happyInputs();
        $masterB = 100 * 1024 * 1024 * 1024;
        $in['diskStats']['master_datadir_bytes'] = $masterB;
        $in['diskStats']['slave_free_bytes']     = (int) ceil($masterB * ReloadFromMaster::DISK_HEADROOM_RATIO);
        $r = $this->evalPreflight($in);
        $this->assertTrue($r['physical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_OK, $this->statusOf($r, 'disk_space'));
    }

    public function testDiskSpaceInconclusiveIsAWarnNotABlocker(): void
    {
        $in = $this->happyInputs();
        $in['diskStats'] = ['slave_free_bytes' => null, 'master_datadir_bytes' => null];
        $r = $this->evalPreflight($in);
        $this->assertTrue($r['physical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_WARN, $this->statusOf($r, 'disk_space'));
    }

    public function testSourceCoverageAtRiskIsWarnAndDoesNotBlock(): void
    {
        $in = $this->happyInputs();
        $in['sourceCoverage'] = ['status' => 'at_risk', 'reason' => 'replica position purged on master'];
        $r = $this->evalPreflight($in);
        $this->assertTrue($r['physical_eligible']);
        $this->assertTrue($r['logical_eligible']);
        $cond = $this->findCondition($r, 'source_coverage');
        $this->assertSame(ReloadFromMaster::STATUS_WARN, $cond['status']);
        $this->assertStringContainsString('purged', $cond['message']);
    }

    public function testSourceCoverageNullProducesWarnNotFail(): void
    {
        $in = $this->happyInputs();
        $in['sourceCoverage'] = null;
        $r = $this->evalPreflight($in);
        $this->assertSame(ReloadFromMaster::STATUS_WARN, $this->statusOf($r, 'source_coverage'));
        $this->assertTrue($r['physical_eligible']);
    }

    public function testMajorVersionParsing(): void
    {
        $this->assertSame('10.11', ReloadFromMaster::majorVersion('10.11.5-MariaDB-1:10.11.5+maria~ubu2204'));
        $this->assertSame('8.0',   ReloadFromMaster::majorVersion('8.0.36-0ubuntu0.22.04.1'));
        $this->assertSame('5.7',   ReloadFromMaster::majorVersion('5.7.44-log'));
        $this->assertNull(ReloadFromMaster::majorVersion(''));
        $this->assertNull(ReloadFromMaster::majorVersion('xx'));
    }

    public function testHumanBytesFormatting(): void
    {
        $this->assertSame('512.00 B',   ReloadFromMaster::humanBytes(512));
        $this->assertSame('1.00 KiB',   ReloadFromMaster::humanBytes(1024));
        $this->assertSame('1.50 MiB',   ReloadFromMaster::humanBytes((int) (1.5 * 1024 * 1024)));
        $this->assertSame('2.00 GiB',   ReloadFromMaster::humanBytes(2 * 1024 * 1024 * 1024));
    }

    public function testMultipleBlockersAreAllReported(): void
    {
        $in = $this->happyInputs();
        $in['slaveRow']['is_out_of_service'] = 0;
        $in['sshProbe'] = ['ok' => false, 'command' => 'ssh slave', 'stderr' => 'timeout'];
        $in['diskStats']['slave_free_bytes'] = 1;
        $r = $this->evalPreflight($in);
        $this->assertFalse($r['physical_eligible']);
        $this->assertFalse($r['logical_eligible']);
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'out_of_service'));
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'ssh_reachable'));
        $this->assertSame(ReloadFromMaster::STATUS_FAIL, $this->statusOf($r, 'disk_space'));
    }

    public function testEligibilityShapeAlwaysContainsSixConditions(): void
    {
        $r = $this->evalPreflight($this->happyInputs());
        $keys = array_column($r['conditions'], 'key');
        $this->assertEqualsCanonicalizing(
            ['out_of_service', 'version_match', 'replica_stale', 'ssh_reachable', 'disk_space', 'source_coverage'],
            $keys
        );
    }

    private function findCondition(array $r, string $key): array
    {
        foreach ($r['conditions'] as $c) {
            if ($c['key'] === $key) {
                return $c;
            }
        }
        $this->fail("missing key {$key}");
    }
}
