<?php

declare(strict_types=1);

use App\Library\Format as Mysql;
use PHPUnit\Framework\TestCase;

final class MysqlVersionCompatibilityTest extends TestCase
{
    public function testGetMySQLNumVersionHandlesPercona84SuffixWithoutWarnings(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.4.8-8', "Percona Server (GPL), Release '8', Revision '1c288264'");

        $this->assertSame('8.4.8', $parsed['number']);
        $this->assertSame('Percona', $parsed['fork']);
        $this->assertTrue($parsed['enterprise']);
    }

    public function testGetMySQLNumVersionKeepsMysqlForGenericLogSuffix(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.0.44-log', 'MySQL Community Server - GPL');

        $this->assertSame('8.0.44', $parsed['number']);
        $this->assertSame('log', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }

    public function testMariaDBEnterprise(): void
    {
        $parsed = Mysql::getMySQLNumVersion('10.6.19-15-MariaDB-enterprise-log', 'MariaDB Enterprise Server');

        $this->assertSame('10.6.19', $parsed['number']);
        $this->assertSame('MariaDB', $parsed['fork']);
        $this->assertTrue($parsed['enterprise']);
    }

    public function testMariaDBEnterpriseNumericOnlyAfterVersion(): void
    {
        // e.g. "10.11.16-15" — only 2 parts after the version number
        $parsed = Mysql::getMySQLNumVersion('10.11.16-15', 'MariaDB Server');

        $this->assertSame('10.11.16', $parsed['number']);
        // fork falls back to the numeric string when no 3rd part exists
        $this->assertSame('15', $parsed['fork']);
        $this->assertTrue($parsed['enterprise']);
    }

    public function testPlainMySQLNoSuffix(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.0.32', 'MySQL Community Server - GPL');

        $this->assertSame('8.0.32', $parsed['number']);
        $this->assertSame('MySQL', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }

    public function testMariaDBStandard(): void
    {
        $parsed = Mysql::getMySQLNumVersion('10.11.16-MariaDB', 'MariaDB Server');

        $this->assertSame('10.11.16', $parsed['number']);
        $this->assertSame('MariaDB', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }

    public function testMariaDBWithLogSuffix(): void
    {
        $parsed = Mysql::getMySQLNumVersion('10.6.19-MariaDB-log', 'MariaDB Server');

        $this->assertSame('10.6.19', $parsed['number']);
        $this->assertSame('MariaDB', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }

    public function testPerconaDetectedFromComment(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.0.36-28', "Percona Server (GPL), Release 28, Revision 1234");

        $this->assertSame('8.0.36', $parsed['number']);
        $this->assertSame('Percona', $parsed['fork']);
        $this->assertTrue($parsed['enterprise']);
    }

    public function testProxySQLDetectedFromComment(): void
    {
        $parsed = Mysql::getMySQLNumVersion('5.5.30', 'ProxySQL Admin Module');

        $this->assertSame('5.5.30', $parsed['number']);
        $this->assertSame('ProxySQL', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }

    public function testMySQL84WithPatchSuffix(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.4.5-1', 'MySQL Community Server - GPL');

        $this->assertSame('8.4.5', $parsed['number']);
        $this->assertTrue($parsed['enterprise']);
    }
}
