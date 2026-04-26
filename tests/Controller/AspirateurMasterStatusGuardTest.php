<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AspirateurMasterStatusGuardTest extends TestCase
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

    #[DataProvider('masterStatusCommandProvider')]
    public function testMasterStatusCommandMatchesServerCapability(
        string $version,
        string $versionComment,
        bool $isSingleStore,
        ?string $expected
    ): void {
        $command = $this->invokePrivate(
            $this->newAspirateur(),
            'getMasterStatusCommand',
            [$version, $versionComment, $isSingleStore]
        );

        $this->assertSame($expected, $command);
    }

    public static function masterStatusCommandProvider(): array
    {
        return array(
            'MySQL 5.7' => ['5.7.44-log', 'MySQL Community Server', false, 'SHOW MASTER STATUS'],
            'MySQL 8.0' => ['8.0.44', 'MySQL Community Server', false, 'SHOW MASTER STATUS'],
            'MySQL 8.4' => ['8.4.8', 'MySQL Community Server', false, 'SHOW BINARY LOG STATUS'],
            'Percona 8.4' => ['8.4.8-8', 'Percona Server', false, 'SHOW BINARY LOG STATUS'],
            'MariaDB 10.11' => ['10.11.16-MariaDB-deb12-log', '', false, 'SHOW MASTER STATUS'],
            'SingleStore flag' => ['8.1.32', 'MySQL Community Server', true, null],
            'SingleStore comment' => ['8.1.32', 'SingleStoreDB', false, null],
            'Unknown version' => ['', '', false, null],
        );
    }

    public function testMasterStatusIsSkippedWhenCapabilityIsUnavailable(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new class {
            public $queries = array();

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;

                return false;
            }
        };

        $status = $this->invokePrivate(
            $aspirateur,
            'getMasterStatusFromConnection',
            [$db, '8.1.32', 'SingleStoreDB', true]
        );

        $this->assertSame(array(), $status);
        $this->assertSame(array(), $db->queries);
    }

    public function testMasterStatusUsesSelectedCommandAndReturnsFirstRow(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new class {
            public $queries = array();

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;

                return 'master_status';
            }

            public function sql_num_rows($res): int
            {
                return 1;
            }

            public function sql_fetch_array($res, $mode): array
            {
                return array('File' => 'binlog.000001', 'Position' => '123');
            }
        };

        $status = $this->invokePrivate(
            $aspirateur,
            'getMasterStatusFromConnection',
            [$db, '8.4.8', 'MySQL Community Server', false]
        );

        $this->assertSame(array('File' => 'binlog.000001', 'Position' => '123'), $status);
        $this->assertSame(array('SHOW BINARY LOG STATUS'), $db->queries);
    }

    public function testMasterStatusReturnsEmptyArrayWhenQueryFails(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new class {
            public $queries = array();

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;
                throw new \RuntimeException('syntax error');
            }
        };

        $status = $this->invokePrivate(
            $aspirateur,
            'getMasterStatusFromConnection',
            [$db, '5.7.44-log', 'MySQL Community Server', false]
        );

        $this->assertSame(array(), $status);
        $this->assertSame(array('SHOW MASTER STATUS'), $db->queries);
    }
}
