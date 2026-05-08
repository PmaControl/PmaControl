<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Pin the wiring for the ProxySQL audit tab (#897 / EPIC #895).
 *
 * Grep-based contract checks — full controller boot needs Glial DB.
 * The shape we lock in:
 *   - controller has a public `audit` action;
 *   - the menu builder registers an "audit" entry next to "log";
 *   - the view exists and uses the framework helpers.
 */
final class ProxySQLAuditRouteTest extends TestCase
{
    public function testControllerExposesAuditAction(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');
        $this->assertNotFalse($controller);
        $this->assertMatchesRegularExpression(
            '/public function audit\\s*\\(\\s*\\$param\\s*\\)/',
            $controller,
            'ProxySQL::audit($param) must exist'
        );
        $this->assertStringContainsString('Runner::runAll', $controller, 'audit() must invoke the audit runner');
        $this->assertStringContainsString('Sgbd::sql(DB_DEFAULT)', $controller, 'audit() must pass pmacontrol DB through context');
    }

    public function testMenuRegistersAuditNextToLogs(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');
        $this->assertNotFalse($controller);

        // Audit menu entry must exist and point at /ProxySQL/audit/<id>.
        $this->assertStringContainsString("\$data['menu']['audit']['title']", $controller);
        $this->assertStringContainsString("LINK.'ProxySQL/audit/'", $controller);
    }

    public function testAuditViewRendersSeverityBadgesAndJsonExport(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/audit.view.php');
        $this->assertNotFalse($view);

        // Severity classes for the three vocabulary entries.
        $this->assertStringContainsString('severity-critical', $view);
        $this->assertStringContainsString('severity-warning',  $view);
        $this->assertStringContainsString('severity-info',     $view);

        // Search box + export button the operator uses.
        $this->assertStringContainsString('id="sv-audit-search"', $view);
        $this->assertStringContainsString('id="sv-audit-export"', $view);
        $this->assertStringContainsString('Copy as JSON', $view);

        // Empty state when there are no findings.
        $this->assertStringContainsString('No audit findings', $view);

        // Surfaces connection-level errors as a banner rather than 500.
        $this->assertStringContainsString('audit_error', $view);
    }

    public function testAuditViewSurfacesTicketReferenceWhenPresent(): void
    {
        // Findings that carry a `ticket` field (e.g. "#148") must render
        // it inline so the operator can jump back to the historical incident.
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/audit.view.php');
        $this->assertStringContainsString("\$f['ticket']", $view);
    }

    public function testControllerGroupsFindingsByCategoryWithSeverityCounts(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');
        $this->assertStringContainsString("'by_category'", $controller);
        $this->assertStringContainsString("'severity_counts'", $controller);
    }
}
