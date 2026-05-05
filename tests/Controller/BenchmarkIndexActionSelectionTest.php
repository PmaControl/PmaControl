<?php

declare(strict_types=1);

use App\Controller\Benchmark;
use App\Library\Security\ControllerActionSelection;
use PHPUnit\Framework\TestCase;

final class BenchmarkIndexActionSelectionTest extends TestCase
{
    public function testBenchmarkIndexAcceptsOnlyMenuTabs(): void
    {
        foreach (['bench', 'current', 'graph'] as $tab) {
            $this->assertSame($tab, Benchmark::evaluateIndexTab([$tab]));
        }
    }

    public function testBenchmarkIndexRejectsControllerMethodTrampolines(): void
    {
        foreach ($this->unsafeTabs() as $param) {
            $this->assertSame('graph', Benchmark::evaluateIndexTab($param));
        }
    }

    public function testSharedControllerActionSelectionUsesStrictAllowList(): void
    {
        $allowed = ['bench', 'current', 'graph'];

        $this->assertSame('current', ControllerActionSelection::normalize(' current ', $allowed, 'graph'));
        $this->assertSame('graph', ControllerActionSelection::normalize('install', $allowed, 'graph'));
        $this->assertSame('graph', ControllerActionSelection::normalize(['bench'], $allowed, 'graph'));
        $this->assertSame('graph', ControllerActionSelection::normalize('', $allowed, 'graph'));
    }

    public function testSharedControllerActionSelectionRequiresAValidDefault(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ControllerActionSelection::normalize('bench', ['bench'], 'graph');
    }

    public function testBenchmarkIndexNoLongerUsesGetPathAsActionSource(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Benchmark.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Benchmark/index.view.php');

        $indexStart = strpos($controller, 'public function index($param)');
        $evaluateStart = strpos($controller, 'public static function benchmarkIndexTabs');
        $indexBody = substr($controller, $indexStart, $evaluateStart - $indexStart);

        $this->assertStringContainsString('use App\\Library\\Security\\ControllerActionSelection;', $controller);
        $this->assertStringContainsString('self::evaluateIndexTab($param, array_keys($data[\'menu\'])', $indexBody);
        $this->assertStringContainsString('$data[\'selected_benchmark_tab\']  = $selectedTab;', $indexBody);
        $this->assertStringNotContainsString('$_GET[\'path\']', $indexBody);
        $this->assertStringNotContainsString('$_GET["path"]', $indexBody);

        $this->assertStringContainsString('ControllerActionSelection::normalize(', $view);
        $this->assertStringContainsString(
            'FactoryController::addNode("benchmark", $selectedBenchmarkTab, array())',
            $view
        );
        $this->assertStringNotContainsString('$_GET[\'path\']', $view);
        $this->assertStringNotContainsString('$_GET["path"]', $view);
        $this->assertStringNotContainsString('explode(\'/\', $_GET[\'path\'])', $view);
        $this->assertStringNotContainsString('$method = end($elems)', $view);
    }

    private function unsafeTabs(): array
    {
        return [
            [],
            [''],
            ['install'],
            ['uninstall'],
            ['queue'],
            ['run'],
            ['testError'],
            ['bench/../../install'],
            [['bench']],
        ];
    }
}
