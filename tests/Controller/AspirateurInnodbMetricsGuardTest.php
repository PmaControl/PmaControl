<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurInnodbMetricsGuardTest extends TestCase
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

    public function testCollectInnodbMetricsReturnsEmptyArrayWhenTableIsMissing(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new class {
            public $queries = array();

            public function sql_real_escape_string($value): string
            {
                return addslashes((string)$value);
            }

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;

                return 'show_tables';
            }

            public function sql_num_rows($res): int
            {
                return 0;
            }
        };

        $metrics = $this->invokePrivate($aspirateur, 'collectInnodbMetricsFromConnection', [$db]);

        $this->assertSame(array(), $metrics);
        $this->assertCount(1, $db->queries);
        $this->assertStringContainsString("SHOW TABLES FROM `INFORMATION_SCHEMA` LIKE 'INNODB_METRICS'", $db->queries[0]);
    }

    public function testCollectInnodbMetricsReturnsEmptyArrayWhenProbeFails(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new class {
            public $queries = array();

            public function sql_real_escape_string($value): string
            {
                return addslashes((string)$value);
            }

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;
                throw new \RuntimeException('unknown table');
            }
        };

        $metrics = $this->invokePrivate($aspirateur, 'collectInnodbMetricsFromConnection', [$db]);

        $this->assertSame(array(), $metrics);
        $this->assertCount(1, $db->queries);
    }

    public function testCollectInnodbMetricsOnlyExportsEnabledRowsWhenTableExists(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new class {
            public $queries = array();
            private $rows = array(
                array('NAME' => 'buffer_pool_reads', 'ENABLED' => 1, 'COUNT' => 42),
                array('NAME' => 'disabled_metric', 'ENABLED' => 0, 'COUNT' => 99),
            );
            private $index = 0;

            public function sql_real_escape_string($value): string
            {
                return addslashes((string)$value);
            }

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;
                if (strpos($sql, 'SELECT * FROM `INFORMATION_SCHEMA`.`INNODB_METRICS`') !== false) {
                    $this->index = 0;
                    return 'innodb_metrics';
                }

                return 'show_tables';
            }

            public function sql_num_rows($res): int
            {
                return $res === 'show_tables' ? 1 : 0;
            }

            public function sql_fetch_array($res, $mode)
            {
                if ($res !== 'innodb_metrics') {
                    return null;
                }

                if (!isset($this->rows[$this->index])) {
                    return null;
                }

                return $this->rows[$this->index++];
            }
        };

        $metrics = $this->invokePrivate($aspirateur, 'collectInnodbMetricsFromConnection', [$db]);

        $this->assertCount(2, $db->queries);
        $this->assertArrayHasKey('buffer_pool_reads', $metrics);
        $this->assertArrayNotHasKey('disabled_metric', $metrics);

        $payload = json_decode($metrics['buffer_pool_reads'], true);
        $this->assertSame(1, $payload['enabled']);
        $this->assertSame(42, $payload['count']);
        $this->assertArrayNotHasKey('name', $payload);
    }
}
