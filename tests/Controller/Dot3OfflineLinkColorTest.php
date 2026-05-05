<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

final class Dot3OfflineLinkColorTest extends TestCase
{
    private const DOT3_INFORMATION_ID = 990003;

    private array $originalConfig = [];
    private array $originalBuildServer = [];
    private array $originalBuildMs = [];

    protected function setUp(): void
    {
        $this->originalConfig = Dot3::$config;
        $this->originalBuildServer = Dot3::$build_server;
        $this->originalBuildMs = Dot3::$build_ms;

        Dot3::$config = array_merge(Dot3::$config, [
            'VIP_LINK_ACTIVE' => ['color' => '#008000', 'style' => 'filled', 'options' => []],
            'VIP_LINK_PREVIOUS' => ['color' => '#9e9e9e', 'style' => 'dashed', 'options' => []],
            'PROXYSQL_ONLINE' => ['color' => '#008000', 'style' => 'filled', 'options' => []],
        ]);

        Dot3::$id_dot3_information = self::DOT3_INFORMATION_ID;
        Dot3::$information[self::DOT3_INFORMATION_ID] = [
            'information' => [
                'mapping' => [
                    '10.0.0.2:3306' => 10,
                    '10.0.0.31:3306' => 11,
                ],
                'tunnel' => [],
                'servers' => [
                    10 => [
                        'id_mysql_server' => 10,
                        'display_name' => 'db-primary',
                        'hostname' => 'db-primary',
                        'ip' => '10.0.0.2',
                        'ip_real' => '10.0.0.2',
                        'port' => '3306',
                        'port_real' => '3306',
                        'mysql_available' => '1',
                    ],
                    11 => [
                        'id_mysql_server' => 11,
                        'display_name' => 'db-maxscale',
                        'hostname' => 'db-maxscale',
                        'ip' => '10.0.0.31',
                        'ip_real' => '10.0.0.31',
                        'port' => '3306',
                        'port_real' => '3306',
                        'mysql_available' => '1',
                    ],
                    20 => [
                        'id_mysql_server' => 20,
                        'display_name' => 'vip-app',
                        'hostname' => 'vip-app',
                        'ip' => '10.0.0.50',
                        'ip_real' => '10.0.0.50',
                        'port' => '3306',
                        'port_real' => '3306',
                        'mysql_available' => '0',
                        'is_vip' => '1',
                        'destination_id' => 10,
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Dot3::$config = $this->originalConfig;
        Dot3::$build_server = $this->originalBuildServer;
        Dot3::$build_ms = $this->originalBuildMs;
        unset(Dot3::$information[self::DOT3_INFORMATION_ID]);
        Dot3::$id_dot3_information = null;
    }

    public function testOfflineVipLinksTurnRed(): void
    {
        $dot3 = $this->newDot3WithoutConstructor();

        Dot3::$build_ms = [];
        $dot3->buildLinkVIP([self::DOT3_INFORMATION_ID, [20]]);

        $this->assertCount(1, Dot3::$build_ms);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['color']);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['options']['color']);
    }

    public function testOfflineProxySqlLinksTurnRed(): void
    {
        $dot3 = $this->newDot3WithoutConstructor();

        Dot3::$build_ms = [];
        Dot3::$build_server = [
            30 => [
                'id_mysql_server' => 30,
                'display_name' => 'offline-proxysql',
                'mysql_available' => '0',
                'is_proxysql' => '1',
                'mysql_servers' => [
                    ['hostgroup_id' => '10', 'hostname' => '10.0.0.2', 'port' => '3306', 'status' => 'ONLINE'],
                ],
                'mysql_replication_hostgroups' => [
                    ['writer_hostgroup' => '10', 'reader_hostgroup' => '20'],
                ],
            ],
        ];

        $dot3->linkHostGroup([self::DOT3_INFORMATION_ID, []]);

        $this->assertCount(1, Dot3::$build_ms);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['color']);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['options']['color']);
    }

    public function testOfflineMaxScaleLinksTurnRed(): void
    {
        $dot3 = $this->newDot3WithoutConstructor();

        Dot3::$build_ms = [];
        Dot3::$build_server = [
            40 => [
                'id_mysql_server' => 40,
                'display_name' => 'offline-maxscale',
                'mysql_available' => '0',
                'is_maxscale' => '1',
                'ip_real' => '10.0.0.40',
                'port_real' => '4006',
                'maxscale_listeners' => [
                    'data' => [[
                        'id' => 'Read-Write-Listener',
                        'attributes' => [
                            'parameters' => [
                                'address' => '10.0.0.40',
                                'port' => 4006,
                            ],
                        ],
                        'relationships' => [
                            'services' => [
                                'data' => [['id' => 'Read-Write-Service', 'type' => 'services']],
                            ],
                        ],
                    ]],
                ],
                'maxscale_services' => [
                    'data' => [[
                        'id' => 'Read-Write-Service',
                        'attributes' => [
                            'state' => 'Started',
                            'router' => 'readwritesplit',
                            'statistics' => [
                                'active_operations' => 0,
                                'connections' => 5,
                            ],
                        ],
                        'relationships' => [
                            'servers' => [
                                'data' => [['id' => 'db-primary', 'type' => 'servers']],
                            ],
                        ],
                    ]],
                ],
                'maxscale_servers' => [
                    'data' => [[
                        'id' => 'db-primary',
                        'attributes' => [
                            'state' => 'Master, Running',
                            'parameters' => [
                                'address' => '10.0.0.31',
                                'port' => 3306,
                            ],
                            'statistics' => [
                                'connections' => 2,
                                'max_connections' => 10,
                            ],
                        ],
                        'relationships' => [
                            'monitors' => [
                                'data' => [['id' => 'MariaDB-Monitor', 'type' => 'monitors']],
                            ],
                        ],
                    ]],
                ],
                'maxscale_monitors' => [
                    'data' => [[
                        'id' => 'MariaDB-Monitor',
                        'attributes' => [
                            'state' => 'Running',
                            'module' => 'mariadbmon',
                        ],
                    ]],
                ],
            ],
        ];

        $dot3->linkMaxScale([self::DOT3_INFORMATION_ID, []]);

        $this->assertCount(1, Dot3::$build_ms);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['color']);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['options']['color']);
    }

    public function testOfflineMysqlRouterLinksTurnRed(): void
    {
        $dot3 = $this->newDot3WithoutConstructor();

        Dot3::$build_ms = [];
        Dot3::$build_server = [
            50 => [
                'id_mysql_server' => 50,
                'display_name' => 'offline-router',
                'mysql_available' => '0',
                'port_real' => '6446',
                'version_comment' => 'MySQL Community Server - GPL',
                'mysqlrouter_metadata_config' => [
                    'bootstrap' => [
                        'groupReplicationId' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                        'nodes' => [[
                            'hostname' => '10.0.0.31',
                            'port' => 3306,
                        ]],
                    ],
                ],
                'mysqlrouter_routes' => [
                    'items' => [[
                        'bindAddress' => '10.0.0.60',
                        'bindPort' => 6446,
                        'destinations_payload' => [
                            'items' => [[
                                'address' => '10.0.0.31',
                                'port' => 3306,
                            ]],
                        ],
                    ]],
                ],
                'mysqlrouter_route_match' => [
                    'destinations_payload' => [
                        'items' => [[
                            'address' => '10.0.0.31',
                            'port' => 3306,
                        ]],
                    ],
                    'config' => [
                        'connection_sharing' => '0',
                    ],
                ],
            ],
        ];

        $dot3->linkMysqlRouter([self::DOT3_INFORMATION_ID]);

        $this->assertCount(1, Dot3::$build_ms);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['color']);
        $this->assertSame('#FF0000', Dot3::$build_ms[0]['options']['color']);
    }

    private function newDot3WithoutConstructor(): Dot3
    {
        $reflection = new ReflectionClass(Dot3::class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
