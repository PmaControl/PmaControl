<?php

declare(strict_types=1);

use App\Controller\Benchmark;
use App\Library\Security\PositiveIntegerSelection;
use App\Library\Security\ServerIdSelection;
use PHPUnit\Framework\TestCase;

final class BenchmarkGraphFilterTest extends TestCase
{
    public function testGraphAcceptsEmptyGetSelection(): void
    {
        $outcome = Benchmark::evaluateGraphRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'ids' => [],
            'id_list' => '',
            'query_value' => '',
        ], $outcome['selection']);
    }

    public function testGraphAcceptsGetArraySelection(): void
    {
        $outcome = Benchmark::evaluateGraphRequest(
            ['benchmark' => '1', 'benchmark_main' => ['id' => ['7', '8']]],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'ids' => [7, 8],
            'id_list' => '7,8',
            'query_value' => '[7,8]',
        ], $outcome['selection']);
    }

    public function testGraphAcceptsLegacyBracketedRouteSelection(): void
    {
        $outcome = Benchmark::evaluateGraphRequest(
            ['benchmark_main' => ['id' => '[12,13]']],
            ['REQUEST_METHOD' => 'HEAD']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'ids' => [12, 13],
            'id_list' => '12,13',
            'query_value' => '[12,13]',
        ], $outcome['selection']);
    }

    public function testGraphTreatsEmptySubmittedSelectionAsInitialRender(): void
    {
        foreach ([['benchmark_main' => ['id' => '']], ['benchmark_main' => ['id' => []]]] as $get) {
            $outcome = Benchmark::evaluateGraphRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(200, $outcome['status']);
            $this->assertSame([], $outcome['selection']['ids']);
        }
    }

    public function testGraphRejectsResidualPostAndOtherUnsafeMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = Benchmark::evaluateGraphRequest(
                ['benchmark_main' => ['id' => ['7']]],
                ['REQUEST_METHOD' => $method]
            );

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testGraphRejectsInvalidSelectionsBeforeSql(): void
    {
        foreach ($this->invalidSelections() as $get) {
            $outcome = Benchmark::evaluateGraphRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid benchmark graph selection', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testPositiveIntegerSelectionNormalizesSharedIdFormats(): void
    {
        $this->assertSame([1, 2], PositiveIntegerSelection::normalizeList(['1', '2']));
        $this->assertSame([1, 2], PositiveIntegerSelection::normalizeList('1,2'));
        $this->assertSame([1, 2], PositiveIntegerSelection::normalizeList('[1,2]'));
        $this->assertSame('1,2', PositiveIntegerSelection::toCsv([1, 2]));
        $this->assertSame('[1,2]', PositiveIntegerSelection::toBracketedList([1, 2]));

        $this->assertNull(PositiveIntegerSelection::normalizeList('1 OR 1=1'));
        $this->assertNull(PositiveIntegerSelection::normalizeList('[1,1]'));
        $this->assertNull(PositiveIntegerSelection::normalizeList(['1', ['2']]));
    }

    public function testServerIdSelectionDelegatesToSharedPositiveIntegerSelection(): void
    {
        $serverIdSelection = (string) file_get_contents(__DIR__ . '/../../App/Library/Security/ServerIdSelection.php');

        $this->assertSame([4, 5], ServerIdSelection::normalizeList('[4,5]'));
        $this->assertSame('4,5', ServerIdSelection::toCsv([4, 5]));
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeList($raw, $maxIds)', $serverIdSelection);
        $this->assertStringContainsString('PositiveIntegerSelection::toCsv($ids)', $serverIdSelection);
    }

    public function testGraphUsesGetOnlyAndNormalizedBenchmarkIds(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Benchmark.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Benchmark/graph.view.php');

        $graphStart = strpos($controller, 'public function graph()');
        $evaluateStart = strpos($controller, 'public static function evaluateGraphRequest');
        $graphBody = substr($controller, $graphStart, $evaluateStart - $graphStart);

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('self::evaluateGraphRequest($_GET, $_SERVER)', $graphBody);
        $this->assertStringContainsString('$id_to_take = $selection[\'id_list\'];', $graphBody);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeList($get[\'benchmark_main\'][\'id\'], 100)', $controller);
        $this->assertStringContainsString('PositiveIntegerSelection::toCsv($ids)', $controller);
        $this->assertStringContainsString('PositiveIntegerSelection::toBracketedList($ids)', $controller);
        $this->assertStringNotContainsString('$_POST', $graphBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $graphBody);
        $this->assertStringNotContainsString('json_decode($_GET[\'benchmark_main\'][\'id\'])', $graphBody);

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
    }

    private function invalidSelections(): array
    {
        return [
            ['benchmark_main' => '7,8'],
            ['benchmark_main' => ['id' => '7 OR 1=1']],
            ['benchmark_main' => ['id' => '0,8']],
            ['benchmark_main' => ['id' => '[7,7]']],
            ['benchmark_main' => ['id' => ['7', ['8']]]],
            ['benchmark_main' => ['id' => ['7', '8'], 'extra' => '1']],
            ['benchmark_main' => ['id' => '{"0":7}']],
            ['benchmark_main' => ['id' => '[7,\"8 OR 1=1\"]']],
        ];
    }
}
