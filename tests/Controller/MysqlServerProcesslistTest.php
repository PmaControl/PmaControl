<?php

declare(strict_types=1);

use App\Controller\MysqlServer;
use PHPUnit\Framework\TestCase;

final class MysqlServerProcesslistTest extends TestCase
{
    public function testShouldUsePerfSchemaProcesslistForPercona84(): void
    {
        $db = new class {
            public function getServerType(): string { return 'Percona Server'; }
            public function checkVersion(array $versions): bool
            {
                return isset($versions['Percona Server']) && version_compare('8.4.8', $versions['Percona Server'], '>=');
            }
        };

        $method = new ReflectionMethod(MysqlServer::class, 'shouldUsePerfSchemaProcesslist');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(null, $db, true));
    }

    public function testShouldUsePerfSchemaProcesslistIsFalseForMariaDB(): void
    {
        $db = new class {
            public function getServerType(): string { return 'MariaDB'; }
            public function checkVersion(array $versions): bool { return true; }
        };

        $method = new ReflectionMethod(MysqlServer::class, 'shouldUsePerfSchemaProcesslist');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke(null, $db, true));
    }

    public function testShouldUsePerfSchemaProcesslistIsFalseWithoutPerfThreads(): void
    {
        $db = new class {
            public function getServerType(): string { return 'Percona Server'; }
            public function checkVersion(array $versions): bool { return true; }
        };

        $method = new ReflectionMethod(MysqlServer::class, 'shouldUsePerfSchemaProcesslist');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke(null, $db, false));
    }

    public function testGetProcesslistClassThresholds(): void
    {
        $method = new ReflectionMethod(MysqlServer::class, 'getProcesslistClass');
        $method->setAccessible(true);

        $this->assertSame('', $method->invoke(null, 1));
        $this->assertSame('info', $method->invoke(null, 2));
        $this->assertSame('primary', $method->invoke(null, 11));
        $this->assertSame('warning', $method->invoke(null, 61));
        $this->assertSame('danger', $method->invoke(null, 601));
    }
}
