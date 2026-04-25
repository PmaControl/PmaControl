<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurProxySqlDiscoveryTest extends TestCase
{
    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);

        return $instanceMethod->invokeArgs($object, $arguments);
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function testDiscoverProxySqlWriterHostgroupPrefersConfiguredGaleraWriter(): void
    {
        $galeraSql = "SELECT writer_hostgroup FROM mysql_galera_hostgroups WHERE active=1 ORDER BY writer_hostgroup ASC LIMIT 1";
        $db = $this->newProxySqlDb([
            $galeraSql => [
                ['writer_hostgroup' => 1],
            ],
        ]);

        $hostgroup = $this->invokePrivate($this->newAspirateur(), 'discoverProxySqlWriterHostgroup', [$db]);

        $this->assertSame(1, $hostgroup);
        $this->assertSame([$galeraSql], $db->queries);
    }

    public function testDiscoverProxySqlWriterHostgroupFallsBackToOnlineRuntimeServer(): void
    {
        $runtimeSql = "SELECT hostgroup_id FROM runtime_mysql_servers WHERE status='ONLINE' ORDER BY hostgroup_id ASC LIMIT 1";
        $db = $this->newProxySqlDb([
            $runtimeSql => [
                ['hostgroup_id' => 2],
            ],
        ]);

        $hostgroup = $this->invokePrivate($this->newAspirateur(), 'discoverProxySqlWriterHostgroup', [$db]);

        $this->assertSame(2, $hostgroup);
        $this->assertContains($runtimeSql, $db->queries);
    }

    private function newProxySqlDb(array $resultsBySql): object
    {
        return new class($resultsBySql) {
            public array $queries = [];
            private array $resultsBySql;
            private array $cursors = [];

            public function __construct(array $resultsBySql)
            {
                $this->resultsBySql = $resultsBySql;
            }

            public function sql_query_silent(string $sql)
            {
                $this->queries[] = $sql;
                if (!array_key_exists($sql, $this->resultsBySql)) {
                    return false;
                }

                $this->cursors[$sql] = 0;

                return $sql;
            }

            public function sql_fetch_object($res)
            {
                $sql = (string)$res;
                $index = $this->cursors[$sql] ?? 0;
                if (!isset($this->resultsBySql[$sql][$index])) {
                    return null;
                }

                $this->cursors[$sql] = $index + 1;

                return (object)$this->resultsBySql[$sql][$index];
            }
        };
    }
}
