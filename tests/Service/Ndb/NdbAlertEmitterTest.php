<?php

declare(strict_types=1);

use App\Service\Ndb\NdbAlertEmitter;
use PHPUnit\Framework\TestCase;

final class NdbAlertEmitterTest extends TestCase
{
    public function testHealthyClusterEmitsNoAlerts(): void
    {
        $alerts = NdbAlertEmitter::computeAlerts(
            ['id' => 7, 'display_name' => 'ndb84'],
            [
                ['ndb_node_id' => 1, 'role' => 'mgmd', 'ip' => '10.0.0.1', 'status' => 'CONNECTED', 'node_group' => null],
                ['ndb_node_id' => 2, 'role' => 'data', 'ip' => '10.0.0.2', 'status' => 'STARTED',   'node_group' => 0,
                 'memory_used_mb' => 100, 'memory_total_mb' => 1024],
                ['ndb_node_id' => 3, 'role' => 'data', 'ip' => '10.0.0.3', 'status' => 'STARTED',   'node_group' => 0,
                 'memory_used_mb' => 100, 'memory_total_mb' => 1024],
                ['ndb_node_id' => 4, 'role' => 'sql',  'ip' => '10.0.0.4', 'status' => 'CONNECTED', 'node_group' => null],
            ]
        );

        $this->assertSame([], $alerts);
    }

    public function testOneDataNodeDownEmitsWarningButNotNodegroupLost(): void
    {
        $alerts = NdbAlertEmitter::computeAlerts(
            ['id' => 7, 'display_name' => 'ndb84'],
            [
                ['ndb_node_id' => 2, 'role' => 'data', 'ip' => '10.0.0.2', 'status' => 'STARTED',     'node_group' => 0],
                ['ndb_node_id' => 3, 'role' => 'data', 'ip' => '10.0.0.3', 'status' => 'NOT_STARTED', 'node_group' => 0],
            ]
        );

        $codes = array_column($alerts, 'code');
        $this->assertSame(['NDB_DATA_NODE_DOWN'], $codes);
        $this->assertSame('warning', $alerts[0]['severity']);
        $this->assertSame('node:3', $alerts[0]['entity']);
    }

    public function testNodeGroupLostEmitsCriticalNotPerNodeWarning(): void
    {
        $alerts = NdbAlertEmitter::computeAlerts(
            ['id' => 7, 'display_name' => 'ndb84'],
            [
                ['ndb_node_id' => 2, 'role' => 'data', 'ip' => '10.0.0.2', 'status' => 'NOT_STARTED', 'node_group' => 0],
                ['ndb_node_id' => 3, 'role' => 'data', 'ip' => '10.0.0.3', 'status' => 'NOT_STARTED', 'node_group' => 0],
            ]
        );

        $codes = array_column($alerts, 'code');
        $this->assertSame(['NDB_NODEGROUP_LOST'], $codes, 'fully-down NG should escalate to critical and not duplicate as per-node warnings');
        $this->assertSame('critical', $alerts[0]['severity']);
        $this->assertSame('ng:0', $alerts[0]['entity']);
    }

    public function testEveryMgmdDownEmitsNoArbitratorCritical(): void
    {
        $alerts = NdbAlertEmitter::computeAlerts(
            ['id' => 7, 'display_name' => 'ndb84'],
            [
                ['ndb_node_id' => 1, 'role' => 'mgmd', 'ip' => '10.0.0.1', 'status' => 'DISCONNECTED', 'node_group' => null],
                ['ndb_node_id' => 2, 'role' => 'mgmd', 'ip' => '10.0.0.5', 'status' => 'DISCONNECTED', 'node_group' => null],
            ]
        );

        $codes = array_column($alerts, 'code');
        $this->assertSame(['NDB_NO_ARBITRATOR'], $codes);
        $this->assertSame('critical', $alerts[0]['severity']);
    }

    public function testSqlNodeDownIsInfoOnlyWhenPeersUp(): void
    {
        $alerts = NdbAlertEmitter::computeAlerts(
            ['id' => 7, 'display_name' => 'ndb84'],
            [
                ['ndb_node_id' => 4, 'role' => 'sql', 'ip' => '10.0.0.4', 'status' => 'CONNECTED',    'node_group' => null],
                ['ndb_node_id' => 5, 'role' => 'sql', 'ip' => '10.0.0.5', 'status' => 'DISCONNECTED', 'node_group' => null],
            ]
        );

        $codes = array_column($alerts, 'code');
        $this->assertSame(['NDB_SQL_NODE_DOWN'], $codes);
        $this->assertSame('info', $alerts[0]['severity']);
    }

    public function testMemoryThresholdsMapToWarningAndCritical(): void
    {
        $alerts = NdbAlertEmitter::computeAlerts(
            ['id' => 7, 'display_name' => 'ndb84'],
            [
                // 92% → pressure
                ['ndb_node_id' => 2, 'role' => 'data', 'ip' => '10.0.0.2', 'status' => 'STARTED', 'node_group' => 0,
                 'memory_used_mb' => 920, 'memory_total_mb' => 1000],
                // 99% → full
                ['ndb_node_id' => 3, 'role' => 'data', 'ip' => '10.0.0.3', 'status' => 'STARTED', 'node_group' => 0,
                 'memory_used_mb' => 990, 'memory_total_mb' => 1000],
            ]
        );

        $byCode = [];
        foreach ($alerts as $a) {
            $byCode[$a['code']] = $a;
        }
        $this->assertArrayHasKey('NDB_MEMORY_PRESSURE', $byCode);
        $this->assertArrayHasKey('NDB_MEMORY_FULL', $byCode);
        $this->assertSame('warning', $byCode['NDB_MEMORY_PRESSURE']['severity']);
        $this->assertSame('critical', $byCode['NDB_MEMORY_FULL']['severity']);
    }

    public function testFingerprintIsStableAcrossCallsAndPerEntity(): void
    {
        $a = NdbAlertEmitter::fingerprintHex(7, 'NDB_DATA_NODE_DOWN', 'node:3');
        $b = NdbAlertEmitter::fingerprintHex(7, 'NDB_DATA_NODE_DOWN', 'node:3');
        $c = NdbAlertEmitter::fingerprintHex(7, 'NDB_DATA_NODE_DOWN', 'node:5');

        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
        $this->assertSame(64, strlen($a), 'sha256 hex must be 64 chars');
    }

    public function testBuildUpsertSqlPinsTheExpectedShape(): void
    {
        $db = new NdbAlertEmitterFakeDb();
        $sql = NdbAlertEmitter::buildUpsertSql(
            $db,
            7,
            [
                'code'     => 'NDB_DATA_NODE_DOWN',
                'severity' => 'warning',
                'title'    => 'NDB data node 3 is NOT_STARTED',
                'message'  => 'data node 3 down',
                'entity'   => 'node:3',
                'context'  => ['ndb_node_id' => 3],
            ],
            'ndb84'
        );

        $this->assertStringContainsString("INSERT INTO `pmc_event_alert`", $sql);
        $this->assertStringContainsString("'system', 'alert', 'warning', 'open'", $sql);
        $this->assertStringContainsString("'ndb_collector'", $sql);
        $this->assertStringContainsString("'ndb_cluster'", $sql);
        $this->assertStringContainsString("UNHEX('", $sql);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
        // Re-emitting must reopen a previously-resolved alert.
        $this->assertStringContainsString(
            "`status` = IF(`status` IN ('resolved','closed'), 'open', `status`)",
            $sql
        );
    }
}

final class NdbAlertEmitterFakeDb
{
    public function sql_real_escape_string(string $v): string
    {
        return addslashes($v);
    }
    public function sql_query(string $sql)
    {
        return true;
    }
}
