<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1196 — pin the wiring of the *Binlog group commit* section
 * on slave/show.
 */
final class SlaveShowGroupCommitSectionTest extends TestCase
{
    private string $view;
    private string $controller;

    protected function setUp(): void
    {
        $v = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $c = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($v);
        $this->assertNotFalse($c);
        $this->view = $v;
        $this->controller = $c;
    }

    public function testControllerPopulatesGroupCommitRows(): void
    {
        $this->assertStringContainsString(
            "\$data['group_commit_rows'] = self::buildBinlogGroupCommitRows(",
            $this->controller,
            'Slave::show must populate $data[group_commit_rows]'
        );
        $this->assertStringContainsString(
            'private static function buildBinlogGroupCommitRows(',
            $this->controller,
            'helper buildBinlogGroupCommitRows must be defined'
        );
        $this->assertStringContainsString(
            '\\App\\Library\\BinlogGroupCommit::rows(',
            $this->controller,
            'controller must delegate classification to the helper'
        );
        $this->assertStringContainsString(
            "\$gtidCompat['slave_family'],",
            $this->controller,
            'controller must reuse the GTID compat slave_family classification'
        );
        $this->assertStringContainsString(
            "\$gtidCompat['master_family']",
            $this->controller,
            'controller must reuse the GTID compat master_family classification'
        );
    }

    public function testViewRendersGroupCommitTable(): void
    {
        $this->assertStringContainsString('Binlog group commit', $this->view);
        $this->assertStringContainsString('sv-group-commit-table', $this->view);
        // Two-column slave/master layout
        $this->assertStringContainsString('<?= __(\'Slave\') ?>', $this->view);
        $this->assertStringContainsString('<?= __(\'Master\') ?>', $this->view);
        // The 4 severity colours used by the cell badges.
        $this->assertStringContainsString("'ok'      => '#16a34a'", $this->view);
        $this->assertStringContainsString("'warn'    => '#d97706'", $this->view);
        $this->assertStringContainsString("'info'    => '#2563eb'", $this->view);
        $this->assertStringContainsString("'unknown' => '#94a3b8'", $this->view);
        // Tooltip wiring: Bootstrap-armed + native title=, with the
        // tooltip on the label AND on each cell badge.
        $this->assertStringContainsString('data-toggle="tooltip"', $this->view);
        $this->assertStringContainsString('title="<?= htmlspecialchars($gc[\'tooltip\']', $this->view);
        // Drift banner only when applicable
        $this->assertStringContainsString('!empty($gc[\'drift\'])', $this->view);
        $this->assertStringContainsString('drift_reason', $this->view);
    }

    public function testViewIsIdempotentWhenDataMissing(): void
    {
        // Like the durability section, the group_commit block must
        // hide entirely when no data is available rather than render
        // an empty table.
        $this->assertMatchesRegularExpression(
            "/<\\?php if \\(!empty\\(\\\$data\\['group_commit_rows'\\]\\)\\): \\?>/",
            $this->view
        );
    }

    public function testViewExposesPerCellVariableNameForDebugging(): void
    {
        // The label cell shows the variable name in monospace below
        // the human label, with the master variant when it differs
        // (cross-family case). Pin that contract.
        $this->assertStringContainsString(
            "htmlspecialchars(\$gc['slave']['var']",
            $this->view
        );
        $this->assertMatchesRegularExpression(
            "/\\\$gc\\['slave'\\]\\['var'\\] !== \\\$gc\\['master'\\]\\['var'\\]/",
            $this->view,
            'view must show master variant when var name differs across family'
        );
    }
}
