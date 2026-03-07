<?php

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

class InnoDBClusterDot3Test extends TestCase
{
    public function testExtractGroupReplicationEndpoints(): void
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('extractGroupReplicationEndpoints');
        $method->setAccessible(true);

        $res = $method->invoke(null, 'mysql1:33061,mysql2:33062,mysql3');

        $this->assertSame(['mysql1:33061', 'mysql2:33062'], $res);
    }

    public function testGenerateGroupInnoDBClusterBuildsMappings(): void
    {
        $dot3 = new Dot3();
        $info = [
            'servers' => [
                10 => [
                    'group_replication_group_name' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'group_replication_group_seeds' => 'db1:3306,db2:3306',
                    'group_replication_local_address' => 'db1:33061',
                ],
                11 => [
                    'group_replication_group_name' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                    'group_replication_group_seeds' => 'db1:3306,db2:3306',
                ],
            ],
            'mapping' => [
                'db1:3306' => 10,
                'db2:3306' => 11,
            ],
        ];

        $groups = $dot3->generateGroupInnoDBCluster($info);

        $this->assertArrayHasKey(10, $groups);
        $this->assertArrayHasKey(11, $groups);
        $this->assertSame([10, 11], $groups[10]);
        $this->assertSame([10, 11], $groups[11]);
    }
}
