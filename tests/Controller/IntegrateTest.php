<?php

declare(strict_types=1);

if (!defined('TMP')) {
    define('TMP', sys_get_temp_dir().'/');
}

use App\Controller\Integrate;
use PHPUnit\Framework\TestCase;

final class IntegrateTest extends TestCase
{
    public function testNormalizeSlaveMetricRowAddsDefaultConnectionName(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Master_Host' => '10.105.1.11',
            'Master_Port' => 3306,
            'Slave_IO_Running' => 'Yes',
            'Slave_SQL_Running' => 'Yes',
        ]);

        $this->assertSame('', $normalized['connection_name']);
        $this->assertSame('10.105.1.11', $normalized['master_host']);
        $this->assertSame(3306, $normalized['master_port']);
        $this->assertSame('Yes', $normalized['slave_io_running']);
        $this->assertSame('Yes', $normalized['slave_sql_running']);
        $this->assertSame('0', $normalized['seconds_behind_master']);
    }

    public function testNormalizeSlaveMetricRowKeepsExistingConnectionName(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'connection_name' => 'replica_a',
            'seconds_behind_master' => '7',
        ]);

        $this->assertSame('replica_a', $normalized['connection_name']);
        $this->assertSame('7', $normalized['seconds_behind_master']);
    }

    public function testNormalizeSlaveMetricRowMapsSourceHostAndSourcePortToLegacyFields(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Source_Host' => 'mysql-primary.example.net',
            'Source_Port' => 3310,
            'Replica_IO_Running' => 'Yes',
            'Replica_SQL_Running' => 'Yes',
            'Seconds_Behind_Source' => '3',
        ]);

        $this->assertSame('mysql-primary.example.net', $normalized['master_host']);
        $this->assertSame(3310, $normalized['master_port']);
        $this->assertSame('mysql-primary.example.net', $normalized['source_host']);
        $this->assertSame(3310, $normalized['source_port']);
        $this->assertSame('3', $normalized['seconds_behind_master']);
        $this->assertSame('3', $normalized['seconds_behind_source']);
        $this->assertSame('Yes', $normalized['slave_io_running']);
        $this->assertSame('Yes', $normalized['slave_sql_running']);
        $this->assertSame('Yes', $normalized['replica_io_running']);
        $this->assertSame('Yes', $normalized['replica_sql_running']);
    }

    public function testNormalizeSlaveMetricRowReturnsNullForNonSlaveMetrics(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $this->assertNull($controller->exposeNormalizeSlaveMetricRow('status', ['threads_running' => 1]));
    }

    public function testPayloadLockPreventsSecondOwnerForSameFile(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);
        $file = sys_get_temp_dir().'/pmacontrol-integrate-test::phpunit_integrate_lock_'.getmypid();

        $firstLock = $controller->exposeAcquireIntegratePayloadLock($file);
        $this->assertIsArray($firstLock);

        try {
            $secondLock = $controller->exposeAcquireIntegratePayloadLock($file);
            $this->assertNull($secondLock);
        } finally {
            $controller->exposeReleaseIntegratePayloadLock($firstLock);
        }

        $thirdLock = $controller->exposeAcquireIntegratePayloadLock($file);
        $this->assertIsArray($thirdLock);
        $controller->exposeReleaseIntegratePayloadLock($thirdLock);
        @unlink($thirdLock['path']);
    }

    public function testBuildTimeSeriesInsertSqlIsIdempotentOnPrimaryKeyCollision(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $sql = $controller->exposeBuildTimeSeriesInsertSql(
            'ts_value_general_int',
            ['id_mysql_server', 'id_ts_variable', 'date', 'value'],
            ['(4499,238,"2026-04-15 23:03:31","1")']
        );

        $this->assertStringStartsWith(
            'INSERT INTO `ts_value_general_int` (`id_mysql_server`,`id_ts_variable`,`date`,`value`) VALUES ',
            $sql
        );
        $this->assertStringContainsString(
            'ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);',
            $sql
        );
    }
}

final class TestableIntegrate extends Integrate
{
    public function exposeNormalizeSlaveMetricRow(string $typeMetrics, $value): ?array
    {
        return $this->normalizeSlaveMetricRow($typeMetrics, $value);
    }

    public function exposeAcquireIntegratePayloadLock(string $file): ?array
    {
        return $this->acquireIntegratePayloadLock($file);
    }

    public function exposeReleaseIntegratePayloadLock(?array $lock): void
    {
        $this->releaseIntegratePayloadLock($lock);
    }

    public function exposeBuildTimeSeriesInsertSql(string $table, array $columns, array $values): string
    {
        return $this->buildTimeSeriesInsertSql($table, $columns, $values);
    }
}
