<?php

declare(strict_types=1);

namespace App\Library\Database;

/**
 * Issue #583 — phase tracking for /database/refresh jobs.
 *
 * The `job` table now carries 5 extra columns so the dump and load
 * phases can be tracked independently and the dump artefact reused on
 * a load-only restart:
 *
 *   - dump_status        : RUNNING / SUCCESS / ERROR
 *   - load_status        : RUNNING / SUCCESS / ERROR
 *   - artifact_path      : absolute path of the mydumper output dir
 *   - artifact_size_kb   : `du -sk` snapshot
 *   - artifact_expires_at: conservative TTL for orphan cleanup
 *
 * This class wraps the row updates and the resume eligibility logic.
 * The IO-free pieces (`isResumable`, `buildExpiresAt`) are tested
 * directly; the SQL helpers are exercised functionally.
 */
final class RefreshArtifact
{
    /** Default TTL of a dump artefact: 24 hours. */
    public const TTL_SECONDS = 86_400;

    /** Statuses that make a job a candidate for `Restart load only`. */
    public const RESUMABLE_LOAD_STATUSES = ['ERROR', 'INTERRUPTED'];

    /**
     * Decide whether `Restart load only` should be offered for this job row.
     *
     * Pure logic, IO-free. The caller is responsible for is_dir() on the
     * returned path; this only validates the contract carried by the row.
     *
     * @param array<string, mixed> $jobRow associative row from the `job` table
     * @param int|null $now unix timestamp (for tests). Defaults to time().
     */
    public static function isResumable(array $jobRow, ?int $now = null): bool
    {
        $now = $now ?? time();

        if (($jobRow['class'] ?? '') !== 'App\\Controller\\Database') {
            return false;
        }
        if (($jobRow['method'] ?? '') !== 'addRefresh') {
            return false;
        }
        if (($jobRow['dump_status'] ?? '') !== 'SUCCESS') {
            return false;
        }
        if (!in_array((string) ($jobRow['load_status'] ?? ''), self::RESUMABLE_LOAD_STATUSES, true)) {
            return false;
        }
        $path = (string) ($jobRow['artifact_path'] ?? '');
        if ($path === '') {
            return false;
        }
        $expiresAt = (string) ($jobRow['artifact_expires_at'] ?? '');
        if ($expiresAt === '' || $expiresAt === '0000-00-00 00:00:00') {
            return false;
        }
        $expiresTs = strtotime($expiresAt);
        if ($expiresTs === false || $expiresTs < $now) {
            return false;
        }

        return true;
    }

    /**
     * Build the `artifact_expires_at` SQL datetime (UTC server time).
     */
    public static function buildExpiresAt(?int $now = null): string
    {
        $now = $now ?? time();
        return date('Y-m-d H:i:s', $now + self::TTL_SECONDS);
    }

    public static function markDumpRunning($db, string $uuid, string $artifactPath): void
    {
        $sql = "UPDATE job SET dump_status='RUNNING', artifact_path='".$db->sql_real_escape_string($artifactPath)."'"
             . " WHERE uuid='".$db->sql_real_escape_string($uuid)."'";
        $db->sql_query($sql);
    }

    public static function markDumpSuccess($db, string $uuid, int $sizeKb): void
    {
        $expiresAt = self::buildExpiresAt();
        $sql = "UPDATE job SET dump_status='SUCCESS',"
             . " artifact_size_kb=".(int) $sizeKb.","
             . " artifact_expires_at='".$db->sql_real_escape_string($expiresAt)."'"
             . " WHERE uuid='".$db->sql_real_escape_string($uuid)."'";
        $db->sql_query($sql);
    }

    public static function markDumpError($db, string $uuid): void
    {
        $sql = "UPDATE job SET dump_status='ERROR' WHERE uuid='".$db->sql_real_escape_string($uuid)."'";
        $db->sql_query($sql);
    }

    public static function markLoadRunning($db, string $uuid): void
    {
        $sql = "UPDATE job SET load_status='RUNNING' WHERE uuid='".$db->sql_real_escape_string($uuid)."'";
        $db->sql_query($sql);
    }

    public static function markLoadSuccess($db, string $uuid): void
    {
        $sql = "UPDATE job SET load_status='SUCCESS', artifact_path=NULL, artifact_expires_at=NULL"
             . " WHERE uuid='".$db->sql_real_escape_string($uuid)."'";
        $db->sql_query($sql);
    }

    public static function markLoadError($db, string $uuid): void
    {
        $sql = "UPDATE job SET load_status='ERROR' WHERE uuid='".$db->sql_real_escape_string($uuid)."'";
        $db->sql_query($sql);
    }

    /**
     * Estimate the size of a dump directory in KiB. Uses `du -sk` and falls
     * back to 0 on any error (we never want a sizing failure to break the
     * refresh worker).
     */
    public static function measureSizeKb(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }
        $cmd = 'du -sk ' . escapeshellarg($path) . ' 2>/dev/null';
        $out = @shell_exec($cmd);
        if (!is_string($out)) {
            return 0;
        }
        $parts = preg_split('/\s+/', trim($out));
        if (!is_array($parts) || !isset($parts[0])) {
            return 0;
        }

        return (int) $parts[0];
    }
}
