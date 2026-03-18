<?php

declare(strict_types=1);

use App\Controller\Dot3;
use App\Library\Graphviz;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

if (!defined('LINK')) {
    define('LINK', '/');
}

if (!function_exists('__')) {
    function __($value)
    {
        return $value;
    }
}

final class GraphvizOfflineRenderingTest extends TestCase
{
    private const DOT3_INFORMATION_ID = 990002;

    private array $originalConfig = [];

    protected function setUp(): void
    {
        $this->originalConfig = Dot3::$config;
        Dot3::$config = array_merge(Dot3::$config, [
            'SERVER_CONFIG' => ['background' => '#0f172a', 'color' => '#ffffff'],
            'PROXYSQL_ONLINE' => ['color' => '#008000', 'font' => '#ffffff', 'style' => 'filled'],
            'PROXYSQL_CONFIG' => ['color' => '#ff9800', 'font' => '#000000', 'style' => 'filled'],
            'MAXSCALE_RUNNING' => ['background' => '#008000', 'color' => '#ffffff'],
            'MAXSCALE_UNSYNC' => ['background' => '#337ab7', 'color' => '#ffffff'],
            'MAXSCALE_DOWN' => ['background' => '#424242', 'color' => '#ffffff'],
        ]);

        Dot3::$id_dot3_information = self::DOT3_INFORMATION_ID;
        Dot3::$information[self::DOT3_INFORMATION_ID] = [
            'information' => [
                'tunnel' => [
                    '127.0.0.254:65535' => '127.0.0.254:65535',
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Dot3::$config = $this->originalConfig;
        unset(Dot3::$information[self::DOT3_INFORMATION_ID]);
        Dot3::$id_dot3_information = null;
    }

    #[DataProvider('offlineServerProvider')]
    public function testOfflineBoxTurnsHeaderRedWhileGreyRowsStayGrey(array $server, string $titleText): void
    {
        $dot = Graphviz::generateServer($server);

        $this->assertStringContainsString('<td PORT="title" colspan="2" bgcolor="#FF0000">', $dot);
        $this->assertStringContainsString('<b>' . $titleText . '</b>', $dot);
        $this->assertStringContainsString('<td bgcolor="lightgrey" width="100" align="left">', $dot);
    }

    public function testOfflineProxySqlBoxTurnsBackendRowsRedButKeepsHostGroupGrey(): void
    {
        $server = [
            'id_mysql_server' => '204',
            'display_name' => 'offline-proxysql',
            'hostname' => 'offline-proxysql',
            'ip' => '10.0.0.20',
            'ip_real' => '10.0.0.20',
            'port' => '6033',
            'port_real' => '6033',
            'version' => '2.7.3-12-g50b7f85',
            'version_comment' => 'ProxySQL',
            'version_label_override' => 'ProxySQL 2.7',
            'color' => '#008000',
            'mysql_available' => '0',
            'is_proxysql' => '1',
            'mysql_galera_hostgroups' => [
                ['writer_hostgroup' => '10', 'backup_writer_hostgroup' => '20', 'reader_hostgroup' => '30', 'offline_hostgroup' => '40'],
            ],
            'mysql_servers' => [
                ['hostgroup_id' => '10', 'hostname' => '10.0.0.21', 'port' => '3306', 'status' => 'ONLINE'],
            ],
        ];

        $dot = Graphviz::generateServer($server);

        $this->assertStringContainsString('bgcolor="#aaaaaa"', $dot);
        $this->assertStringContainsString('Host group : <b>writer</b>', $dot);
        $this->assertStringContainsString('<td colspan="2" bgcolor="#FF0000" align="left"', $dot);
        $this->assertStringContainsString('<font color="#ffffff">10.0.0.21:3306</font>', $dot);
    }

    public function testOfflineMaxScaleBoxTurnsRuntimeRowsRed(): void
    {
        $server = $this->buildOfflineMaxScaleServer();

        $dot = Graphviz::generateServer($server);

        $this->assertStringContainsString('<font color="#ffffff">⛔ Router : readwritesplit (0/12)</font>', $dot);
        $this->assertStringContainsString('<font color="#ffffff">⛔ Module : mariadbmon</font>', $dot);
        $this->assertStringContainsString('<font color="#ffffff">⛔ 10.0.0.31:3306 (5/20)</font>', $dot);
        $this->assertGreaterThanOrEqual(3, substr_count($dot, 'bgcolor="#FF0000"'));
    }

    public static function offlineServerProvider(): array
    {
        return [
            'mysql' => [[
                'id_mysql_server' => '201',
                'display_name' => 'offline-mysql',
                'hostname' => 'offline-mysql',
                'ip' => '127.0.0.1',
                'ip_real' => '127.0.0.1',
                'port' => '3306',
                'port_real' => '3306',
                'version' => '8.0.35',
                'version_comment' => 'MySQL Community Server - GPL',
                'color' => '#008000',
                'mysql_available' => '0',
            ], 'offline-mysql'],
            'mariadb' => [[
                'id_mysql_server' => '202',
                'display_name' => 'offline-mariadb',
                'hostname' => 'offline-mariadb',
                'ip' => '10.0.0.11',
                'ip_real' => '10.0.0.11',
                'port' => '3306',
                'port_real' => '3306',
                'version' => '10.11.16-MariaDB-deb12-log',
                'version_comment' => 'mariadb.org binary distribution',
                'color' => '#008000',
                'mysql_available' => '0',
                'time_zone' => 'SYSTEM',
                'system_time_zone' => 'UTC',
                'server_id' => '10',
                'auto_increment_increment' => '1',
                'auto_increment_offset' => '1',
                'binlog_format' => 'ROW',
                'binlog_row_image' => 'FULL',
                'read_only' => 'OFF',
                'log_slave_updates' => 'ON',
            ], 'offline-mariadb'],
            'percona' => [[
                'id_mysql_server' => '203',
                'display_name' => 'offline-percona',
                'hostname' => 'offline-percona',
                'ip' => '10.0.0.12',
                'ip_real' => '10.0.0.12',
                'port' => '3306',
                'port_real' => '3306',
                'version' => '8.0.35',
                'version_comment' => 'Percona Server (GPL), Release 35',
                'color' => '#008000',
                'mysql_available' => '0',
            ], 'offline-percona'],
            'vip' => [[
                'id_mysql_server' => '205',
                'display_name' => 'offline-vip',
                'hostname' => 'offline-vip',
                'ip' => '10.0.0.50',
                'ip_real' => '10.0.0.50',
                'port' => '3306',
                'port_real' => '3306',
                'color' => '#008000',
                'mysql_available' => '0',
                'is_vip' => '1',
                'vip_dns_ip' => '10.0.0.50',
                'vip_dns_port' => '3306',
                'vip_active_label' => 'db-primary:3306',
                'vip_previous_label' => 'db-replica:3306',
                'vip_last_switch' => '2026-03-18 10:00:00',
            ], 'offline-vip'],
        ];
    }

    private function buildOfflineMaxScaleServer(): array
    {
        return [
            'id_mysql_server' => '206',
            'display_name' => 'offline-maxscale',
            'hostname' => 'offline-maxscale',
            'ip' => '10.0.0.30',
            'ip_real' => '10.0.0.30',
            'port' => '4006',
            'port_real' => '4006',
            'version' => '25.10.0',
            'version_comment' => 'MaxScale',
            'color' => '#008000',
            'mysql_available' => '0',
            'is_maxscale' => '1',
            'maxscale_listeners' => [
                'data' => [[
                    'id' => 'Read-Write-Listener',
                    'attributes' => [
                        'parameters' => [
                            'address' => '10.0.0.30',
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
                            'connections' => 12,
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
                            'connections' => 5,
                            'max_connections' => 20,
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
        ];
    }
}
