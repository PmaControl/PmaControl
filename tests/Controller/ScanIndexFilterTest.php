<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ScanIndexFilterTest extends TestCase
{
    public function testScanIndexViewDoesNotEmitPost(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Scan/index.view.php');

        $this->assertIsString($view);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    public function testScanIndexControllerDoesNotReadPost(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Scan.php');

        $this->assertIsString($controller);
        $this->assertStringNotContainsString('$_POST', $controller);
    }
}
