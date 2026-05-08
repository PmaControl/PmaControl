<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * #929 — view-level pinning for the home-page ProxySQL audit card.
 *
 * The view file is rendered without the framework loaded, so we
 * stub `LINK`, `__()`, and `htmlspecialchars` and just isolate the
 * audit-card block. The goal is to pin the deep-link URL shape and
 * the severity-class hooks so future refactors don't break the
 * card without warning.
 */
final class HomeProxysqlAuditCardViewTest extends TestCase
{
    private function render(array $audit): string
    {
        if (!defined('LINK')) {
            define('LINK', '/');
        }
        if (!function_exists('__')) {
            // Use eval so the redefine guard is local to this test file.
            eval('function __($s) { return $s; }');
        }
        $data = ['proxysql_audit' => $audit, 'stuck_analyses' => []];
        ob_start();
        // Extract just the audit-card block from the real view.
        $viewPath = __DIR__ . '/../../App/view/Home/index.view.php';
        $contents = file_get_contents($viewPath);
        $start = strpos($contents, '// #929 — ProxySQL audit summary card');
        $end   = strpos($contents, "<?php if (!empty(\$data['stuck_analyses']))", $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $snippet = "<?php\n" . substr($contents, $start, $end - $start);
        eval('?>' . $snippet);
        return (string) ob_get_clean();
    }

    public function testRendersDeepLinkToWorstServerAudit(): void
    {
        $html = $this->render([
            'available'     => true,
            'total_servers' => 2,
            'critical'      => 4,
            'warning'       => 1,
            'info'          => 0,
            'worst_id'      => 17,
            'worst_name'    => 'px-prod-1',
        ]);
        $this->assertStringContainsString('/ProxySQL/audit/17/', $html);
        $this->assertStringContainsString('px-prod-1', $html);
    }

    public function testCriticalSeverityBadgeAndAccent(): void
    {
        $html = $this->render([
            'available'     => true,
            'total_servers' => 1,
            'critical'      => 3,
            'warning'       => 0,
            'info'          => 0,
            'worst_id'      => 1,
            'worst_name'    => 'px',
        ]);
        $this->assertStringContainsString('data-severity="critical"', $html);
        $this->assertStringContainsString('hm-pa-sev-critical', $html);
        $this->assertStringContainsString('#dc2626', $html); // red accent
    }

    public function testWarningSeverityWhenNoCritical(): void
    {
        $html = $this->render([
            'available'     => true,
            'total_servers' => 1,
            'critical'      => 0,
            'warning'       => 2,
            'info'          => 5,
            'worst_id'      => 1,
            'worst_name'    => 'px',
        ]);
        $this->assertStringContainsString('data-severity="warning"', $html);
        $this->assertStringContainsString('#f59e0b', $html); // orange accent
    }

    public function testCleanSeverityWhenNoCriticalNoWarning(): void
    {
        $html = $this->render([
            'available'     => true,
            'total_servers' => 1,
            'critical'      => 0,
            'warning'       => 0,
            'info'          => 4,
            'worst_id'      => 1,
            'worst_name'    => 'px',
        ]);
        $this->assertStringContainsString('data-severity="clean"', $html);
        $this->assertStringContainsString('#16a34a', $html); // green accent
    }

    public function testRendersNothingWhenSnapshotUnavailable(): void
    {
        $html = $this->render([
            'available'     => false,
            'total_servers' => 0,
            'critical'      => 0,
            'warning'       => 0,
            'info'          => 0,
            'worst_id'      => null,
            'worst_name'    => null,
        ]);
        $this->assertStringNotContainsString('hm-proxysql-audit', $html);
    }

    public function testRendersNothingWhenZeroServers(): void
    {
        $html = $this->render([
            'available'     => true,
            'total_servers' => 0,
            'critical'      => 0,
            'warning'       => 0,
            'info'          => 0,
            'worst_id'      => null,
            'worst_name'    => null,
        ]);
        $this->assertStringNotContainsString('hm-proxysql-audit', $html);
    }
}
