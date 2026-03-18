<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}

final class Dot3ReplicationLinkTest extends TestCase
{
    private const DOT3_INFORMATION_ID = 990004;

    private array $originalConfig = [];
    private array $originalBuildMs = [];
    private array $originalRankSame = [];

    protected function setUp(): void
    {
        $this->originalConfig = Dot3::$config;
        $this->originalBuildMs = Dot3::$build_ms;
        $this->originalRankSame = Dot3::$rank_same;

        Dot3::$config = array_merge(Dot3::$config, [
            'REPLICATION_OK' => [
                'color' => '#008000',
                'style' => 'filled',
                'options' => [],
            ],
            'REPLICATION_BLACKOUT' => [
                'color' => '#FF0000',
                'style' => 'filled',
                'options' => [],
            ],
        ]);

        Dot3::$id_dot3_information = self::DOT3_INFORMATION_ID;
        Dot3::$information[self::DOT3_INFORMATION_ID] = [
            'information' => [
                'mapping' => [
                    '10.0.0.1:3306' => 1,
                    '10.0.0.2:3306' => 2,
                ],
                'servers' => [
                    1 => [
                        'id_mysql_server' => 1,
                        'display_name' => 'master-a',
                        'mysql_available' => '1',
                        '@slave' => [
                            '' => [
                                'connection_name' => '',
                                'master_host' => '10.0.0.2',
                                'master_port' => '3306',
                                'slave_io_running' => 'Yes',
                                'slave_sql_running' => 'Yes',
                                'seconds_behind_master' => '0',
                                'using_gtid' => 'No',
                            ],
                        ],
                    ],
                    2 => [
                        'id_mysql_server' => 2,
                        'display_name' => 'master-b',
                        'mysql_available' => '1',
                        '@slave' => [
                            '' => [
                                'connection_name' => '',
                                'master_host' => '10.0.0.1',
                                'master_port' => '3306',
                                'slave_io_running' => 'Yes',
                                'slave_sql_running' => 'Yes',
                                'seconds_behind_master' => '0',
                                'using_gtid' => 'No',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Dot3::$config = $this->originalConfig;
        Dot3::$build_ms = $this->originalBuildMs;
        Dot3::$rank_same = $this->originalRankSame;
        unset(Dot3::$information[self::DOT3_INFORMATION_ID]);
        Dot3::$id_dot3_information = null;
    }

    public function testBuildLinkAddsRankSameAndMasterMasterLabel(): void
    {
        $reflection = new ReflectionClass(Dot3::class);
        $dot3 = $reflection->newInstanceWithoutConstructor();

        Dot3::$build_ms = [];
        Dot3::$rank_same = [];

        $dot3->buildLink([self::DOT3_INFORMATION_ID, [1, 2]]);

        $this->assertCount(2, Dot3::$build_ms);
        $edgeOptions = array_map(
            static fn (array $edge): array => $edge['options'],
            Dot3::$build_ms
        );

        $hasForwardConstraintFalse = false;
        $hasBackwardConstraintFalse = false;
        foreach ($edgeOptions as $options) {
            if (($options['constraint'] ?? null) !== 'false') {
                continue;
            }

            if (($options['dir'] ?? null) === 'back') {
                $hasBackwardConstraintFalse = true;
                $this->assertSame(' ', $options['label'] ?? null);
            } else {
                $hasForwardConstraintFalse = true;
                $this->assertSame(' ', $options['label'] ?? null);
            }
        }

        $this->assertTrue($hasForwardConstraintFalse);
        $this->assertTrue($hasBackwardConstraintFalse);
        $this->assertSame([], Dot3::$rank_same);
    }

    public function testBuildLinkBetweenProxySqlUsesTwoReadableOppositeEdges(): void
    {
        $reflection = new ReflectionClass(Dot3::class);
        $dot3 = $reflection->newInstanceWithoutConstructor();

        Dot3::$build_ms = [];
        Dot3::$rank_same = [];
        Dot3::$information[self::DOT3_INFORMATION_ID]['information']['mapping']['10.0.0.10:6032'] = 10;
        Dot3::$information[self::DOT3_INFORMATION_ID]['information']['mapping']['10.0.0.11:6032'] = 11;
        Dot3::$information[self::DOT3_INFORMATION_ID]['information']['servers'][10] = [
            'id_mysql_server' => 10,
            'display_name' => 'proxysql-a',
            'is_proxy' => '1',
            'proxysql_servers' => [
                ['hostname' => '10.0.0.11', 'port' => '6032'],
            ],
        ];
        Dot3::$information[self::DOT3_INFORMATION_ID]['information']['servers'][11] = [
            'id_mysql_server' => 11,
            'display_name' => 'proxysql-b',
            'is_proxy' => '1',
            'proxysql_servers' => [
                ['hostname' => '10.0.0.10', 'port' => '6032'],
            ],
        ];

        $dot3->buildLinkBetweenProxySQL([self::DOT3_INFORMATION_ID, [10, 11]]);

        $this->assertCount(2, Dot3::$build_ms);
        $edgeOptions = array_map(
            static fn (array $edge): array => $edge['options'],
            Dot3::$build_ms
        );

        $hasForwardConstraintFalse = false;
        $hasBackwardConstraintFalse = false;
        foreach ($edgeOptions as $options) {
            if (($options['constraint'] ?? null) !== 'false') {
                continue;
            }

            $this->assertSame(' ', $options['label'] ?? null);
            if (($options['dir'] ?? null) === 'back') {
                $hasBackwardConstraintFalse = true;
            } else {
                $hasForwardConstraintFalse = true;
            }
        }

        $this->assertTrue($hasForwardConstraintFalse);
        $this->assertTrue($hasBackwardConstraintFalse);
        $this->assertSame([], Dot3::$rank_same);
    }
}
