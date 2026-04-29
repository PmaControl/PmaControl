<?php

declare(strict_types=1);

namespace Tests\Library\Database;

use App\Library\Database\RefreshArtifact;
use PHPUnit\Framework\TestCase;

/**
 * Issue #583 — phase tracking for /database/refresh.
 * The IO-free pieces (`isResumable`, `buildExpiresAt`) are exercised
 * here; the SQL helpers are covered functionally by the controller.
 */
final class RefreshArtifactTest extends TestCase
{
    private const NOW = 1_900_000_000;

    public function testIsResumableAcceptsACanonicalLoadFailureRow(): void
    {
        $row = $this->row([
            'load_status' => 'ERROR',
        ]);
        $this->assertTrue(RefreshArtifact::isResumable($row, self::NOW));
    }

    public function testIsResumableAcceptsAnInterruptedLoad(): void
    {
        $row = $this->row([
            'load_status' => 'INTERRUPTED',
        ]);
        $this->assertTrue(RefreshArtifact::isResumable($row, self::NOW));
    }

    public function testIsResumableRejectsNonRefreshJobs(): void
    {
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'class' => 'App\\Controller\\Backup',
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'method' => 'mydumper',
        ]), self::NOW));
    }

    public function testIsResumableRejectsRowsWhereDumpDidNotSucceed(): void
    {
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'dump_status' => 'RUNNING',
            'load_status' => 'ERROR',
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'dump_status' => 'ERROR',
            'load_status' => 'ERROR',
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'dump_status' => null,
            'load_status' => 'ERROR',
        ]), self::NOW));
    }

    public function testIsResumableRejectsLoadStatusesThatAreNotRetryable(): void
    {
        // SUCCESS — the load is already done.
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'load_status' => 'SUCCESS',
        ]), self::NOW));
        // RUNNING — another worker is currently driving it.
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'load_status' => 'RUNNING',
        ]), self::NOW));
    }

    public function testIsResumableRejectsRowsWithoutAnArtifactPath(): void
    {
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'artifact_path' => '',
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'artifact_path' => null,
        ]), self::NOW));
    }

    public function testIsResumableRejectsExpiredOrInvalidExpiry(): void
    {
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'artifact_expires_at' => date('Y-m-d H:i:s', self::NOW - 60),
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'artifact_expires_at' => '0000-00-00 00:00:00',
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'artifact_expires_at' => '',
        ]), self::NOW));
        $this->assertFalse(RefreshArtifact::isResumable($this->row([
            'artifact_expires_at' => 'not-a-date',
        ]), self::NOW));
    }

    public function testBuildExpiresAtAdds24Hours(): void
    {
        $now = mktime(12, 0, 0, 6, 15, 2026);
        $this->assertNotFalse($now);
        $this->assertSame(
            date('Y-m-d H:i:s', $now + 86_400),
            RefreshArtifact::buildExpiresAt($now)
        );
        $this->assertSame(86_400, RefreshArtifact::TTL_SECONDS);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function row(array $overrides): array
    {
        return $overrides + [
            'class'               => 'App\\Controller\\Database',
            'method'              => 'addRefresh',
            'dump_status'         => 'SUCCESS',
            'load_status'         => 'ERROR',
            'artifact_path'       => '/srv/backup/abc',
            'artifact_expires_at' => date('Y-m-d H:i:s', self::NOW + 3600),
        ];
    }
}
