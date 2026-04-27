<?php

declare(strict_types=1);

use App\Controller\Compare;
use App\Library\Security\CompareMainSelection;
use PHPUnit\Framework\TestCase;

final class CompareIndexFilterTest extends TestCase
{
    public function testIndexAcceptsGetSelection(): void
    {
        $outcome = Compare::evaluateIndexRequest($this->validGet(), ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            CompareMainSelection::SERVER_ORIGINAL => 3,
            CompareMainSelection::SERVER_COMPARE => 4,
            CompareMainSelection::DATABASE_ORIGINAL => 'main_db',
            CompareMainSelection::DATABASE_COMPARE => 'main-db-copy',
        ], $outcome['selection']);
    }

    public function testIndexAcceptsEmptyGetSelection(): void
    {
        $outcome = Compare::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            CompareMainSelection::SERVER_ORIGINAL => null,
            CompareMainSelection::SERVER_COMPARE => null,
            CompareMainSelection::DATABASE_ORIGINAL => null,
            CompareMainSelection::DATABASE_COMPARE => null,
        ], $outcome['selection']);
    }

    public function testIndexRejectsResidualPostAndOtherMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = Compare::evaluateIndexRequest([], ['REQUEST_METHOD' => $method]);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexRejectsInvalidSelectionPayloads(): void
    {
        foreach ($this->invalidGets() as $get) {
            $outcome = Compare::evaluateIndexRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid compare selection', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testCompareIndexUsesGetOnlySharedSelection(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Compare.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Compare/index.view.php');
        $indexStart = strpos($controller, 'function index($params)');
        $checkConfigStart = strpos($controller, 'private function checkConfig');

        $this->assertNotFalse($indexStart);
        $this->assertNotFalse($checkConfigStart);
        $indexBody = substr($controller, $indexStart, $checkConfigStart - $indexStart);

        $this->assertStringContainsString('CompareMainSelection::evaluate($get, $server)', $controller);
        $this->assertStringContainsString('self::evaluateIndexRequest($_GET, $_SERVER)', $indexBody);
        $this->assertStringContainsString('CompareMainSelection::applyToGet($selection)', $indexBody);
        $this->assertStringContainsString('$selection[CompareMainSelection::SERVER_ORIGINAL]', $indexBody);
        $this->assertStringNotContainsString('$_POST', $indexBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $indexBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringContainsString('action="<?= LINK ?>compare/index"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
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
