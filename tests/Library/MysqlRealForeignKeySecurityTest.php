<?php

declare(strict_types=1);

use App\Library\Mysql;
use App\Library\Security\ForeignKeyRoute;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MysqlRealForeignKeySecurityTest extends TestCase
{
    public function testForeignKeyRouteNormalizesValidRoute(): void
    {
        $this->assertSame(
            ['id_mysql_server' => 7, 'database' => 'customer_db', 'param' => [7, 'customer_db']],
            ForeignKeyRoute::normalize([' 7 ', ' customer_db '])
        );
    }

    #[DataProvider('invalidForeignKeyRouteProvider')]
    public function testForeignKeyRouteRejectsSqlInjectionPayloads(array $param): void
    {
        $this->assertNull(ForeignKeyRoute::normalize($param));
    }

    public function testRealForeignKeyQueryEscapesDatabaseValue(): void
    {
        $query = Mysql::buildRealForeignKeyQuery($this->escapingDb(), "app'db");

        $this->assertStringContainsString("`CONSTRAINT_SCHEMA` ='app''db'", $query);
        $this->assertStringContainsString("`REFERENCED_TABLE_SCHEMA`='app''db'", $query);
        $this->assertStringNotContainsString("`CONSTRAINT_SCHEMA` ='app'db'", $query);
    }

    public function testMysqlLibraryHelperNormalizesBeforeQuerying(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Library/Mysql.php');
        $body = $this->methodBody(
            $source,
            'static public function getRealForeignKey($param)',
            'Debug::sql($sql);'
        );

        $this->assertStringContainsString('use App\\Library\\Security\\ForeignKeyRoute;', $source);
        $this->assertStringContainsString('$route = ForeignKeyRoute::normalize($param);', $body);
        $this->assertStringContainsString('return [];', $body);
        $this->assertStringContainsString('$sql = self::buildRealForeignKeyQuery($db, $database);', $body);
        $this->assertStringContainsString('$databaseSql = $db->sql_real_escape_string($database);', $source);
        $this->assertStringNotContainsString('$id_mysql_server = $param[0];', $body);
        $this->assertStringNotContainsString('$database        = $param[1];', $body);
        $this->assertStringNotContainsString('."$database"', $body);
        $this->assertStringNotContainsString('.".$database."', $body);
    }

    public static function invalidForeignKeyRouteProvider(): array
    {
        return [
            'missing id' => [['customer_db']],
            'zero id' => [['0', 'customer_db']],
            'id injection' => [['7 OR 1=1', 'customer_db']],
            'id array' => [[['7'], 'customer_db']],
            'database quote injection' => [['7', "customer_db' OR '1'='1"]],
            'database backtick injection' => [['7', 'customer_db`;DROP TABLE mysql_server;--']],
            'database expression' => [['7', 'customer_db UNION SELECT']],
            'database array' => [['7', ['customer_db']]],
            'empty database' => [['7', '']],
            'oversized database' => [['7', str_repeat('a', 65)]],
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
