<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}

/**
 * Regression coverage for issue #742 (final form: native canvas drawing).
 *
 * After three Chart.js iterations (#743 responsive=false, #744 ISO 8601 dates,
 * #745 drop time scale) the 160×17 sparkline still rendered nothing because
 * Chart.js v4 cannot lay out a chart in a 17px-tall canvas — its internal
 * layout box collapses even with all axes/legend disabled.
 *
 * The fix bypasses Chart.js entirely for this row and draws the polyline
 * directly with the 2D context. These tests inspect the JS injected by
 * Slave::generateGraph through the Glial JS DI container.
 */
final class SlaveSparklineRenderingTest extends TestCase
{
    public function testGenerateGraphDoesNotLoadChartJsOrMomentForSparkline(): void
    {
        $assets = $this->captureLoadedJsAssets();

        $this->assertSame(
            [],
            $assets,
            'Sparkline must not pull Chart.js, moment.js or any adapter — issue #742 final '
            . 'form draws the 160×17 line directly on the 2D context.'
        );
    }

    public function testGenerateGraphInjectsRawCanvasDrawingNotChartJs(): void
    {
        $captured = $this->captureGeneratedSparklineJs();

        $this->assertNotSame('', $captured, 'Slave::generateGraph should emit JS for at least one slave row.');
        $this->assertStringContainsString('getContext("2d")', $captured);
        $this->assertStringContainsString('ctx.stroke()', $captured);
        $this->assertStringContainsString('ctx.fill()', $captured);
        $this->assertStringNotContainsString(
            'new Chart(',
            $captured,
            'Sparkline must not instantiate Chart.js — Chart.js v4 cannot lay out a '
            . 'chart in a 17px-tall canvas (issue #742).'
        );
        $this->assertStringNotContainsString('Chart.getChart(', $captured);
        $this->assertStringNotContainsString('new Date(', $captured);
    }

    public function testGenerateGraphInjectsParsedYValuesArray(): void
    {
        $captured = $this->captureGeneratedSparklineJs();

        $this->assertMatchesRegularExpression(
            '/var\s+d\s*=\s*\[\s*0\s*,\s*1\.5\s*,\s*null\s*,\s*2\s*\]/',
            $captured,
            'Sparkline must receive a flat array of parsed numeric y values (or null '
            . 'for missing samples) extracted server-side from the SQL graph payload.'
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
