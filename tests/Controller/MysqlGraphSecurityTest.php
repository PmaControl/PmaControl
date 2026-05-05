<?php

declare(strict_types=1);

use App\Controller\Mysql;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MysqlGraphSecurityTest extends TestCase
{
    public function testGraphRequestNormalizesValidRoute(): void
    {
        $this->assertSame(
            [
                'id_mysql_server' => 12,
                'database' => 'app_db',
            ],
            Mysql::normalizeGraphRequest([' 12 ', ' app_db '])
        );
    }

    #[DataProvider('invalidGraphRouteProvider')]
    public function testGraphRequestRejectsSqlInjectionPayloads(array $param): void
    {
        $this->assertNull(Mysql::normalizeGraphRequest($param));
    }

    public function testGraphTablesQueryEscapesDatabaseValue(): void
    {
        $query = Mysql::buildGraphTablesQuery($this->escapingDb(), 'app_db');

        $this->assertSame(
            "SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` "
            ."WHERE TABLE_SCHEMA = 'app_db' "
            ."AND TABLE_TYPE IN ('BASE TABLE', 'SYSTEM VERSIONED') ORDER BY TABLE_NAME;",
            $query
        );
    }

    public function testGraphColumnsQueryEscapesSecondOrderTableName(): void
    {
        $query = Mysql::buildGraphColumnsQuery(
            $this->escapingDb(),
            'app_db',
            "orders' UNION SELECT password FROM user_main--"
        );

        $this->assertStringContainsString("TABLE_SCHEMA = 'app_db'", $query);
        $this->assertStringContainsString("TABLE_NAME = 'orders'' UNION SELECT password FROM user_main--'", $query);
        $this->assertStringNotContainsString("TABLE_NAME = 'orders' UNION SELECT", $query);
    }

    public function testMpdGraphFlowUsesNormalizedRequestAndQueryBuilders(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');
        $mpdBody = $this->methodBody(
            $controller,
            'public function mpd($param)',
            'public static function normalizeGraphRequest'
        );

        $this->assertStringContainsString('use App\\Library\\Security\\Identifier;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('$request = self::normalizeGraphRequest($param);', $mpdBody);
        $this->assertStringContainsString("self::sendMysqlGraphError(400, 'Invalid MySQL graph request');", $mpdBody);
        $this->assertStringContainsString('$graphParam      = [$id_mysql_server, $database];', $mpdBody);
        $this->assertStringContainsString('$sql = self::buildGraphTablesQuery($db, $database);', $mpdBody);
        $this->assertStringContainsString('$liste_table_connected = $this->tableListLinked($graphParam);', $mpdBody);
        $this->assertStringContainsString('Mysql::protectInformationSchemaTablesQuery($db, $sql, $id_mysql_server)', $mpdBody);
        $this->assertStringContainsString('self::buildGraphColumnsQuery($db, $database, (string) $table[\'TABLE_NAME\'])', $mpdBody);
        $this->assertStringContainsString('$contraints = $this->getForeignKey($graphParam);', $mpdBody);

        $this->assertStringNotContainsString('$param[1]', $mpdBody);
        $this->assertStringNotContainsString('TABLE_NAME =\'".$table[\'TABLE_NAME\']."\'', $mpdBody);
    }

    public function testGraphForeignKeyHelpersReuseNormalizedRouteAndEscapedDatabase(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');
        $tableListBody = $this->methodBody(
            $controller,
            'public function tableListLinked($param)',
            'public function getVirtualForeignKey($param)'
        );
        $virtualBody = $this->methodBody(
            $controller,
            'public function getVirtualForeignKey($param)',
            'public function getRealForeignKey($param)'
        );
        $realBody = $this->methodBody(
            $controller,
            'public function getRealForeignKey($param)',
            'public function getForeignKey($param)'
        );
        $foreignKeyBody = $this->methodBody(
            $controller,
            'public function getForeignKey($param)',
            'public function audit($param)'
        );

        foreach ([$tableListBody, $virtualBody, $realBody, $foreignKeyBody] as $body) {
            $this->assertStringContainsString('$request = self::normalizeGraphRequest($param);', $body);
            $this->assertStringContainsString('return [];', $body);
            $this->assertStringNotContainsString('$database = $param[1];', $body);
            $this->assertStringNotContainsString('$database        = $param[1];', $body);
            $this->assertStringNotContainsString('$id_mysql_server = $param[0];', $body);
        }

        foreach ([$tableListBody, $virtualBody, $realBody] as $body) {
            $this->assertStringContainsString('$databaseSql = $db->sql_real_escape_string($database);', $body);
            $this->assertStringNotContainsString('."$database"', $body);
            $this->assertStringNotContainsString('.".$database."', $body);
        }

        $this->assertStringContainsString('a.id_mysql_server = ".$id_mysql_server."', $tableListBody);
        $this->assertStringContainsString('b.id_mysql_server = ".$id_mysql_server."', $tableListBody);
        $this->assertStringContainsString("constraint_schema = '\".\$databaseSql.\"'", $virtualBody);
        $this->assertStringContainsString("`CONSTRAINT_SCHEMA` ='\".\$databaseSql.\"'", $realBody);
        $this->assertStringContainsString("`REFERENCED_TABLE_SCHEMA`='\".\$databaseSql.\"'", $realBody);
        $this->assertStringContainsString('$graphParam = [$request[\'id_mysql_server\'], $request[\'database\']];', $foreignKeyBody);
    }

    public static function invalidGraphRouteProvider(): array
    {
        return [
            'missing id' => [['app_db']],
            'zero id' => [['0', 'app_db']],
            'id injection' => [['12 OR 1=1', 'app_db']],
            'id array' => [[['12'], 'app_db']],
            'database quote injection' => [['12', "app_db'--"]],
            'database backtick injection' => [['12', 'app_db`;DROP TABLE mysql_server;--']],
            'database expression' => [['12', 'app_db UNION SELECT']],
            'database array' => [['12', ['app_db']]],
            'empty database' => [['12', '']],
        ];
    }

    private function escapingDb(): object
    {
        return new class {
            public function sql_real_escape_string($value): string
            {
                return str_replace("'", "''", (string) $value);
            }
        };
    }

    private function methodBody(string $source, string $startNeedle, string $endNeedle): string
    {
        $start = strpos($source, $startNeedle);
        $end = strpos($source, $endNeedle, $start === false ? 0 : $start);

        $this->assertIsInt($start);
        $this->assertIsInt($end);

        return substr($source, $start, $end - $start);
    }
}
