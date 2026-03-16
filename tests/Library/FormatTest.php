<?php

declare(strict_types=1);

use App\Library\Format;
use PHPUnit\Framework\TestCase;

if (!defined('IMG')) {
    define('IMG', '/assets');
}

final class FormatTest extends TestCase
{
    public function testBytesFormatsHumanReadableSize(): void
    {
        $this->assertSame('1.00 Ko', Format::bytes(1024));
        $this->assertSame('1.50 Ko', Format::bytes(1536));
    }

    public function testPingSwitchesToSecondsAboveOneSecond(): void
    {
        $this->assertSame('500 ms', Format::ping(0.5, 0));
        $this->assertSame('1.5 s', Format::ping(1.5, 1));
    }

    public function testGetMySQLNumVersionDetectsEnterpriseMariadb(): void
    {
        $version = Format::getMySQLNumVersion('10.6.19-15-MariaDB-enterprise-log', 'MariaDB Enterprise Server');

        $this->assertSame('10.6.19', $version['number']);
        $this->assertSame('MariaDB', $version['fork']);
        $this->assertTrue($version['enterprise']);
    }

    public function testGetMySQLNumVersionCommentOverridesFork(): void
    {
        $version = Format::getMySQLNumVersion('8.0.35', 'Percona Server (GPL), Release 35');

        $this->assertSame('8.0.35', $version['number']);
        $this->assertSame('Percona', $version['fork']);
        $this->assertFalse($version['enterprise']);
    }

    public function testMysqlVersionRendersProxySqlLabel(): void
    {
        $label = Format::mysqlVersion('2.5.5', 'ProxySQL');

        $this->assertStringContainsString('ProxySQL', $label);
        $this->assertStringContainsString('2.5.5', $label);
    }
}
