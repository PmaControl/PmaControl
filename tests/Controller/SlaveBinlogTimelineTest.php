<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SlaveBinlogTimelineTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
    }

    public function testBinlogFileTimelineCanvasAndTooltipAreRendered(): void
    {
        $this->assertStringContainsString('id="sv-ba-file-timeline"', $this->view);
        $this->assertStringContainsString('id="sv-ba-file-tooltip"', $this->view);
    }

    public function testBinlogTimelineUsesCustomChartPluginInsteadOfFloatingBars(): void
    {
        $this->assertStringContainsString("id: 'binlogFileTimeline'", $this->view);
        $this->assertStringContainsString('afterDatasetsDraw: function(chart, args, opts)', $this->view);
        $this->assertStringContainsString('renderBinlogTimeline(d.binlog_file_ranges || [])', $this->view);
        $this->assertStringContainsString('buildTimelineTooltip', $this->view);
    }

    public function testBinlogTimelineZoomIsSynchronizedWithExistingGraphs(): void
    {
        $this->assertStringContainsString('syncZoom(ctx.chart, baTimelineChart)', $this->view);
        $this->assertStringContainsString('syncZoom(ctx.chart, baChart)', $this->view);
        $this->assertStringContainsString('syncZoom(ctx.chart, baParallelChart)', $this->view);
        $this->assertStringContainsString('if (baTimelineChart) { baTimelineChart.resetZoom(); }', $this->view);
    }

    public function testZoomFilteredTreemapsReuseBinlogRangeParser(): void
    {
        // #1225: renderBinlogTimeline normalises each range once via
        // parseTimelineTs and caches the result as startMs/endMs on the
        // range object. Zoom-window aggregation no longer re-parses
        // per-range — it uses a global volume ratio (sizeBytes /
        // total_size_bytes) — so the only call sites left are this
        // normalisation pass.
        $this->assertStringContainsString('var startMs = parseTimelineTs(fr.start);', $this->view);
        $this->assertStringContainsString('var endMs = parseTimelineTs(fr.end);', $this->view);
    }

    public function testTimelineReadsBinlogAnalyzerPayloadFieldNames(): void
    {
        $this->assertStringContainsString('parseInt(fr.size || 0)', $this->view);
        $this->assertStringContainsString("escHtml(fr.name || '')", $this->view);
        $this->assertStringContainsString('shortBinlogName(fr.name)', $this->view);
        $this->assertStringNotContainsString('fr.size_bytes', $this->view);
        $this->assertStringNotContainsString('fr.file', $this->view);
    }
}
