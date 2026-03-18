<?php

use App\Controller\Dot3;
use App\Library\Graphviz;
use PHPUnit\Framework\TestCase;

class InnoDBClusterDot3Test extends TestCase
{
    public function testExtractGroupReplicationEndpoints(): void
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('extractGroupReplicationEndpoints');
        $method->setAccessible(true);

        $res = $method->invoke(null, 'mysql://db1:33061,[2001:db8::1]:33062,db3');

        $this->assertSame(['db1:33061', '2001:db8::1:33062'], $res);
    }

    public function testGenerateGroupInnoDBClusterBuildsMappingsFromLocalAddress(): void
    {
        $reflection = new ReflectionClass(Dot3::class);
        $dot3 = $reflection->newInstanceWithoutConstructor();
        $info = [
            'servers' => [
                10 => [
                    'group_replication_group_name' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'group_replication_group_seeds' => 'db1:33061,db2:33061',
                    'group_replication_local_address' => 'db1:33061',
                    'report_host' => 'db1',
                    'mysql_available' => '1',
                ],
                11 => [
                    'group_replication_group_name' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'group_replication_group_seeds' => 'db1:33061,db2:33061',
                    'group_replication_local_address' => 'db2:33061',
                    'report_host' => 'db2',
                    'mysql_available' => '1',
                ],
            ],
            'mapping' => [
                'db1:3306' => 10,
                'db2:3306' => 11,
            ],
        ];

        $groups = $dot3->generateGroupInnoDBCluster($info);

        $this->assertArrayHasKey('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', $groups);
        $this->assertSame([10, 11], $groups['aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee']);
    }

    public function testGenerateInnoDBClusterCreatesPrimaryAndReplicaSubgraphs(): void
    {
        if (!defined('LINK')) {
            define('LINK', '/');
        }

        $graph = Graphviz::generateInnoDBCluster([
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee' => [
                'id_cluster' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'name' => 'prodCluster',
                'group_name' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                'mode' => 'single-primary',
                'members' => 3,
                'node_online' => 3,
                'primary_online' => 1,
                'config' => 'INNODB_CLUSTER_OK',
                'node' => [
                    10 => ['member_role' => 'PRIMARY', 'member_state' => 'ONLINE'],
                    11 => ['member_role' => 'SECONDARY', 'member_state' => 'ONLINE'],
                    12 => ['member_role' => 'SECONDARY', 'member_state' => 'ONLINE'],
                ],
            ],
        ]);

        $this->assertStringContainsString('label = "Primary";', $graph);
        $this->assertStringContainsString('label = "Replica";', $graph);
        $this->assertMatchesRegularExpression('/subgraph cluster_innodb_[^{]+\{[^}]*label = "Primary";[^}]*10;/s', $graph);
        $this->assertMatchesRegularExpression('/subgraph cluster_innodb_[^{]+\{[^}]*label = "Replica";[^}]*11;[^}]*12;/s', $graph);
    }
}
