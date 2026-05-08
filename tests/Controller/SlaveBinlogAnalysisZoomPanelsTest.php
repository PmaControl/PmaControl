<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Pin the wiring that recomputes the General Info / DML Volume /
 * Risk Factors / Top tables / treemap panels on every zoom of the
 * Binlog Analysis charts (#826).
 *
 * The full computation is JS-only and lives in
 * App/view/Slave/show.view.php; this test ensures the helpers stay
 * factored and the four zoom hooks (KB/s, Txn/s, file timeline,
 * Reset zoom) all route through `rebuildPanelsFromZoom` so the
 * panels can never silently drift back to "treemap-only".
 */
final class SlaveBinlogAnalysisZoomPanelsTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $loaded = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $this->assertNotFalse($loaded);
        $this->view = $loaded;
    }

    public function testHelpersAreDefined(): void
    {
        // The two helpers that make the panels recomputable.
        $this->assertStringContainsString(
            'function computeWindowAggregates(d, win)',
            $this->view,
            'Window-aware aggregate helper must exist'
        );
        $this->assertStringContainsString(
            'function paintWindowedPanels(agg, d)',
            $this->view,
            'Window-aware panel painter must exist'
        );
        $this->assertStringContainsString(
            'function rebuildPanelsFromZoom(chart)',
            $this->view,
            'Zoom dispatcher must exist'
        );
    }

    public function testInitialRenderRoutesThroughTheWindowAwarePainter(): void
    {
        // renderResults must call paintWindowedPanels with a null window
        // so the initial paint and zoom paint share the same code path.
        $this->assertStringContainsString(
            'paintWindowedPanels(computeWindowAggregates(d, null), d)',
            $this->view
        );
    }

    public function testEveryZoomHookCallsRebuildPanelsFromZoom(): void
    {
        // 3 onZoomComplete callbacks (KB/s, Txn/s, timeline) + 1 Reset Zoom
        // button. None of them may bypass the dispatcher.
        $matches = preg_match_all('/rebuildPanelsFromZoom\s*\(/', $this->view);
        $this->assertGreaterThanOrEqual(
            5, // 3 callbacks + 1 reset + 1 definition site
            $matches,
            'Expected rebuildPanelsFromZoom to be called from every zoom hook + Reset Zoom'
        );

        // The legacy name must not creep back in — that's how the bug got
        // through the first time (treemap-only rebuild on zoom).
        $this->assertStringNotContainsString(
            'rebuildTreemapsFromZoom',
            $this->view,
            'Drop the legacy treemap-only name; use rebuildPanelsFromZoom'
        );
    }

    public function testRiskFactor3IsTaggedAsFullWindow(): void
    {
        // Large-transaction risk factor has no per-time data — paintWindowedPanels
        // must keep showing global numbers and tag the section so users
        // know the rest of the panel is zoom-aware.
        $this->assertMatchesRegularExpression(
            '/3\.\s*Large Transactions.{0,80}\(full window\)/s',
            $this->view,
            'Risk factor 3 must be visibly tagged "(full window)"'
        );
    }
}
