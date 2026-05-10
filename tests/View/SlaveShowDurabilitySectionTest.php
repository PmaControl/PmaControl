<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1185 — pin the wiring of the durability section on the
 * slave/show view: the controller must pass the rows in
 * `$data['durability_rows']` and the view must render them inside the
 * Actions card as a tooltipped table just below the Parallel threads
 * block.
 *
 * Grep-based contract checks — full controller boot needs Glial DB.
 */
final class SlaveShowDurabilitySectionTest extends TestCase
{
    public function testControllerPopulatesDurabilityRows(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($controller);
        $this->assertStringContainsString(
            "\$data['durability_rows'] = self::buildDurabilityRows(",
            $controller,
            'Slave::show must populate $data[durability_rows]'
        );
        $this->assertStringContainsString(
            'private static function buildDurabilityRows(',
            $controller,
            'helper buildDurabilityRows must be defined'
        );
        $this->assertStringContainsString(
            '\\App\\Library\\SlaveDurability::rows(',
            $controller,
            'controller must delegate value classification to SlaveDurability'
        );
        // Both legacy names (slave_preserve_commit_order,
        // master_info_repository) must be requested so MariaDB / 5.7
        // values do not show up as "n/a".
        $this->assertStringContainsString('slave_preserve_commit_order', $controller);
        $this->assertStringContainsString('master_info_repository', $controller);
    }

    public function testViewRendersDurabilitySection(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $this->assertNotFalse($view);

        $this->assertStringContainsString(
            "Durability & crash safety",
            $view,
            'view must render the new action group'
        );
        $this->assertStringContainsString('sv-durability-table', $view);
        $this->assertStringContainsString('sv-durability-badge', $view);

        // Color tokens for each severity level — the badge palette
        // must keep 4 distinct values so a future edit does not
        // accidentally collapse them.
        $this->assertStringContainsString("'ok'      => '#16a34a'", $view);
        $this->assertStringContainsString("'warn'    => '#d97706'", $view);
        $this->assertStringContainsString("'risk'    => '#dc2626'", $view);
        $this->assertStringContainsString("'unknown' => '#94a3b8'", $view);

        // Tooltip wiring (cursor:help + native title attribute, with
        // optional Bootstrap data-toggle to upgrade on hover).
        $this->assertStringContainsString('cursor:help', $view);
        $this->assertStringContainsString('title="<?= htmlspecialchars($row[\'tooltip\']', $view);
        $this->assertStringContainsString('data-toggle="tooltip"', $view);
    }

    public function testViewIsIdempotentWhenDataMissing(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        // Without $data['durability_rows'] the section must be skipped
        // (so the page still renders on legacy rows that predate the
        // migration of the metric).
        $this->assertMatchesRegularExpression(
            "/<\\?php if \\(!empty\\(\\\$data\\['durability_rows'\\]\\)\\): \\?>/",
            $view
        );
    }
}
