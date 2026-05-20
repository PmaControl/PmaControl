<?php

declare(strict_types=1);

use App\Controller\Cleaner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CleanerGetColumnByTableSecurityTest extends TestCase
{
    public function testGetColumnByTableRequestNormalizesValidPayload(): void
    {
        $this->assertSame(
            [
                'id_mysql_server' => 12,
                'schema' => 'shop_db',
                'table' => 'orders_2024',
            ],
            Cleaner::normalizeGetColumnByTableRequest(
                [' orders_2024 '],
                ['id_mysql_server' => ' 12 ', 'schema' => ' shop_db ']
            )
        );
    }

    #[DataProvider('invalidGetColumnByTableProvider')]
    public function testGetColumnByTableRequestRejectsSqlInjectionPayloads(array $param, array $get): void
    {
        $this->assertNull(Cleaner::normalizeGetColumnByTableRequest($param, $get));
    }

    public function testGetColumnByTableUsesIdentifierAllowlistBeforeSql(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Cleaner.php');
        $methodStart = strpos($controller, 'function getColumnByTable($param)');
        $normalizerStart = strpos($controller, 'public static function normalizeGetColumnByTableRequest', $methodStart);

        $this->assertIsInt($methodStart);
        $this->assertIsInt($normalizerStart);

        $methodBody = substr($controller, $methodStart, $normalizerStart - $methodStart);

        $this->assertStringContainsString('use App\\Library\\Security\\Identifier;', $controller);
        $this->assertStringContainsString('$request = self::normalizeGetColumnByTableRequest($param, $_GET);', $methodBody);
        $this->assertStringContainsString("self::sendCleanerGetColumnByTableError(400, 'Invalid cleaner column request');", $methodBody);
        $this->assertStringContainsString('"SELECT id,name FROM mysql_server WHERE id = ".$request[\'id_mysql_server\']." LIMIT 1;"', $methodBody);
        $this->assertStringContainsString('Identifier::quoteSqlIdentifier($request[\'schema\'])', $methodBody);
        $this->assertStringContainsString('Identifier::quoteSqlIdentifier($request[\'table\'])', $methodBody);
        $this->assertStringContainsString('$db_clean->sql_fetch_object($res2)', $methodBody);

        $this->assertStringNotContainsString('show index from `".$_GET[\'schema\']."`.`".$param[0]."`', $methodBody);
        $this->assertStringNotContainsString('sql_real_escape_string($_GET[\'id_mysql_server\'])', $methodBody);
        $this->assertStringNotContainsString('$db->sql_fetch_object($res2)', $methodBody);
    }

    public static function invalidGetColumnByTableProvider(): array
    {
        return [
            'missing server id' => [
                ['orders'],
                ['schema' => 'shop_db'],
            ],
            'server id injection' => [
                ['orders'],
                ['id_mysql_server' => '12 OR 1=1', 'schema' => 'shop_db'],
            ],
            'server id array' => [
                ['orders'],
                ['id_mysql_server' => ['12'], 'schema' => 'shop_db'],
            ],
            'schema backtick injection' => [
                ['orders'],
                ['id_mysql_server' => '12', 'schema' => 'shop_db`;DROP TABLE cleaner_main;--'],
            ],
            'schema expression' => [
                ['orders'],
                ['id_mysql_server' => '12', 'schema' => 'shop_db UNION SELECT'],
            ],
            'schema array' => [
                ['orders'],
                ['id_mysql_server' => '12', 'schema' => ['shop_db']],
            ],
            'table backtick injection' => [
                ['orders`;DROP TABLE cleaner_main;--'],
                ['id_mysql_server' => '12', 'schema' => 'shop_db'],
            ],
            'table expression' => [
                ['orders WHERE 1=1'],
                ['id_mysql_server' => '12', 'schema' => 'shop_db'],
            ],
            'table array' => [
                [['orders']],
                ['id_mysql_server' => '12', 'schema' => 'shop_db'],
            ],
            'empty table' => [
                [''],
                ['id_mysql_server' => '12', 'schema' => 'shop_db'],
            ],
        ];
    }
}
