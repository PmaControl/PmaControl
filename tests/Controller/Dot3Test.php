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

    public function testDetectsMysqlRouterFromStandardPorts()
    {
        $server = [
            'display_name' => 'prodCluster-router-1-ro',
            'port_real' => '6447',
            'is_proxysql' => '0',
        ];

        $this->assertTrue(Dot3::isMysqlRouterNode($server));
    }

    public function testDetectsMysqlRouterFromCollectedPayload()
    {
        $server = [
            'display_name' => 'APP-1',
            'mysqlrouter_routes' => '{"items":[{"route":"bootstrap_ro","bind_port":6447}]}',
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
            'mysqlrouter_routes' => '{"items":[]}',
        ];

        $this->assertFalse(Dot3::isMysqlRouterNode($server));
    }
}
