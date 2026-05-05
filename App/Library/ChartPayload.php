<?php

namespace App\Library;

final class ChartPayload
{
    /**
     * @return array<int,array<string,string>>
     */
    public static function colorPalette(int $count): array
    {
        $palette = [
            ['border' => 'rgba(37, 99, 235, 1)', 'fill' => 'rgba(37, 99, 235, 0.16)'],
            ['border' => 'rgba(5, 150, 105, 1)', 'fill' => 'rgba(5, 150, 105, 0.16)'],
            ['border' => 'rgba(220, 38, 38, 1)', 'fill' => 'rgba(220, 38, 38, 0.16)'],
            ['border' => 'rgba(245, 158, 11, 1)', 'fill' => 'rgba(245, 158, 11, 0.16)'],
            ['border' => 'rgba(124, 58, 237, 1)', 'fill' => 'rgba(124, 58, 237, 0.16)'],
            ['border' => 'rgba(8, 145, 178, 1)', 'fill' => 'rgba(8, 145, 178, 0.16)'],
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
    }

    /**
     * @param array<int,mixed> $data
     * @param array<string,string> $color
     * @return array<string,mixed>
     */
    public static function dataset(string $label, array $data, array $color, bool $fill = false): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $color['border'],
            'backgroundColor' => $color['fill'],
            'fill' => $fill,
            'borderWidth' => 2,
            'tension' => 0.18,
            'pointRadius' => 0,
        ];
    }

    /**
     * @param mixed $rows
     * @param callable $labelResolver Receives one Extraction row and returns the dataset label.
     * @param array<string,mixed> $options
     * @return array{datasets_js:array<int,string>,legend:array<int,array<string,mixed>>,tooltip_js:string}
     */
    public static function legacyExtraction($rows, callable $labelResolver, array $options = []): array
    {
        $datasets = [];
        $legend = [];
        $tooltip = "var agregat = []\n";
        $alpha = isset($options['alpha']) ? (float)$options['alpha'] : 0.2;
        $aggregateFormatter = $options['aggregate_formatter'] ?? null;
        $index = 0;

        if (!is_array($rows) && !$rows instanceof \Traversable) {
            return [
                'datasets_js' => $datasets,
                'legend' => $legend,
                'tooltip_js' => $tooltip,
            ];
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $colors = self::legacyColor($index, $alpha);
            $label = (string)$labelResolver($row);
            $graph = isset($row['graph']) ? (string)$row['graph'] : '';

            $legendRow = $row;
            $legendRow['color'] = $colors['border'];
            $legend[] = $legendRow;

            $datasets[] = self::legacyDatasetJavascript($label, $graph, $colors['border'], $colors['fill']);
            $tooltip .= self::legacyAggregateJavascript($index, $row, $aggregateFormatter);

            $index++;
        }

        return [
            'datasets_js' => $datasets,
            'legend' => $legend,
            'tooltip_js' => $tooltip,
        ];
    }

    /**
     * @return array<string,string>
     */
    private static function legacyColor(int $index, float $alpha): array
    {
        $palette = [
            ['rgb' => '54, 162, 235'],
            ['rgb' => '255, 99, 132'],
            ['rgb' => '255, 205, 86'],
            ['rgb' => '75, 192, 192'],
            ['rgb' => '153, 102, 255'],
            ['rgb' => '201, 203, 207'],
            ['rgb' => '255, 159, 64'],
        ];

        $rgb = $palette[$index % count($palette)]['rgb'];

        return [
            'border' => 'rgb(' . $rgb . ')',
            'fill' => 'rgba(' . $rgb . ', ' . $alpha . ')',
        ];
    }

    private static function legacyDatasetJavascript(string $label, string $graph, string $borderColor, string $backgroundColor): string
    {
        return '{
                label: ' . self::encodeJavascript($label) . ',
                data: [' . $graph . '],
                borderColor: ' . self::encodeJavascript($borderColor) . ',
                fill:true,
                pointBackgroundColor: ' . self::encodeJavascript($backgroundColor) . ',
                borderWidth: 2,
                pointRadius: 0,
                lineTension: 0,
                backgroundColor: ' . self::encodeJavascript($backgroundColor) . ',
                interpolate: true,
                showLine: true,
            }';
    }

    /**
     * @param array<string,mixed> $row
     * @param callable|null $aggregateFormatter
     */
    private static function legacyAggregateJavascript(int $index, array $row, $aggregateFormatter): string
    {
        $min = $row['min'] ?? 0;
        $max = $row['max'] ?? 0;
        $avg = $row['avg'] ?? 0;
        $std = round(sqrt(max(0, (float)($row['std'] ?? 0))), 2);

        if (is_callable($aggregateFormatter)) {
            $min = (string)call_user_func($aggregateFormatter, $min);
            $max = (string)call_user_func($aggregateFormatter, $max);
            $avg = (string)call_user_func($aggregateFormatter, $avg);
        } else {
            $min = (string)round((float)$min, 0);
            $max = (string)round((float)$max, 0);
            $avg = (string)round((float)$avg, 2);
        }

        $value = " -\tMin : " . $min . " - Max : " . $max . " - Avg :  " . $avg . " -\tStd : " . $std;

        return 'agregat["' . $index . '"] = ' . self::encodeJavascript($value) . "\n";
    }

    private static function encodeJavascript(string $value): string
    {
        return (string)json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
