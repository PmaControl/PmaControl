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

    public function testSystemSchemasConstantCoversTheFourMysqlReservedNames(): void
    {
        // #567: keep the canonical list. Casing matches MySQL's lowercase
        // SHOW DATABASES output; comparisons are done case-insensitively
        // by isSystemSchema().
        $this->assertSame(
            ['information_schema', 'performance_schema', 'mysql', 'sys'],
            MysqlServer::SYSTEM_SCHEMAS
        );
    }

    public function testIsSystemSchemaRecognizesTheFourReservedNames(): void
    {
        foreach (MysqlServer::SYSTEM_SCHEMAS as $name) {
            $this->assertTrue(
                MysqlServer::isSystemSchema($name),
                $name.' must be recognized as a system schema'
            );
        }
    }

    public function testIsSystemSchemaIsCaseInsensitive(): void
    {
        $this->assertTrue(MysqlServer::isSystemSchema('MYSQL'));
        $this->assertTrue(MysqlServer::isSystemSchema('Information_Schema'));
        $this->assertTrue(MysqlServer::isSystemSchema('Performance_SCHEMA'));
        $this->assertTrue(MysqlServer::isSystemSchema('Sys'));
    }

    public function testIsSystemSchemaRejectsUserDatabasesAndEdgeCases(): void
    {
        $this->assertFalse(MysqlServer::isSystemSchema('myapp'));
        $this->assertFalse(MysqlServer::isSystemSchema('production'));
        $this->assertFalse(MysqlServer::isSystemSchema('mysql_backup'));   // similar prefix, not equal
        $this->assertFalse(MysqlServer::isSystemSchema('information'));    // similar prefix, not equal
        $this->assertFalse(MysqlServer::isSystemSchema(''));
        $this->assertFalse(MysqlServer::isSystemSchema(null));
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
            $this->assertMatchesRegularExpression(
                '/MysqlServer::getDbLinkFromId\(\$id_db(,\s*false)?\)/',
                $source,
                $controller.' must delegate getDbLinkFromId to App\\Library\\MysqlServer'
            );
        }
    }

    public function testDbLinkControllersDoNotInlineBrokenMysqlServerLookupSql(): void
    {
        foreach ($this->dbLinkControllers() as $controller => $path) {
            $source = (string) file_get_contents($path);

            // 'id_deleted' may legitimately appear inside a TODO comment
            // referencing the regression; only flag it outside comments.
            $stripped = preg_replace('@//[^\n]*@', '', $source);
            $stripped = preg_replace('@/\*.*?\*/@s', '', (string) $stripped);

            $this->assertStringNotContainsString(
                'id_deleted',
                (string) $stripped,
                $controller.' must not reference the non-existent mysql_server.id_deleted column (outside comments)'
            );
            $this->assertDoesNotMatchRegularExpression(
                '/SELECT\s+id\s*,\s*name\s+FROM\s+mysql_server\s+WHERE[^;]+WHERE/i',
                $source,
                $controller.' must not contain the double-WHERE mysql_server lookup regression'
            );
        }
    }

    public function testCommonControllerUsesFilteredLookupAsTheBugFix(): void
    {
        // #559 fix: Common::getDbLinkFromId must use the default
        // excludeDeleted=true to filter soft-deleted servers — otherwise the
        // /database/refresh AJAX regression returns.
        $source = (string) file_get_contents(__DIR__.'/../../App/Controller/Common.php');

        $this->assertMatchesRegularExpression(
            '/MysqlServer::getDbLinkFromId\(\$id_db\)\s*;/',
            $source,
            'Common.php must call MysqlServer::getDbLinkFromId without overriding the default soft-delete filter (#559)'
        );
        $this->assertStringNotContainsString(
            'MysqlServer::getDbLinkFromId($id_db, false)',
            $source,
            'Common.php must not opt out of the soft-delete filter (would re-introduce the #559 regression direction)'
        );
    }

    public function testLegacyControllersOptOutOfFilterUntilFollowup(): void
    {
        // #561 followup: Compare/CompareConfig/CheckConfig/CheckDataOnCluster
        // historically did NOT filter mysql_server.is_deleted=0. Preserve that
        // behavior explicitly until the audit decides per endpoint. Any switch
        // to the default (filtered) call must be a deliberate diff that
        // updates this assertion.
        $legacy = [
            'Compare' => __DIR__.'/../../App/Controller/Compare.php',
            'CompareConfig' => __DIR__.'/../../App/Controller/CompareConfig.php',
            'CheckConfig' => __DIR__.'/../../App/Controller/CheckConfig.php',
            'CheckDataOnCluster' => __DIR__.'/../../App/Controller/CheckDataOnCluster.php',
        ];

        foreach ($legacy as $controller => $path) {
            $source = (string) file_get_contents($path);

            $this->assertStringContainsString(
                'MysqlServer::getDbLinkFromId($id_db, false)',
                $source,
                $controller.' must keep legacy semantics by passing excludeDeleted=false until #561 is closed'
            );
            $this->assertStringContainsString(
                'TODO #561',
                $source,
                $controller.' must keep the #561 follow-up TODO so the legacy opt-out is auditable'
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
