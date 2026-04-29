<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MonitoringExplainViewSecurityTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = (string) file_get_contents(__DIR__ . '/../../App/view/Monitoring/explain.view.php');
    }

    public function testExplainViewDoesNotDumpDebugStructures(): void
    {
        self::assertDoesNotMatchRegularExpression('/^\s*(print_r|var_dump|var_export)\s*\(/m', $this->view);
        self::assertStringNotContainsString('$table_sql', $this->view);
    }

    public function testExplainViewEscapesTableCellValues(): void
    {
        self::assertStringContainsString(
            "htmlspecialchars((string) \$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')",
            $this->view
        );
    }
}
