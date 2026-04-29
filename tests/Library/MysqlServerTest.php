<?php

declare(strict_types=1);

use App\Library\MysqlServer;
use PHPUnit\Framework\TestCase;

final class MysqlServerTest extends TestCase
{
    public function testBuildDbLinkSqlCastsIdAndFiltersDeletedServers(): void
    {
        $sql = MysqlServer::buildDbLinkSql('149 OR 1=1');

        $this->assertSame(
            'SELECT id,name FROM mysql_server WHERE id = 149 AND is_deleted = 0;',
            $sql
        );
        $this->assertSame(1, substr_count(strtoupper($sql), 'WHERE'));
        $this->assertStringNotContainsString('id_deleted', $sql);
        $this->assertStringNotContainsString("'", $sql);
    }

    public function testBuildDbLinkSqlCanIncludeDeletedServersWhenExplicitlyRequested(): void
    {
        $sql = MysqlServer::buildDbLinkSql(149, false);

        $this->assertSame('SELECT id,name FROM mysql_server WHERE id = 149;', $sql);
        $this->assertStringNotContainsString('is_deleted', $sql);
    }

    public function testDbLinkControllersDelegateToSharedHelper(): void
    {
        foreach ($this->dbLinkControllers() as $controller => $path) {
            $source = (string) file_get_contents($path);

            $this->assertStringContainsString(
                'use App\\Library\\MysqlServer;',
                $source,
                $controller.' must import the shared mysql server helper'
            );
            $this->assertStringContainsString(
                'MysqlServer::getDbLinkFromId($id_db)',
                $source,
                $controller.' must delegate getDbLinkFromId to App\\Library\\MysqlServer'
            );
        }
    }

    public function testDbLinkControllersDoNotInlineBrokenMysqlServerLookupSql(): void
    {
        foreach ($this->dbLinkControllers() as $controller => $path) {
            $source = (string) file_get_contents($path);

            $this->assertStringNotContainsString(
                'id_deleted',
                $source,
                $controller.' must not reference the non-existent mysql_server.id_deleted column'
            );
            $this->assertDoesNotMatchRegularExpression(
                '/SELECT\s+id\s*,\s*name\s+FROM\s+mysql_server\s+WHERE[^;]+WHERE/i',
                $source,
                $controller.' must not contain the double-WHERE mysql_server lookup regression'
            );
        }
    }

    private function dbLinkControllers(): array
    {
        $root = __DIR__.'/../..';

        return [
            'Common' => $root.'/App/Controller/Common.php',
            'Compare' => $root.'/App/Controller/Compare.php',
            'CompareConfig' => $root.'/App/Controller/CompareConfig.php',
            'CheckConfig' => $root.'/App/Controller/CheckConfig.php',
            'CheckDataOnCluster' => $root.'/App/Controller/CheckDataOnCluster.php',
        ];
    }
}
