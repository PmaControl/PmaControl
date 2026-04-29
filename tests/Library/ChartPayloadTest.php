<?php

namespace Tests\Library;

use App\Library\ChartPayload;
use PHPUnit\Framework\TestCase;

final class ChartPayloadTest extends TestCase
{
    public function testColorPaletteCyclesPmmColors(): void
    {
        $colors = ChartPayload::colorPalette(7);

        $this->assertCount(7, $colors);
        $this->assertSame('rgba(37, 99, 235, 1)', $colors[0]['border']);
        $this->assertSame('rgba(37, 99, 235, 1)', $colors[6]['border']);
    }

    public function testDatasetBuildsPmmCompatibleShape(): void
    {
        $dataset = ChartPayload::dataset(
            'Queries',
            [1.0, 2.5],
            ['border' => 'rgba(37, 99, 235, 1)', 'fill' => 'rgba(37, 99, 235, 0.16)'],
            true
        );

        $this->assertSame('Queries', $dataset['label']);
        $this->assertSame([1.0, 2.5], $dataset['data']);
        $this->assertSame('rgba(37, 99, 235, 1)', $dataset['borderColor']);
        $this->assertSame('rgba(37, 99, 235, 0.16)', $dataset['backgroundColor']);
        $this->assertTrue($dataset['fill']);
        $this->assertSame(2, $dataset['borderWidth']);
        $this->assertSame(0.18, $dataset['tension']);
        $this->assertSame(0, $dataset['pointRadius']);
    }

    public function testLegacyExtractionBuildsJavascriptDatasetAndLegend(): void
    {
        $payload = ChartPayload::legacyExtraction(
            [
                [
                    'id_ts_variable' => 42,
                    'graph' => "{x:new Date('2026-04-29 10:00:00'),y:1}",
                    'min' => 1,
                    'max' => 10,
                    'avg' => 5.55,
                    'std' => 4,
                ],
            ],
            static function (array $row): string {
                return 'metric-' . $row['id_ts_variable'];
            }
        );

        $this->assertCount(1, $payload['datasets_js']);
        $this->assertSame('rgb(54, 162, 235)', $payload['legend'][0]['color']);
        $this->assertStringContainsString('label: "metric-42"', $payload['datasets_js'][0]);
        $this->assertStringContainsString("data: [{x:new Date('2026-04-29 10:00:00'),y:1}]", $payload['datasets_js'][0]);
        $this->assertStringContainsString('borderColor: "rgb(54, 162, 235)"', $payload['datasets_js'][0]);
        $this->assertStringContainsString('Min : 1 - Max : 10 - Avg :  5.55', $payload['tooltip_js']);
        $this->assertStringContainsString('Std : 2', $payload['tooltip_js']);
    }

    public function testLegacyExtractionCanFormatAggregateValues(): void
    {
        $payload = ChartPayload::legacyExtraction(
            [
                [
                    'id_mysql_server' => 1,
                    'graph' => "{x:new Date('2026-04-29 10:00:00'),y:1024}",
                    'min' => 1024,
                    'max' => 2048,
                    'avg' => 1536,
                    'std' => 9,
                ],
            ],
            static function (): string {
                return 'server-1';
            },
            [
                'alpha' => 0.1,
                'aggregate_formatter' => static function ($value): string {
                    return $value . ' B';
                },
            ]
        );

        $this->assertStringContainsString('backgroundColor: "rgba(54, 162, 235, 0.1)"', $payload['datasets_js'][0]);
        $this->assertStringContainsString('Min : 1024 B - Max : 2048 B - Avg :  1536 B', $payload['tooltip_js']);
        $this->assertStringContainsString('Std : 3', $payload['tooltip_js']);
    }
}
