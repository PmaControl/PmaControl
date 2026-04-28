<?php

declare(strict_types=1);

use App\Controller\Kpi;
use PHPUnit\Framework\TestCase;

final class KpiIndexTest extends TestCase
{
    public function testDashboardJsonAllowsGetAndReturnsValidJson(): void
    {
        $outcome = Kpi::evaluateDashboardJsonRequest(
            'realtime',
            [],
            ['REQUEST_METHOD' => 'GET'],
            static fn (): array => ['generated_at' => '2026-04-28 12:00:00', 'latest' => null]
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('application/json; charset=UTF-8', $outcome['headers']['Content-Type']);
        $decoded = json_decode($outcome['body'], true);
        $this->assertIsArray($decoded);
        $this->assertSame('2026-04-28 12:00:00', $decoded['generated_at']);
    }

    public function testDashboardJsonAllowsHeadWithoutBody(): void
    {
        $outcome = Kpi::evaluateDashboardJsonRequest('series', [], ['REQUEST_METHOD' => 'HEAD']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame('application/json; charset=UTF-8', $outcome['headers']['Content-Type']);
    }

    public function testDashboardJsonRejectsPost(): void
    {
        $outcome = Kpi::evaluateDashboardJsonRequest('realtime', [], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
        $decoded = json_decode($outcome['body'], true);
        $this->assertSame(405, $decoded['code']);
    }

    public function testControllerViewJsAndAclExposeAdminDashboard(): void
    {
        $controller = (string)file_get_contents(__DIR__.'/../../App/Controller/Kpi.php');
        $view = (string)file_get_contents(__DIR__.'/../../App/view/Kpi/index.view.php');
        $js = (string)file_get_contents(__DIR__.'/../../App/Webroot/js/Kpi/index.js');
        $acl = (string)file_get_contents(__DIR__.'/../../config_sample/acl.config.ini');

        $this->assertStringContainsString('public function index($param)', $controller);
        $this->assertStringContainsString('public function realtime($param)', $controller);
        $this->assertStringContainsString('public function series($param)', $controller);
        $this->assertStringContainsString('KpiDashboard::buildInitialPayload($_GET)', $controller);
        $this->assertStringContainsString('window.kpiDashboard', $controller);
        $this->assertStringContainsString('Kpi/realtime/ajax:true', $controller);
        $this->assertStringContainsString('Kpi/series/ajax:true', $controller);

        $this->assertStringContainsString('foreach (($series[\'charts\'] ?? []) as $chart)', $view);
        $this->assertStringContainsString('$chart[\'canvas_id\']', $view);
        $this->assertStringContainsString('data-kpi-field="latest.aspirateur_attempts_total"', $view);
        $this->assertStringContainsString('data-kpi-events', $view);
        $this->assertStringContainsString('window.setInterval(refreshRealtime', $js);
        $this->assertStringContainsString('fetch(realtimeUrl', $js);

        $this->assertStringContainsString('Administrator[] = "Kpi/index"', $acl);
        $this->assertStringContainsString('Administrator[] = "Kpi/realtime"', $acl);
        $this->assertStringContainsString('Administrator[] = "Kpi/series"', $acl);
        $this->assertStringNotContainsString('ReadOnly[] = "Kpi/index"', $acl);
        $this->assertStringNotContainsString('ReadOnly[] = "Kpi/realtime"', $acl);
        $this->assertStringNotContainsString('ReadOnly[] = "Kpi/series"', $acl);
    }
}
