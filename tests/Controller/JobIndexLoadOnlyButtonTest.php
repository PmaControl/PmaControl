<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #583 — /job/index must offer a `Restart load only` button when
 * the dump succeeded, the load failed/was interrupted, and the
 * artefact is still on disk and not expired.
 */
final class JobIndexLoadOnlyButtonTest extends TestCase
{
    private string $controller;
    private string $view;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Job.php'
        );
        $this->view = (string) file_get_contents(
            __DIR__ . '/../../App/view/Job/index.view.php'
        );
        $this->assertNotSame('', $this->controller, 'Job.php must be readable');
        $this->assertNotSame('', $this->view, 'Job/index.view.php must be readable');
    }

    public function testJobControllerImportsRefreshArtifactAndPopulatesFlag(): void
    {
        $this->assertStringContainsString(
            'use App\\Library\\Database\\RefreshArtifact;',
            $this->controller,
            'Job::index must import RefreshArtifact (#583)'
        );
        $this->assertStringContainsString(
            "\$ob['can_restart_load_only']",
            $this->controller,
            'Job::index must expose can_restart_load_only on each job row'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::isResumable($ob)',
            $this->controller,
            'Job::index must compute can_restart_load_only via RefreshArtifact::isResumable'
        );
        $this->assertStringContainsString(
            "is_dir((string) (\$ob['artifact_path'] ?? ''))",
            $this->controller,
            'Job::index must also verify the artefact still exists on disk'
        );
    }

    public function testViewRendersButtonOnlyWhenFlagSet(): void
    {
        $this->assertStringContainsString(
            "if (!empty(\$job['can_restart_load_only']))",
            $this->view,
            'view must gate the Restart load only button on can_restart_load_only (#583)'
        );
        $this->assertStringContainsString(
            "database/restartLoadOnly/'.(int) \$job['id']",
            $this->view,
            'view must point the button at /database/restartLoadOnly/<id>'
        );
        $this->assertStringContainsString(
            'Restart load only',
            $this->view,
            'view must use the localized button label'
        );
    }
}
