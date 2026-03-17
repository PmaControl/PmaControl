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
}
