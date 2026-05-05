<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReportsControllerTest extends TestCase
{
    public function testReportsControllerViewJsAclAndMenuAreWired(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Reports.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Reports/index.view.php');
        $js = (string)file_get_contents(__DIR__ . '/../../App/Webroot/js/Reports/reports.js');
        $acl = (string)file_get_contents(__DIR__ . '/../../config_sample/acl.config.ini');
        $menu = (string)file_get_contents(__DIR__ . '/../../sql/incremental_v2/20260430_reports_menu.sql');

        $this->assertStringContainsString('class Reports extends Controller', $controller);
        $this->assertStringContainsString('public function index($param = [])', $controller);
        $this->assertStringContainsString('BusinessReportCatalog::build($selectedReport, $_GET)', $controller);
        $this->assertStringContainsString('chart-4.5.1.umd.min.js', $controller);
        $this->assertStringContainsString('Reports/reports.js', $controller);

        $this->assertStringContainsString('window.reportsPayload', $view);
        $this->assertStringContainsString('class="js-reports-chart"', $view);
        $this->assertStringContainsString('name="report"', $view);
        $this->assertStringContainsString('JSON_HEX_TAG', $view);
        $this->assertStringContainsString('JSON_HEX_AMP', $view);
        $this->assertStringContainsString('JSON_HEX_APOS', $view);
        $this->assertStringContainsString('JSON_HEX_QUOT', $view);

        $this->assertStringContainsString('new Chart(ctx', $js);

        $this->assertStringContainsString('ReadOnly[] = "Reports/index"', $acl);
        $this->assertStringContainsString('Reports', $menu);
        $this->assertStringContainsString('{LINK}Reports/index', $menu);
        $this->assertStringContainsString("`title` = 'Dashboard'", $menu);
        $this->assertStringContainsString('@reports_group_id', $menu);
        $this->assertStringContainsString('`group_id` = @reports_group_id', $menu);
        $this->assertStringContainsString('`parent_id`, `group_id`, `icon`', $menu);
    }

    public function testLegacyBiControllerNoLongerExposesSpiderActions(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/BI.php');

        $this->assertStringContainsString('@deprecated Historical Spider federation prototype', $controller);
        $this->assertStringContainsString('class BI extends Controller', $controller);
        $this->assertStringNotContainsString('public function searchField', $controller);
        $this->assertStringNotContainsString('public function createServer', $controller);
        $this->assertStringNotContainsString('public function rapport', $controller);
        $this->assertStringNotContainsString('public function createTableSpider', $controller);
        $this->assertStringNotContainsString('Mysql::getDbLink', $controller);
        $this->assertStringNotContainsString('CREATE OR REPLACE SERVER', $controller);
    }
}
