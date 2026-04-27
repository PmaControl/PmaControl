<?php

declare(strict_types=1);

use App\Controller\Database;
use PHPUnit\Framework\TestCase;

final class DatabaseDataFilterTest extends TestCase
{
    public function testDataRequestAllowsEmptyGet(): void
    {
        $outcome = Database::evaluateDataRequest([], ['REQUEST_METHOD' => 'GET']);
        $missingMethodOutcome = Database::evaluateDataRequest([], []);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server__original' => null,
            'id_mysql_server__compare' => null,
            'database__original' => null,
            'database__compare' => null,
        ], $outcome['selection']);
        $this->assertSame(200, $missingMethodOutcome['status']);
    }

    public function testDataRequestAllowsHead(): void
    {
        $outcome = Database::evaluateDataRequest([], ['REQUEST_METHOD' => 'HEAD']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testDataRequestNormalizesValidGetSelection(): void
    {
        $outcome = Database::evaluateDataRequest($this->validGet(), ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server__original' => 7,
            'id_mysql_server__compare' => 8,
            'database__original' => 'app_db',
            'database__compare' => 'app-db-copy',
        ], $outcome['selection']);
    }

    public function testDataRequestRejectsPost(): void
    {
        $outcome = Database::evaluateDataRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
        $this->assertNull($outcome['selection']);

        $putOutcome = Database::evaluateDataRequest([], ['REQUEST_METHOD' => 'PUT']);
        $this->assertSame(405, $putOutcome['status']);
        $this->assertSame('GET, HEAD', $putOutcome['headers']['Allow']);
    }

    public function testDataRequestRejectsHostileGetSelection(): void
    {
        foreach ($this->invalidGets() as $get) {
            $outcome = Database::evaluateDataRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid database data selection', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testDataControllerAndViewUseGetFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Database/data.view.php');
        $dataStart = strpos($controller, 'function data($param)');
        $dataCompareStart = strpos($controller, 'public function dataCompate($param)');

        $this->assertNotFalse($dataStart);
        $this->assertNotFalse($dataCompareStart);
        $dataBody = substr($controller, $dataStart, $dataCompareStart - $dataStart);

        $this->assertStringContainsString('evaluateDataRequest($_GET, $_SERVER)', $dataBody);
        $this->assertStringContainsString('Identifier::isDatabaseName($text)', $dataBody);
        $this->assertStringContainsString('$selection[\'database__original\']', $dataBody);
        $this->assertStringNotContainsString('$_POST', $dataBody);
        $this->assertStringNotContainsString('header(\'location: \'.LINK.\'database/compare/', $dataBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    private function validGet(): array
    {
        return [
            'compare_main' => [
                'id_mysql_server__original' => '7',
                'id_mysql_server__compare' => '8',
                'database__original' => ' app_db ',
                'database__compare' => ' app-db-copy ',
            ],
        ];
    }

    private function invalidGets(): array
    {
        $base = $this->validGet();
        $invalidId = $base;
        $invalidId['compare_main']['id_mysql_server__original'] = '7 OR 1=1';
        $zeroId = $base;
        $zeroId['compare_main']['id_mysql_server__compare'] = '0';
        $badDatabase = $base;
        $badDatabase['compare_main']['database__original'] = "app'db";
        $arrayDatabase = $base;
        $arrayDatabase['compare_main']['database__compare'] = ['app_db'];
        $unknownField = $base;
        $unknownField['compare_main']['unexpected'] = '1';

        return [
            ['compare_main' => 'invalid'],
            $invalidId,
            $zeroId,
            $badDatabase,
            $arrayDatabase,
            $unknownField,
        ];
    }
}
