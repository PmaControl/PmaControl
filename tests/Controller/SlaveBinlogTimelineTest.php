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
        $this->assertStringContainsString('var fStart = parseTimelineTs(fr.start);', $this->view);
        $this->assertStringContainsString('var fEnd = parseTimelineTs(fr.end);', $this->view);
    }
}
