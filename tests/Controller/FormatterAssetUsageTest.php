<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FormatterAssetUsageTest extends TestCase
{
    public function testChartControllersLoadSharedFormatterAsset(): void
    {
        foreach ([
            'Archives.php',
            'Chartjs.php',
            'Detail.php',
            'PostMortem.php',
        ] as $controller) {
            $path = __DIR__.'/../../App/Controller/'.$controller;
            $this->assertFileExists($path);
            $this->assertStringContainsString('"formatters.js"', file_get_contents($path));
        }
    }

    public function testChartControllersDoNotDefineInlineFileConvertSize(): void
    {
        foreach ([
            'Archives.php',
            'Chartjs.php',
            'Detail.php',
            'PostMortem.php',
        ] as $controller) {
            $path = __DIR__.'/../../App/Controller/'.$controller;
            $this->assertStringNotContainsString('function FileConvertSize', file_get_contents($path));
        }
    }

    public function testSharedFormatterProvidesLegacyAlias(): void
    {
        $path = __DIR__.'/../../App/Webroot/js/formatters.js';
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('window.PmaFormat.bytes', $content);
        $this->assertStringContainsString('window.PmaFormat.number', $content);
        $this->assertStringContainsString('window.FileConvertSize', $content);
    }

    public function testCounterChartsKeepNumericFormatting(): void
    {
        foreach ([
            'Chartjs.php',
            'Detail.php',
        ] as $controller) {
            $path = __DIR__.'/../../App/Controller/'.$controller;
            $this->assertStringContainsString('PmaFormat.number', file_get_contents($path));
        }
    }
}
