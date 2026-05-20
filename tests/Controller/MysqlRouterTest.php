<?php

declare(strict_types=1);

use App\Controller\MysqlRouter;
use PHPUnit\Framework\TestCase;

final class MysqlRouterTest extends TestCase
{
    public function testExtractRouteConfigFindsNestedBindInformation(): void
    {
        $payload = [
            'routeName' => 'bootstrap_ro',
            'config' => [
                'bindAddress' => '0.0.0.0',
                'bindPort' => 6447,
                'destinations' => 'metadata-cache://prodCluster/?role=SECONDARY',
            ],
        ];

        $config = MysqlRouter::extractRouteConfig($payload);

        $this->assertSame('0.0.0.0', $config['bindAddress']);
        $this->assertSame(6447, $config['bindPort']);
    }

    public function testExtractMetadataNamesParsesMetadataEndpointPayload(): void
    {
        $metadataNames = MysqlRouter::extractMetadataNames([
            'items' => [
                ['name' => 'bootstrap'],
                ['name' => 'bootstrap'],
                ['name' => 'drCluster'],
            ],
        ]);

        $this->assertSame(['bootstrap', 'drCluster'], $metadataNames);
    }

    public function testRouterEndpointDiscoveryRealignsProxyAndMonitoringFlags(): void
    {
        $sql = MysqlRouter::buildMarkRouterEndpointMonitoredSql(179);

        $this->assertSame(
            'UPDATE mysql_server SET is_proxy = 1, is_monitored = 1'
            . ' WHERE id = 179 AND (is_proxy != 1 OR is_monitored != 1)',
            $sql
        );
    }

    public function testRouterEndpointMonitoringBackfillMigrationTargetsLinkedEndpoints(): void
    {
        $migration = (string) file_get_contents(
            dirname(__DIR__, 2) . '/sql/incremental_v2/20260507_mysqlrouter_endpoint_monitoring.sql'
        );

        $this->assertStringContainsString('INNER JOIN mysqlrouter_server__mysql_server', $migration);
        $this->assertStringContainsString('SET a.is_proxy = 1,', $migration);
        $this->assertStringContainsString('a.is_monitored = 1', $migration);
        $this->assertStringContainsString('a.is_deleted = 0', $migration);
    }
}
