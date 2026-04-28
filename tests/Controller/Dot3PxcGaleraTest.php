<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

final class Dot3PxcGaleraTest extends TestCase
{
    private const DOT3_INFORMATION_ID = 990442;

    private array $originalGalera = [];
    private array $originalBuildGalera = [];

    protected function setUp(): void
    {
        $this->originalGalera = Dot3::$galera;
        $this->originalBuildGalera = Dot3::$build_galera;

        Dot3::$galera = [];
        Dot3::$build_galera = [];
    }

    protected function tearDown(): void
    {
        Dot3::$galera = $this->originalGalera;
        Dot3::$build_galera = $this->originalBuildGalera;
        unset(Dot3::$information[self::DOT3_INFORMATION_ID]);
    }

    public function testPxcClusterWithoutWsrepOnIsRenderedAsGaleraCluster(): void
    {
        $information = [
            'mapping' => [
                '10.68.68.223:3306' => 252,
                '10.68.68.224:3306' => 253,
                '10.68.68.225:3306' => 254,
            ],
            'servers' => [
                252 => self::pxcServer(252, 'gr80-pxc-1', '10.68.68.223', '56978276-41de-11f1-b108-033ed72a1e49'),
                253 => self::pxcServer(253, 'gr80-pxc-2', '10.68.68.224', 'e94cf2e4-41dd-11f1-b34c-9e9ac4503d3c'),
                254 => self::pxcServer(254, 'gr80-pxc-3', '10.68.68.225', '296965f3-41de-11f1-8d87-032a681a3832'),
            ],
        ];

        Dot3::$information[self::DOT3_INFORMATION_ID] = ['information' => $information];

        $dot3 = self::newDot3WithoutConstructor();
        $groups = $dot3->generateGroupGalera($information);

        $this->assertEqualsCanonicalizing([252, 253, 254], $groups[252]);

        $dot3->buildGaleraCluster([self::DOT3_INFORMATION_ID, [252, 253, 254]]);

        $this->assertCount(1, Dot3::$build_galera);
        $cluster = reset(Dot3::$build_galera);

        $this->assertSame('gr80-pxc', $cluster['name']);
        $this->assertSame(3, $cluster['members']);
        $this->assertSame(3, $cluster['node_available']);
        $this->assertSame('GALERA_AVAILABLE', $cluster['config']);
        $this->assertArrayHasKey(0, $cluster['segment']);
        $this->assertSame('SEGMENT_OK', $cluster['segment'][0]['theme']);
        $this->assertEqualsCanonicalizing([252, 253, 254], array_keys($cluster['node'][0]));
    }

    public function testStandaloneMysqlWithoutWsrepMembershipIsNotRenderedAsGaleraCluster(): void
    {
        $information = [
            'mapping' => [
                '10.68.68.240:3306' => 300,
            ],
            'servers' => [
                300 => [
                    'id_mysql_server' => '300',
                    'hostname' => 'standalone-mysql',
                    'display_name' => 'standalone-mysql',
                    'ip_real' => '10.68.68.240',
                    'port_real' => '3306',
                    'version' => '8.0.45',
                    'version_comment' => 'MySQL Community Server - GPL',
                    'mysql_available' => '1',
                ],
            ],
        ];

        $dot3 = self::newDot3WithoutConstructor();

        $this->assertSame([], $dot3->generateGroupGalera($information));
        $this->assertSame([], Dot3::$galera);
    }

    /**
     * @return array<string,string>
     */
    private static function pxcServer(int $id, string $name, string $ip, string $gcommUuid): array
    {
        return [
            'id_mysql_server' => (string)$id,
            'hostname' => $name,
            'display_name' => $name,
            'ip' => $name,
            'ip_real' => $ip,
            'port' => '3306',
            'port_real' => '3306',
            'version' => '8.0.45-36.1',
            'version_comment' => 'Percona XtraDB Cluster (GPL), Release rel36, Revision f4ae3e8, WSREP version 26.1.4.3',
            'wsrep_cluster_address' => 'gcomm://10.68.68.223,10.68.68.224,10.68.68.225',
            'wsrep_node_address' => $ip,
            'wsrep_cluster_name' => 'gr80-pxc',
            'wsrep_provider_options' => 'base_host = ' . $ip . '; gmcast.segment = 0; pc.weight = 1;',
            'wsrep_sst_method' => 'xtrabackup-v2',
            'wsrep_desync' => 'OFF',
            'wsrep_local_state' => '4',
            'wsrep_local_state_comment' => 'Synced',
            'wsrep_cluster_status' => 'Primary',
            'wsrep_incoming_addresses' => '10.68.68.225:3306,10.68.68.223:3306,10.68.68.224:3306',
            'wsrep_cluster_state_uuid' => '48b38047-41dd-11f1-bd7f-da49e8133615',
            'wsrep_gcomm_uuid' => $gcommUuid,
            'wsrep_local_state_uuid' => '48b38047-41dd-11f1-bd7f-da49e8133615',
            'wsrep_provider_version' => '4.24(c71acc5)',
            'wsrep_cluster_size' => '3',
            'wsrep_slave_threads' => '8',
            'mysql_available' => '1',
        ];
    }

    private static function newDot3WithoutConstructor(): Dot3
    {
        $reflection = new ReflectionClass(Dot3::class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
