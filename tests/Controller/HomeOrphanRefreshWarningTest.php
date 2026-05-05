<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #584 — /home must surface orphan /database/refresh dumps.
 *
 * Source-level guard, same pattern as DatabaseRefreshRedirectTest /
 * DatabaseRefreshLoadFlagTest: verify the wiring at the source level
 * so a refactor cannot silently break the warning banner.
 */
final class HomeOrphanRefreshWarningTest extends TestCase
{
    private string $controller;
    private string $view;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Home.php'
        );
        $this->view = (string) file_get_contents(
            __DIR__ . '/../../App/view/Home/index.view.php'
        );
        $this->assertNotSame('', $this->controller, 'Home.php must be readable');
        $this->assertNotSame('', $this->view, 'Home/index.view.php must be readable');
    }

    public function testHomeControllerImportsOrphanRefreshScanner(): void
    {
        $this->assertStringContainsString(
            'use App\\Library\\OrphanRefreshScanner;',
            $this->controller,
            'Home::index must import OrphanRefreshScanner (#584)'
        );
    }

    public function testHomeControllerCallsScannerAndPopulatesData(): void
    {
        $this->assertStringContainsString(
            'OrphanRefreshScanner::scan()',
            $this->controller,
            'Home::index must call OrphanRefreshScanner::scan() (#584)'
        );
        $this->assertStringContainsString(
            "\$data['orphan_refreshes']",
            $this->controller,
            "Home::index must expose \$data['orphan_refreshes'] for the view"
        );
        $this->assertStringContainsString(
            "\$data['orphan_refreshes_total_size']",
            $this->controller,
            "Home::index must expose \$data['orphan_refreshes_total_size'] for the view"
        );
    }

    public function testHomeControllerWrapsScannerInTryCatch(): void
    {
        // A broken filesystem must NEVER take down /home — the scanner
        // call must sit inside a try/catch that swallows Throwable.
        $start = strpos($this->controller, 'OrphanRefreshScanner::scan()');
        $this->assertNotFalse($start, 'scanner call must exist before this assertion runs');

        $tryStart = strrpos(substr($this->controller, 0, $start), 'try');
        $catchAfter = strpos($this->controller, 'catch (\\Throwable', $start);

        $this->assertNotFalse($tryStart, 'scanner call must be wrapped in try { ... }');
        $this->assertNotFalse($catchAfter, 'scanner call must be followed by catch (\\Throwable ...)');
    }

    public function testHomeViewRendersOrphanRefreshBanner(): void
    {
        $this->assertStringContainsString(
            "if (!empty(\$data['orphan_refreshes']))",
            $this->view,
            'Home view must conditionally render the orphan-refresh banner'
        );
        $this->assertStringContainsString(
            'Orphan refresh dumps',
            $this->view,
            'Home view must use the localized banner title'
        );
        $this->assertStringContainsString(
            'Format::bytes',
            $this->view,
            'Home view must format orphan sizes via App\\Library\\Format::bytes()'
        );
    }
}
