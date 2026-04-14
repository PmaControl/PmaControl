<?php

declare(strict_types=1);

use Glial\Sgbd\Sql\Mysql\Mysql;
use PHPUnit\Framework\TestCase;

final class MysqlVersionCompatibilityTest extends TestCase
{
    public function testGetMySQLNumVersionHandlesPercona84SuffixWithoutWarnings(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.4.8-8', "Percona Server (GPL), Release '8', Revision '1c288264'");

        $this->assertSame('8.4.8', $parsed['number']);
        $this->assertSame('Percona', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }

    public function testGetMySQLNumVersionKeepsMysqlForGenericLogSuffix(): void
    {
        $parsed = Mysql::getMySQLNumVersion('8.0.44-log', 'MySQL Community Server - GPL');

        $this->assertSame('8.0.44', $parsed['number']);
        $this->assertSame('log', $parsed['fork']);
        $this->assertFalse($parsed['enterprise']);
    }
}
