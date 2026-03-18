<?php
use PHPUnit\Framework\TestCase;
use App\Controller\Dot3;

class Dot3Test extends TestCase
{
    public function setUp(): void
    {
        dot3::$id_dot3_information = 1;
    }

    public function testMissingDot3InformationId()
    {
        dot3::$id_dot3_information = null;
        $this->expectExceptionMessageMatches('/PMACONTROL-1000/');
        dot3::getTunnel(['test' => 'value']);
    }

    public function testInvalidParameter()
    {
        $this->expectExceptionMessageMatches('/PMACONTROL-1001/');
        dot3::getTunnel([]);
    }

    public function testDoesNotDetectMysqlRouterFromStandardPortsAlone()
    {
        $server = [
            'display_name' => 'prodCluster-router-1-ro',
            'port_real' => '6447',
            'is_proxysql' => '0',
        ];

        $this->assertFalse(Dot3::isMysqlRouterNode($server));
    }

    public function testDoesNotDetectMysqlRouterFromExplicitSignatureAlone()
    {
        $server = [
            'display_name' => 'prodCluster mysql router 1',
            'port_real' => '6447',
            'is_proxysql' => '0',
        ];

        $this->assertFalse(Dot3::isMysqlRouterNode($server));
    }

    public function testDetectsMysqlRouterFromMetadataConfig()
    {
        $server = [
            'display_name' => 'APP-1',
            'mysqlrouter_metadata_config' => '{"bootstrap":{"groupReplicationId":"b62a1be2-1caa-11f1-895d-bc24110e621d","nodes":[{"hostname":"10.68.68.131","port":3306}]}}',
            'is_proxysql' => '0',
        ];

        $this->assertTrue(Dot3::isMysqlRouterNode($server));
    }

    public function testDoesNotClassifyProxySqlAsMysqlRouter()
    {
        $server = [
            'display_name' => 'proxysql-1',
            'port_real' => '6033',
            'is_proxysql' => '1',
            'mysqlrouter_metadata_config' => '{"bootstrap":{"groupReplicationId":"b62a1be2-1caa-11f1-895d-bc24110e621d","nodes":[{"hostname":"10.68.68.131","port":3306}]}}',
        ];

        $this->assertFalse(Dot3::isMysqlRouterNode($server));
    }

    public function testBuildVipDestinationLabelIncludesResolvedEndpointAndTunnel(): void
    {
        Dot3::$id_dot3_information = 991000;
        Dot3::$information[991000] = [
            'information' => [
                'tunnel' => [
                    '127.0.0.1:8001' => '192.168.114.104:3306',
                ],
            ],
        ];

        $reflection = new ReflectionClass(Dot3::class);
        $dot3 = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('buildVipDestinationLabel');
        $method->setAccessible(true);

        $label = $method->invoke($dot3, [
            112 => [
                'display_name' => 'FRDC1-DR-DTA01L',
                'ip_real' => '127.0.0.1',
                'port_real' => '8001',
            ],
        ], 112);

        $this->assertSame(
            ['label' => '🔀192.168.114.104:3306'],
            $label
        );

        unset(Dot3::$information[991000]);
        Dot3::$id_dot3_information = 1;
    }
}
