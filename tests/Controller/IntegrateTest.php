<?php

declare(strict_types=1);

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
        $this->assertArrayNotHasKey('seconds_behind_master', $normalized);
        $this->assertArrayNotHasKey('seconds_behind_source', $normalized);
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

    public function testNormalizeSlaveMetricRowKeepsZeroLagAsAvailable(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Seconds_Behind_Source' => '0',
        ]);

        $this->assertSame('0', $normalized['seconds_behind_master']);
        $this->assertSame('0', $normalized['seconds_behind_source']);
    }

    public function testNormalizeSlaveMetricRowPreservesNullLagAsUnavailable(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Seconds_Behind_Source' => null,
        ]);

        $this->assertNull($normalized['seconds_behind_source']);
        $this->assertArrayNotHasKey('seconds_behind_master', $normalized);
    }

    public function testNormalizeSlaveMetricRowPreservesBlankLagAsUnavailable(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Seconds_Behind_Master' => ' ',
        ]);

        $this->assertSame(' ', $normalized['seconds_behind_master']);
        $this->assertArrayNotHasKey('seconds_behind_source', $normalized);
    }

    public function testNormalizeSlaveMetricRowReturnsNullForNonSlaveMetrics(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $this->assertNull($controller->exposeNormalizeSlaveMetricRow('status', ['threads_running' => 1]));
    }
}

final class TestableIntegrate extends Integrate
{
    public function exposeNormalizeSlaveMetricRow(string $typeMetrics, $value): ?array
    {
        return $this->normalizeSlaveMetricRow($typeMetrics, $value);
    }
}
