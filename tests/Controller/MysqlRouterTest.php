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
}
