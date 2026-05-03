<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}

/**
 * Regression coverage for issue #742 (final form, Option C).
 *
 * The Slave/index sparkline went through three iterations after the Chart.js
 * 2 → 4 migration:
 *   - A (#743) — responsive=false to avoid the table-cell clientHeight=0 trap.
 *   - B (#744) — strict ISO 8601 dates so `new Date(...)` parses identically.
 *   - C (this) — drop the time scale altogether: Chart.js gets a flat array
 *     of y values and the default category x-axis. No moment.js, no adapter,
 *     no implementation-defined date parsing.
 *
 * These tests inspect the JS injected by Slave::generateGraph through the
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

    public function testGenerateGraphLoadsChart4OnlyWithoutMomentOrAdapter(): void
    {
        $assets = $this->captureLoadedJsAssets();

        $this->assertSame(
            ['chart-4.5.1.umd.min.js'],
            $assets,
            'Sparkline must not pull moment.js or the moment adapter — Option C drops the '
            . 'time scale and feeds Chart.js a flat array of values, so the time-axis '
            . 'plumbing is no longer relevant (issue #742).'
        );
    }

    public function testGenerateGraphInjectsFlatYValuesArrayWithoutXyObjects(): void
    {
        $captured = $this->captureGeneratedSparklineJs();

        $this->assertMatchesRegularExpression(
            '/data:\s*\[\s*0\s*,\s*1\.5\s*,\s*null\s*,\s*2\s*\]/',
            $captured,
            'data must be a flat array of numeric values (or null for missing samples).'
        );
        $this->assertStringNotContainsString(
            'new Date(',
            $captured,
            'No `new Date(...)` should remain in the sparkline payload — Option C parses '
            . 'the y values out server-side and feeds Chart.js a flat array.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/scales\s*:\s*\{[^}]*type\s*:\s*"time"/s',
            $captured,
            'Sparkline must not configure scales.x.type = "time" anymore.'
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

    public function testExtractSparklineYValuesParsesPayloadFromExtraction(): void
    {
        $payload = "{x:new Date('2026-05-03T19:00:00'),y:0},"
            . "{x:new Date('2026-05-03T19:01:00'),y:1.5},"
            . "{x:new Date('2026-05-03T19:02:00'),y:NULL},"
            . "{x:new Date('2026-05-03T19:03:00'),y:2}";

        $values = $this->invokeExtractSparklineYValues($payload);

        $this->assertSame(['0', '1.5', 'null', '2'], $values);
    }

    public function testExtractSparklineYValuesReturnsEmptyForEmptyOrInvalidPayload(): void
    {
        $this->assertSame([], $this->invokeExtractSparklineYValues(''));
        $this->assertSame([], $this->invokeExtractSparklineYValues('not a graph string'));
    }

    private function captureGeneratedSparklineJs(): string
    {
        $jsDi = $this->newJsDi();
        $slave = $this->newSlaveWithJsDi($jsDi);

        $this->invokeGenerateGraph($slave, [
            [
                'id_mysql_server' => 42,
                'connection_name' => '',
                'graph' => "{x:new Date('2026-05-03T19:00:00'),y:0},"
                    . "{x:new Date('2026-05-03T19:01:00'),y:1.5},"
                    . "{x:new Date('2026-05-03T19:02:00'),y:NULL},"
                    . "{x:new Date('2026-05-03T19:03:00'),y:2}",
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
                'graph' => "{x:new Date('2026-05-03T19:00:00'),y:0}",
            ],
        ]);

        return $jsDi->javascript;
    }

    /**
     * @return array<int,string>
     */
    private function invokeExtractSparklineYValues(string $payload): array
    {
        $method = (new ReflectionClass(Slave::class))->getMethod('extractSparklineYValues');

        return $method->invoke(null, $payload);
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
