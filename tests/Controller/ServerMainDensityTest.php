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
        self::assertStringContainsString('.sm-detail-row td { padding: 0 10px 4px 38px;', $this->source);
        self::assertStringContainsString('.sm-t td.sm-status { width: 4px; padding: 0;', $this->source);
    }

    public function testEnvironmentAndTagsUseDedicatedColumns(): void
    {
        self::assertStringContainsString('<th><?= __("Environment") ?></th>', $this->source);
        self::assertStringContainsString('<th><?= __("Tag") ?></th>', $this->source);
        self::assertStringContainsString('class="sm-env-cell"', $this->source);
        self::assertStringContainsString('class="sm-tag-cell"', $this->source);
        self::assertStringContainsString('colspan="12"', $this->source);
    }

    public function testStatusActionsMovedToDetailRowWithWorkerKillAction(): void
    {
        self::assertStringNotContainsString('<th><?= __("Status") ?></th>', $this->source);
        self::assertStringContainsString('class="sm-detail-actions"', $this->source);
        self::assertStringContainsString('class="sm-proc-label"', $this->source);
        self::assertStringContainsString('sv-dot warn halo', $this->source);
        self::assertStringContainsString('worker/killServerWorker/', $this->source);
        self::assertStringContainsString('method="post"', $this->source);
        self::assertStringContainsString('workerKillCsrfToken', $this->source);
    }

    public function testStatusDotPulseUsesBoxShadowVariables(): void
    {
        self::assertStringContainsString('.sv-dot.halo { animation: status-dot-pulse 1.5s infinite; }', $this->source);
        self::assertStringContainsString('@keyframes status-dot-pulse', $this->source);
        self::assertStringContainsString('70%  { box-shadow: 0 0 0 var(--size) var(--endColor); }', $this->source);
        self::assertStringNotContainsString('.sv-dot.halo::after', $this->source);
    }
}
