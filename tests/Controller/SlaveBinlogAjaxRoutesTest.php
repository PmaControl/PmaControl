<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SlaveBinlogAjaxRoutesTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
    }

    public function testBinlogAnalysisFetchUrlsIncludeAjaxRouteSegment(): void
    {
        $this->assertStringContainsString(
            "fetch(LINK + 'slave/startBinlogAnalysis/' + serverId + '/ajax:true/'",
            $this->view
        );
        $this->assertStringContainsString(
            "fetch(LINK + 'slave/binlogAnalysisResult/' + id + '/ajax:true/'",
            $this->view
        );
        $this->assertStringContainsString(
            "fetch(LINK + 'slave/binlogAnalysisList/' + serverId + '/ajax:true/'",
            $this->view
        );
    }

    public function testBinlogAnalysisFetchUrlsDoNotUseNonAjaxJsonEndpoints(): void
    {
        $this->assertStringNotContainsString(
            "fetch(LINK + 'slave/startBinlogAnalysis/' + serverId + '/'",
            $this->view
        );
        $this->assertStringNotContainsString(
            "fetch(LINK + 'slave/binlogAnalysisResult/' + id + '/'",
            $this->view
        );
        $this->assertStringNotContainsString(
            "fetch(LINK + 'slave/binlogAnalysisList/' + serverId + '/'",
            $this->view
        );
    }

}
