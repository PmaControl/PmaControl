<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ServerMainDensityTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(dirname(__DIR__, 2) . '/App/view/Server/main.view.php');
    }

    public function testServerMainRowsUseCompactVerticalPadding(): void
    {
        self::assertStringContainsString('.sm-t td { padding: 4px 10px;', $this->source);
        self::assertStringNotContainsString('.sm-t td { padding: 8px 10px;', $this->source);
    }

    public function testHeaderAndErrorRowsStayCompactButAligned(): void
    {
        self::assertStringContainsString('letter-spacing: .4px; padding: 6px 10px;', $this->source);
        self::assertStringContainsString('.sm-err-row td { padding: 0 10px 4px 38px;', $this->source);
        self::assertStringContainsString('.sm-t td.sm-status { width: 4px; padding: 0;', $this->source);
    }
}
