<?php

declare(strict_types=1);

if (!defined('DATA')) {
    define('DATA', sys_get_temp_dir().'/pmacontrol-data/');
}
if (!defined('TMP')) {
    define('TMP', sys_get_temp_dir().'/pmacontrol-tmp/');
}

use App\Controller\Cleaner;
use PHPUnit\Framework\TestCase;

final class ScanCleanerSignedCacheTest extends TestCase
{
    public function testScanCacheUsesSignedJsonAndNoPhpSerialization(): void
    {
        $source = (string) file_get_contents(__DIR__.'/../../App/Controller/Scan.php');

        $this->assertStringContainsString('use App\\Library\\Security\\SignedJsonCache;', $source);
        $this->assertStringContainsString('TMP . "data/scan.json"', $source);
        $this->assertStringContainsString('SignedJsonCache::read', $source);
        $this->assertStringContainsString('SignedJsonCache::write', $source);
        $this->assertStringNotContainsString('unserialize(', $source);
        $this->assertStringNotContainsString('serialize($this)', $source);
        $this->assertStringNotContainsString('scan.ser', $source);
    }

    public function testCleanerCacheUsesSignedJsonAndNoPhpSerialization(): void
    {
        $source = (string) file_get_contents(__DIR__.'/../../App/Controller/Cleaner.php');

        $this->assertStringContainsString('use App\\Library\\Security\\SignedJsonCache;', $source);
        $this->assertStringContainsString('orderby_".$this->id_cleaner.".json', $source);
        $this->assertStringContainsString('SignedJsonCache::read', $source);
        $this->assertStringContainsString('SignedJsonCache::write', $source);
        $this->assertStringNotContainsString('unserialize(', $source);
        $this->assertStringNotContainsString('serialize($this)', $source);
        $this->assertStringNotContainsString('orderby_".$this->id_cleaner.".ser', $source);
    }

    public function testLegacyGetOrderByNoLongerTouchesCache(): void
    {
        $source = (string) file_get_contents(__DIR__.'/../../App/Controller/Cleaner.php');
        $body = $this->extractBetween($source, 'public function getOrderBy($param)', 'private function delete_rows');

        $this->assertStringNotContainsString('path_to_orderby_tmp', $body);
        $this->assertStringNotContainsString('SignedJsonCache', $body);
        $this->assertStringNotContainsString('file_put_contents', $body);
        $this->assertStringNotContainsString('unserialize(', $body);
        $this->assertStringNotContainsString('serialize($this)', $body);
    }

    public function testCleanerCachePathUsesJsonExtension(): void
    {
        $cleaner = new Cleaner('Controller', 'View', []);
        $cleaner->id_cleaner = 42;

        $reflection = new ReflectionClass($cleaner);
        $method = $reflection->getMethod('setCacheFile');
        $method->invoke($cleaner);

        $property = $reflection->getProperty('path_to_orderby_tmp');

        $this->assertSame(TMP.'cleaner/orderby_42.json', $property->getValue($cleaner));
    }

    private function extractBetween(string $source, string $startNeedle, string $endNeedle): string
    {
        $start = strpos($source, $startNeedle);
        $this->assertIsInt($start);
        $end = strpos($source, $endNeedle, $start);
        $this->assertIsInt($end);

        return substr($source, $start, $end - $start);
    }
}
