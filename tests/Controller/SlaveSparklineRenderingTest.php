<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}

/**
 * Regression coverage for issue #742.
 *
 * The Slave/index sparkline (160×17 canvas inside a <td>) stopped rendering
 * after the Chart.js 2 → 4 migration in commit b1cc6a4. Root cause: the v4
 * config used `responsive: true` + `maintainAspectRatio: false`, which made
 * Chart sample the table-cell `clientHeight` — frequently 0 — and silently
 * draw nothing. Switching to `responsive: false` keeps the canvas at its
 * intrinsic 160×17 dimensions and restores the v2 behaviour.
 *
 * These tests inspect the JS that Slave::generateGraph injects into the
 * Glial JS DI container, without booting a real DOM or Chart.js.
 */
final class SlaveSparklineRenderingTest extends TestCase
{
    public function testGenerateGraphInjectsResponsiveFalseSparklineConfig(): void
    {
        $captured = $this->captureGeneratedSparklineJs();

        $this->assertNotSame('', $captured, 'Slave::generateGraph should emit JS for at least one slave row.');
        $this->assertStringContainsString(
            'responsive: false',
            $captured,
            'Sparkline must use responsive=false. Otherwise Chart.js v4 reads clientHeight=0 '
            . 'from the <td> container and silently draws an empty line (issue #742).'
        );
        $this->assertStringNotContainsString(
            'responsive: true',
            $captured,
            'No leftover responsive=true should sneak back into the sparkline config.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\bmaintainAspectRatio\s*:/',
            $captured,
            'maintainAspectRatio is meaningless when responsive=false; do not reintroduce the option.'
        );
    }

    public function testGenerateGraphLoadsChart4AndMomentAdapterInExpectedOrder(): void
    {
        $assets = $this->captureLoadedJsAssets();

        $this->assertSame(
            ['moment.js', 'chart-4.5.1.umd.min.js', 'chartjs-adapter-moment.min.js'],
            $assets,
            'moment.js must declare window.moment first, then Chart.js, then the adapter — '
            . 'otherwise Chart._adapters._date is never bound to moment and the time scale '
            . 'falls back to the broken default adapter.'
        );
    }

    public function testGenerateGraphPreservesDestroyBeforeNewChartGuard(): void
    {
        $captured = $this->captureGeneratedSparklineJs();

        $this->assertStringContainsString('Chart.getChart(canvas)', $captured);
        $this->assertStringContainsString('existing.destroy()', $captured);
        $this->assertLessThan(
            strpos($captured, 'new Chart('),
            strpos($captured, 'existing.destroy()'),
            'destroy() must run before new Chart(...) to avoid double-init on AJAX refresh.'
        );
    }

    private function captureGeneratedSparklineJs(): string
    {
        $jsDi = $this->newJsDi();
        $slave = $this->newSlaveWithJsDi($jsDi);

        $this->invokeGenerateGraph($slave, [
            [
                'id_mysql_server' => 42,
                'connection_name' => '',
                'graph' => "{x:new Date('2026-05-03 19:00:00'),y:0},{x:new Date('2026-05-03 19:01:00'),y:1}",
            ],
        ]);

        return implode("\n", $jsDi->code_javascript);
    }

    private function captureLoadedJsAssets(): array
    {
        $jsDi = $this->newJsDi();
        $slave = $this->newSlaveWithJsDi($jsDi);

        $this->invokeGenerateGraph($slave, [
            [
                'id_mysql_server' => 1,
                'connection_name' => '',
                'graph' => '{x:new Date(\'2026-05-03 19:00:00\'),y:0}',
            ],
        ]);

        return $jsDi->javascript;
    }

    private function newJsDi(): object
    {
        return new class {
            /** @var array<int,string> */
            public array $javascript = [];
            /** @var array<int,string> */
            public array $code_javascript = [];

            public function addJavascript(array $assets, string $note = ''): void
            {
                foreach ($assets as $asset) {
                    if (!in_array($asset, $this->javascript, true)) {
                        $this->javascript[] = $asset;
                    }
                }
            }

            public function code_javascript(string $code): void
            {
                $this->code_javascript[] = $code;
            }
        };
    }

    private function newSlaveWithJsDi(object $jsDi): Slave
    {
        $slave = (new ReflectionClass(Slave::class))->newInstanceWithoutConstructor();

        $reflection = new ReflectionClass(Slave::class);
        if ($reflection->hasProperty('di')) {
            $reflection->getProperty('di')->setValue($slave, ['js' => $jsDi]);
        }

        return $slave;
    }

    private function invokeGenerateGraph(Slave $slave, array $slaves): void
    {
        $method = (new ReflectionClass(Slave::class))->getMethod('generateGraph');
        $method->invoke($slave, $slaves);
    }
}
