<?php

declare(strict_types=1);

use App\Controller\CompareConfig;
use App\Library\Security\CompareMainSelection;
use PHPUnit\Framework\TestCase;

final class CompareConfigIndexFilterTest extends TestCase
{
    public function testIndexAcceptsGetSelection(): void
    {
        $outcome = CompareConfig::evaluateIndexRequest($this->validGet(), ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            CompareMainSelection::SERVER_ORIGINAL => 3,
            CompareMainSelection::SERVER_COMPARE => 4,
            CompareMainSelection::DATABASE_ORIGINAL => 'main_db',
            CompareMainSelection::DATABASE_COMPARE => 'main-db-copy',
        ], $outcome['selection']);
        $this->assertTrue(CompareMainSelection::isComplete($outcome['selection']));
        $this->assertSame(
            'compare_main:id_mysql_server__original:3/compare_main:id_mysql_server__compare:4/compare_main:database__original:main_db/compare_main:database__compare:main-db-copy',
            CompareMainSelection::toRoute($outcome['selection'])
        );
    }

    public function testIndexAcceptsEmptyGetSelection(): void
    {
        $outcome = CompareConfig::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            CompareMainSelection::SERVER_ORIGINAL => null,
            CompareMainSelection::SERVER_COMPARE => null,
            CompareMainSelection::DATABASE_ORIGINAL => null,
            CompareMainSelection::DATABASE_COMPARE => null,
        ], $outcome['selection']);
        $this->assertFalse(CompareMainSelection::isComplete($outcome['selection']));
        $this->assertSame('', CompareMainSelection::toRoute($outcome['selection']));
    }

    public function testIndexRejectsResidualPostAndOtherMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = CompareConfig::evaluateIndexRequest([], ['REQUEST_METHOD' => $method]);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexRejectsInvalidSelectionPayloads(): void
    {
        foreach ($this->invalidGets() as $get) {
            $outcome = CompareConfig::evaluateIndexRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid compare selection', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testCompareConfigIndexUsesGetOnlySharedSelection(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/CompareConfig.php');
        $indexStart = strpos($controller, 'function index($param)');
        $evaluateStart = strpos($controller, 'public static function evaluateIndexRequest');

        $this->assertNotFalse($indexStart);
        $this->assertNotFalse($evaluateStart);
        $indexBody = substr($controller, $indexStart, $evaluateStart - $indexStart);

        $this->assertStringContainsString('CompareMainSelection::evaluate($get, $server)', $controller);
        $this->assertStringContainsString('self::evaluateIndexRequest($_GET, $_SERVER)', $indexBody);
        $this->assertStringContainsString('CompareMainSelection::toRoute($indexRequest[\'selection\'])', $indexBody);
        $this->assertStringContainsString('$this->view = false;', $indexBody);
        $this->assertStringContainsString("header('location: ' . LINK . 'compare/index'", $indexBody);
        $this->assertStringNotContainsString('$_POST', $indexBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $indexBody);
    }

    public function testCompareConfigNoLongerContainsDuplicatedCompareEngine(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/CompareConfig.php');

        foreach ([
            'function checkConfig',
            'function analyse',
            'function compareTable',
            'function execMulti',
            'function compareListObject',
            'function menu',
            'function generateGet',
            'function getObjectDiff',
            'function compareObject',
            'function getDatabaseByServer',
            'function getDbLinkFromId',
        ] as $method) {
            $this->assertStringNotContainsString($method, $controller);
        }
    }

    private function validGet(): array
    {
        return [
            'compare_main' => [
                'id_mysql_server__original' => '3',
                'id_mysql_server__compare' => '4',
                'database__original' => ' main_db ',
                'database__compare' => 'main-db-copy',
            ],
        ];
    }

    private function invalidGets(): array
    {
        $base = $this->validGet();
        $nonArrayGroup = ['compare_main' => 'id_mysql_server__original=3'];
        $unknownField = $base;
        $unknownField['compare_main']['unexpected'] = '1';
        $badOriginalServer = $base;
        $badOriginalServer['compare_main']['id_mysql_server__original'] = '3 OR 1=1';
        $zeroCompareServer = $base;
        $zeroCompareServer['compare_main']['id_mysql_server__compare'] = '0';
        $badDatabase = $base;
        $badDatabase['compare_main']['database__original'] = 'main.db';
        $longDatabase = $base;
        $longDatabase['compare_main']['database__compare'] = str_repeat('a', 65);
        $arrayDatabase = $base;
        $arrayDatabase['compare_main']['database__compare'] = ['main_db'];

        return [
            $nonArrayGroup,
            $unknownField,
            $badOriginalServer,
            $zeroCompareServer,
            $badDatabase,
            $longDatabase,
            $arrayDatabase,
        ];
    }
}
