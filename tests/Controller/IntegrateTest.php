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

    public function testNormalizeSlaveMetricRowMapsSourceHostAndSourcePortToLegacyFields(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Source_Host' => 'mysql-primary.example.net',
            'Source_Port' => 3310,
            'Replica_IO_Running' => 'Yes',
            'Replica_SQL_Running' => 'Yes',
            'Seconds_Behind_Source' => '3',
            'Source_SSL_Allowed' => 'Yes',
            'Source_SSL_Cipher' => 'TLS_AES_256_GCM_SHA384',
            'Source_TLS_Version' => 'TLSv1.3',
        ]);

        $this->assertSame('mysql-primary.example.net', $normalized['master_host']);
        $this->assertSame(3310, $normalized['master_port']);
        $this->assertSame('mysql-primary.example.net', $normalized['source_host']);
        $this->assertSame(3310, $normalized['source_port']);
        $this->assertSame('Yes', $normalized['master_ssl_allowed']);
        $this->assertSame('Yes', $normalized['source_ssl_allowed']);
        $this->assertSame('TLS_AES_256_GCM_SHA384', $normalized['master_ssl_cipher']);
        $this->assertSame('TLS_AES_256_GCM_SHA384', $normalized['source_ssl_cipher']);
        $this->assertSame('TLSv1.3', $normalized['master_tls_version']);
        $this->assertSame('TLSv1.3', $normalized['source_tls_version']);
        $this->assertSame('3', $normalized['seconds_behind_master']);
        $this->assertSame('3', $normalized['seconds_behind_source']);
        $this->assertSame('Yes', $normalized['slave_io_running']);
        $this->assertSame('Yes', $normalized['slave_sql_running']);
        $this->assertSame('Yes', $normalized['replica_io_running']);
        $this->assertSame('Yes', $normalized['replica_sql_running']);
    }

    public function testNormalizeSlaveMetricRowMapsLegacySslStateToSourceField(): void
    {
        $controller = new TestableIntegrate('Controller', 'View', []);

        $normalized = $controller->exposeNormalizeSlaveMetricRow('slave', [
            'Master_SSL_Allowed' => 'No',
            'Master_SSL_Cipher' => 'DHE-RSA-AES256-SHA',
            'Master_TLS_Version' => 'TLSv1.2',
        ]);

        $this->assertSame('No', $normalized['master_ssl_allowed']);
        $this->assertSame('No', $normalized['source_ssl_allowed']);
        $this->assertSame('DHE-RSA-AES256-SHA', $normalized['master_ssl_cipher']);
        $this->assertSame('DHE-RSA-AES256-SHA', $normalized['source_ssl_cipher']);
        $this->assertSame('TLSv1.2', $normalized['master_tls_version']);
        $this->assertSame('TLSv1.2', $normalized['source_tls_version']);
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
