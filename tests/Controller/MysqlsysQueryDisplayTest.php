<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MysqlsysQueryDisplayTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = file_get_contents(__DIR__ . '/../../App/view/Mysqlsys/index.view.php');
    }

    public function testQueryColumnIsNormalizedBeforeFormattingAndMeasuring(): void
    {
        $this->assertStringContainsString('$query = (string)($val ?? \'\');', $this->view);
        $this->assertStringContainsString('SqlFormatter::format($query)', $this->view);
        $this->assertStringContainsString("mb_strlen(\$query, 'UTF-8')", $this->view);
        $this->assertStringNotContainsString('strlen($val)', $this->view);
        $this->assertStringNotContainsString('SqlFormatter::format($val)', $this->view);
    }

    public function testQueryExcerptIsEscapedBeforeDisplay(): void
    {
        $this->assertStringContainsString("mb_substr(\$query, 0, 32, 'UTF-8')", $this->view);
        $this->assertStringContainsString("mb_substr(\$query, -32, 32, 'UTF-8')", $this->view);
        $this->assertStringContainsString(
            "htmlspecialchars(\$queryExcerpt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')",
            $this->view
        );
        $this->assertStringNotContainsString('echo $query;', $this->view);
    }
}
