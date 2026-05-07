<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Pin the wiring between "Load previous day" and the lag-chart
 * drag-select used to populate Binlog Analysis (#818).
 *
 * The drag-select is bound once at page load on existing
 * `.sv-chart-wrap canvas` elements. Charts prepended by Load
 * previous day must re-arm the binding via the helper exposed on
 * `window.attachLagDragSelectAll`. If either side of the contract
 * disappears, the regression silently returns.
 */
final class SlaveLoadPreviousDayDragSelectTest extends TestCase
{
    public function testControllerCallsAttachLagDragSelectAllAfterPrepending(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($controller);

        // The success handler of the AJAX `slave/showGraphDay` call must
        // re-arm the drag-select on the newly prepended canvases.
        $this->assertStringContainsString('window.attachLagDragSelectAll()', $controller);
    }

    public function testShowViewExposesAttachLagDragSelectAllOnWindow(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $this->assertNotFalse($view);

        // The view must define the helper and publish it on window so the
        // controller's AJAX handler can call it.
        $this->assertStringContainsString('function attachLagDragSelectAll()', $view);
        $this->assertStringContainsString('window.attachLagDragSelectAll = attachLagDragSelectAll', $view);

        // Idempotency sentinel — calling the helper twice must not
        // double-bind listeners on the same canvas.
        $this->assertStringContainsString("dragSelectBound", $view);
    }
}
