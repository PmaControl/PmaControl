<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #583 — Database::restartLoadOnly + Database::databaseRefreshLoadOnly
 * + Database::databaseRefresh phase tracking.
 *
 * Source-level guard: assert the controller wires phase updates around
 * dump and load, only deletes the artefact tree on a successful load,
 * and exposes a load-only restart action with proper input validation.
 */
final class DatabaseRestartLoadOnlyTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Database.php'
        );
        $this->assertNotSame('', $this->controller, 'Database.php must be readable');
    }

    public function testDatabaseRefreshTracksDumpAndLoadPhases(): void
    {
        $this->assertStringContainsString(
            'RefreshArtifact::markDumpRunning',
            $this->controller,
            'databaseRefresh must persist artifact_path before the dump (#583)'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::markDumpSuccess',
            $this->controller,
            'databaseRefresh must record dump SUCCESS + size + expiry (#583)'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::markDumpError',
            $this->controller,
            'databaseRefresh must record dump ERROR on dump failure (#583)'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::markLoadRunning',
            $this->controller,
            'databaseRefresh must record load RUNNING before databaseLoad (#583)'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::markLoadSuccess',
            $this->controller,
            'databaseRefresh must record load SUCCESS on full success (#583)'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::markLoadError',
            $this->controller,
            'databaseRefresh must record load ERROR on load failure (#583)'
        );
    }

    public function testDatabaseRefreshDeletesArtifactOnlyOnLoadSuccess(): void
    {
        $start = strpos($this->controller, 'public function databaseRefresh($param)');
        $this->assertNotFalse($start, 'databaseRefresh must exist');
        $end = strpos($this->controller, 'public function databaseDump($param)', $start);
        $this->assertNotFalse($end, 'databaseDump must follow databaseRefresh');
        $body = substr($this->controller, $start, $end - $start);

        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$loadOk\s*\)\s*\{[^}]*SafeDirectory::removeTree\(\$directory\)/s',
            $body,
            'databaseRefresh must remove the dump tree only when the load succeeded — otherwise the dump must survive for restartLoadOnly (#583)'
        );
    }

    public function testRestartLoadOnlyValidatesInputBeforeSpawningWorker(): void
    {
        $this->assertStringContainsString(
            'public function restartLoadOnly($param)',
            $this->controller,
            'Database must expose a restartLoadOnly($id) action (#583)'
        );

        $start = strpos($this->controller, 'public function restartLoadOnly($param)');
        $end   = strpos($this->controller, 'public function databaseRefreshLoadOnly($param)', $start);
        $this->assertNotFalse($end, 'databaseRefreshLoadOnly worker must follow restartLoadOnly');
        $body = substr($this->controller, $start, $end - $start);

        $this->assertStringContainsString(
            'PositiveIntegerSelection::normalizeSingle',
            $body,
            'restartLoadOnly must validate the job id'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::isResumable',
            $body,
            'restartLoadOnly must reject jobs that are not resumable (#583)'
        );
        $this->assertStringContainsString(
            'is_dir($artifactPath)',
            $body,
            'restartLoadOnly must verify the artefact still exists on disk before relaunching myloader'
        );
        $this->assertStringContainsString(
            'http_response_code(409)',
            $body,
            'restartLoadOnly must return 409 when the job is not eligible'
        );
    }

    public function testDatabaseRefreshLoadOnlyValidatesItsCliPayload(): void
    {
        $this->assertStringContainsString(
            'public function databaseRefreshLoadOnly($param)',
            $this->controller,
            'Database must expose a databaseRefreshLoadOnly worker (#583)'
        );

        $start = strpos($this->controller, 'public function databaseRefreshLoadOnly($param)');
        $end   = strpos($this->controller, 'Handle database state through `rename`', $start);
        $this->assertNotFalse($end, 'rename docblock must follow databaseRefreshLoadOnly');
        $body = substr($this->controller, $start, $end - $start);

        $this->assertStringContainsString(
            'Identifier::isSafeAbsolutePath($artifactPath)',
            $body,
            'databaseRefreshLoadOnly must validate the artefact path'
        );
        $this->assertStringContainsString(
            'Identifier::normalizeDatabaseNameList',
            $body,
            'databaseRefreshLoadOnly must validate the database list'
        );
        $this->assertStringContainsString(
            'Uuid::isValid($uuid)',
            $body,
            'databaseRefreshLoadOnly must validate the parent job uuid'
        );
        $this->assertStringContainsString(
            'RefreshArtifact::markLoadRunning',
            $body,
            'databaseRefreshLoadOnly must record load RUNNING before relaunching the load'
        );
        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$loadOk\s*\)\s*\{[^}]*SafeDirectory::removeTree\(\$artifactPath\)/s',
            $body,
            'databaseRefreshLoadOnly must remove the artefact only on a successful load'
        );
    }
}
