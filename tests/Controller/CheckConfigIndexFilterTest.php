<?php

declare(strict_types=1);

use App\Controller\CheckConfig;
use PHPUnit\Framework\TestCase;

final class CheckConfigIndexFilterTest extends TestCase
{
    public function testIndexAcceptsEmptyGetSelection(): void
    {
        $outcome = CheckConfig::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'source' => null,
            'ids' => [],
            'id_list' => '',
        ], $outcome['selection']);
    }

    public function testIndexAcceptsClusterGetSelection(): void
    {
        $outcome = CheckConfig::evaluateIndexRequest(
            ['mysql_cluster' => ['id' => '3,4']],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'source' => 'mysql_cluster',
            'ids' => [3, 4],
            'id_list' => '3,4',
        ], $outcome['selection']);
    }

    public function testIndexAcceptsServerGetSelectionArray(): void
    {
        $outcome = CheckConfig::evaluateIndexRequest(
            ['mysql_server' => ['id' => ['7', '8']]],
            ['REQUEST_METHOD' => 'HEAD']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'source' => 'mysql_server',
            'ids' => [7, 8],
            'id_list' => '7,8',
        ], $outcome['selection']);
    }

    public function testIndexTreatsEmptySubmittedSelectionAsInitialRender(): void
    {
        $outcome = CheckConfig::evaluateIndexRequest(
            ['mysql_cluster' => ['id' => '']],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'source' => null,
            'ids' => [],
            'id_list' => '',
        ], $outcome['selection']);
    }

    public function testIndexRejectsResidualPostAndOtherMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = CheckConfig::evaluateIndexRequest([], ['REQUEST_METHOD' => $method]);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexRejectsInvalidSelections(): void
    {
        foreach ($this->invalidGets() as $get) {
            $outcome = CheckConfig::evaluateIndexRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid check config selection', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testIndexUsesGetOnlyAndNormalizedServerIds(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/CheckConfig.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/CheckConfig/index.view.php');

        $indexStart = strpos($controller, 'public function index($param)');
        $databasesStart = strpos($controller, 'public function getDatabasesByServers', $indexStart);
        $indexBody = substr($controller, $indexStart, $databasesStart - $indexStart);

        $this->assertStringContainsString('use App\\Library\\Security\\ServerIdSelection;', $controller);
        $this->assertStringContainsString('self::evaluateIndexRequest($_GET, $_SERVER)', $indexBody);
        $this->assertStringContainsString('ServerIdSelection::normalizeList($get[$source][\'id\'])', $controller);
        $this->assertStringContainsString('ServerIdSelection::normalizeList($param[0] ?? null)', $controller);
        $this->assertStringContainsString('$selection[\'id_list\']', $indexBody);
        $this->assertStringContainsString('$selection[\'ids\']', $indexBody);
        $this->assertStringNotContainsString('$_POST', $indexBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $indexBody);
        $this->assertStringNotContainsString('header(\'location: \'.LINK.$this->getClass()', $indexBody);
        $this->assertStringNotContainsString('WHERE id in ($_GET', $indexBody);

        $this->assertSame(2, substr_count($view, 'method="get"'));
        $this->assertStringNotContainsString('method="POST"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    private function invalidGets(): array
    {
        return [
            ['mysql_cluster' => '3,4'],
            ['mysql_cluster' => ['id' => '3 OR 1=1']],
            ['mysql_cluster' => ['id' => '0,4']],
            ['mysql_cluster' => ['id' => '3,3']],
            ['mysql_cluster' => ['id' => [['3']]]],
            ['mysql_server' => ['id' => ['7', ['8']]]],
            ['mysql_cluster' => ['id' => '3'], 'mysql_server' => ['id' => ['7']]],
        ];
    }
}
