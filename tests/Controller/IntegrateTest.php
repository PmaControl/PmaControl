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
