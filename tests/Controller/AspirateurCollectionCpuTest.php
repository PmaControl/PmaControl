<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurCollectionCpuTest extends TestCase
{
    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);
        $instanceMethod->setAccessible(true);

        return $instanceMethod->invokeArgs($object, $arguments);
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        Aspirateur::$database_list_cache = [];
    }

    public function testGetDatabaseListCachesRepeatedShowDatabasesCallsForSameConnection(): void
    {
        $aspirateur = $this->newAspirateur();

        $db = new class {
            public $queryCount = 0;
            private $rows = [
                ['information_schema'],
                ['mysql'],
                ['app'],
                ['analytics'],
            ];
            private $index = 0;

            public function sql_query(string $sql)
            {
                $this->queryCount++;
                $this->index = 0;

                return 'result';
            }

            public function sql_fetch_array($res, $mode)
            {
                if (!isset($this->rows[$this->index])) {
                    return null;
                }

                return $this->rows[$this->index++];
            }
        };

        $first = $this->invokePrivate($aspirateur, 'getDatabaseList', [$db, false]);
        $second = $this->invokePrivate($aspirateur, 'getDatabaseList', [$db, false]);

        $this->assertSame(['app', 'analytics'], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $db->queryCount);
    }

    public function testGetDatabaseListKeepsSeparateCachesPerIncludeSystemSchemasFlag(): void
    {
        $aspirateur = $this->newAspirateur();

        $db = new class {
            public $queryCount = 0;
            private $rows = [
                ['information_schema'],
                ['mysql'],
                ['app'],
            ];
            private $index = 0;

            public function sql_query(string $sql)
            {
                $this->queryCount++;
                $this->index = 0;

                return 'result';
            }

            public function sql_fetch_array($res, $mode)
            {
                if (!isset($this->rows[$this->index])) {
                    return null;
                }

                return $this->rows[$this->index++];
            }
        };

        $withoutSystem = $this->invokePrivate($aspirateur, 'getDatabaseList', [$db, false]);
        $withSystem = $this->invokePrivate($aspirateur, 'getDatabaseList', [$db, true]);
        $withSystemAgain = $this->invokePrivate($aspirateur, 'getDatabaseList', [$db, true]);

        $this->assertSame(['app'], $withoutSystem);
        $this->assertSame(['information_schema', 'mysql', 'app'], $withSystem);
        $this->assertSame($withSystem, $withSystemAgain);
        $this->assertSame(2, $db->queryCount);
    }
}
