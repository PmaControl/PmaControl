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

    public function testParseTimelineTsPadsSingleDigitClockFields(): void
    {
        // 1-digit hour tokens like "2026-05-08 1:27:19" come straight from
        // the analyzer; the previous `replace(' ', 'T')` produced
        // "2026-05-08T1:27:19" which `new Date(...)` rejects as NaN, so
        // every overlapping file silently dropped out of the zoom-windowed
        // walk — treemap froze, panels zeroed (#826 follow-up).
        // Pin the padding regex shape so the fix can't regress.
        $this->assertStringContainsString(
            '(\d{1,2}):(\d{1,2}):(\d{1,2})',
            $this->view,
            'parseTimelineTs must accept 1-or-2 digit clock fields and pad them before Date parsing'
        );
        $this->assertStringContainsString(
            'function pad(n) { return n.length === 1 ? \'0\' + n : n; }',
            $this->view,
            'Single-digit pad helper must exist inside parseTimelineTs'
        );
    }

    public function testEmptyZoomWindowShowsExplicitTreemapPlaceholder(): void
    {
        // When a zoom window has no per-file table data, the treemaps must
        // be torn down and replaced with a placeholder — the prior
        // early-return left the previous render visible, which the user
        // read as "the treemap doesn't recalculate".
        $this->assertStringContainsString(
            'sv-ba-treemap-empty',
            $this->view,
            'Empty-window placeholder element must be inserted'
        );
        $this->assertStringContainsString(
            'No table breakdown for this window',
            $this->view,
            'Placeholder must carry the operator-facing message'
        );
        $this->assertStringContainsString(
            'baTreemapDb.destroy()',
            $this->view,
            'Empty-window path must destroy the previous treemap, not leave it'
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
