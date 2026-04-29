<?php

declare(strict_types=1);

namespace App\Library;

use Glial\Sgbd\Sgbd;

/**
 * Detect orphan /database/refresh dump directories left on disk.
 *
 * Issue #584. When `App\Controller\Database::databaseRefresh()` is killed
 * before line 559 (`shell_exec("rm -rvf $directory")`), the mydumper
 * artefact survives on disk. The destination root is the user-supplied
 * `path` field, which is the JSON `param[3]` of the `addRefresh` job.
 * The actual sub-directory name is a `uniqid()` and is NOT persisted in
 * the `job` table — so it can only be re-discovered by scanning the
 * filesystem.
 *
 * The class is split in two layers:
 *   - `evaluate()`: pure logic, fed with already-collected candidates.
 *     This is what the unit tests exercise.
 *   - `scan()`: thin IO wrapper that collects refresh roots from the
 *     `job` table, lists each root and feeds the result to `evaluate()`.
 *
 * A directory is considered an orphan dump when ALL of the following hold:
 *   1. it sits inside a known refresh root (job.param[3]);
 *   2. it contains a `metadata` file (the mydumper marker);
 *   3. its `mtime` is older than `MIN_AGE_SECONDS` (avoids flagging an
 *      in-flight dump);
 *   4. no currently-running `addRefresh` job points at the same root.
 */
final class OrphanRefreshScanner
{
    public const MIN_AGE_SECONDS = 1800;
    public const MAX_ROOTS       = 5;
    public const MAX_DIRS_PER_ROOT = 20;
    public const MAX_REPORTED    = 10;

    /**
     * Pure evaluation: classify candidate directories as orphan or not.
     *
     * @param array<int, array{
     *     path: string,
     *     root: string,
     *     mtime: int,
     *     size_bytes: int,
     *     has_metadata: bool,
     *     related_job_id: int|null,
     *     related_job_status: string|null,
     * }> $candidates
     * @param array<int, string> $rootsWithRunningJob roots blocked by a live job
     * @return array{
     *     orphans: array<int, array<string, mixed>>,
     *     total_size_bytes: int,
     * }
     */
    public static function evaluate(
        array $candidates,
        array $rootsWithRunningJob,
        int $now,
        int $minAgeSeconds = self::MIN_AGE_SECONDS,
        int $maxReported = self::MAX_REPORTED
    ): array {
        $blockedRoots = array_flip(array_map('strval', $rootsWithRunningJob));
        $orphans = [];
        $totalSize = 0;

        foreach ($candidates as $candidate) {
            if (!($candidate['has_metadata'] ?? false)) {
                continue;
            }
            if (($now - (int) ($candidate['mtime'] ?? 0)) < $minAgeSeconds) {
                continue;
            }
            if (isset($blockedRoots[(string) ($candidate['root'] ?? '')])) {
                continue;
            }

            $orphan = [
                'path'                => (string) $candidate['path'],
                'root'                => (string) $candidate['root'],
                'mtime'               => (int) $candidate['mtime'],
                'age_seconds'         => $now - (int) $candidate['mtime'],
                'size_bytes'          => (int) ($candidate['size_bytes'] ?? 0),
                'related_job_id'      => $candidate['related_job_id'] ?? null,
                'related_job_status'  => $candidate['related_job_status'] ?? null,
            ];

            $orphans[] = $orphan;
            $totalSize += $orphan['size_bytes'];
        }

        usort(
            $orphans,
            static fn (array $a, array $b): int => ($b['size_bytes'] ?? 0) <=> ($a['size_bytes'] ?? 0)
        );

        if (count($orphans) > $maxReported) {
            $orphans = array_slice($orphans, 0, $maxReported);
        }

        return [
            'orphans'          => $orphans,
            'total_size_bytes' => $totalSize,
        ];
    }

    /**
     * Live scan: query the job table, glob the disk, evaluate.
     *
     * Wrapped in a try/catch by the caller (Home::index) so a broken
     * filesystem never breaks the dashboard.
     *
     * @return array{
     *     orphans: array<int, array<string, mixed>>,
     *     total_size_bytes: int,
     * }
     */
    public static function scan(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $roots = self::collectRefreshRoots($db);
        $rootsWithRunningJob = self::collectRootsWithRunningJob($db);
        $latestJobByRoot = self::collectLatestJobByRoot($db);

        $candidates = [];
        foreach ($roots as $root) {
            foreach (self::listDirectories($root) as $dir) {
                $candidates[] = self::probeDirectory($dir, $root, $latestJobByRoot[$root] ?? null);
            }
        }

        return self::evaluate($candidates, $rootsWithRunningJob, time());
    }

    /**
     * @return array<int, string>
     */
    public static function collectRefreshRoots($db, int $maxRoots = self::MAX_ROOTS): array
    {
        $sql = "SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(param, '$[3]')) AS refresh_root
                FROM   job
                WHERE  class = 'App\\\\Controller\\\\Database'
                  AND  method = 'addRefresh'
                  AND  JSON_EXTRACT(param, '$[3]') IS NOT NULL
                ORDER BY id DESC
                LIMIT  ".((int) $maxRoots);

        $res = $db->sql_query($sql);
        $roots = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $root = (string) ($row['refresh_root'] ?? '');
            if ($root === '' || $root === 'null') {
                continue;
            }
            if (!is_dir($root)) {
                continue;
            }
            $roots[$root] = true;
        }

        return array_keys($roots);
    }

    /**
     * Roots that currently have a RUNNING addRefresh job whose pid is alive.
     *
     * @return array<int, string>
     */
    public static function collectRootsWithRunningJob($db): array
    {
        $sql = "SELECT pid, JSON_UNQUOTE(JSON_EXTRACT(param, '$[3]')) AS refresh_root
                FROM   job
                WHERE  class = 'App\\\\Controller\\\\Database'
                  AND  method = 'addRefresh'
                  AND  status = 'RUNNING'";

        $res = $db->sql_query($sql);
        $blocked = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $root = (string) ($row['refresh_root'] ?? '');
            $pid  = (int) ($row['pid'] ?? 0);
            if ($root === '' || $pid <= 0) {
                continue;
            }
            if (!System::isRunningPid($pid)) {
                continue;
            }
            $blocked[$root] = true;
        }

        return array_keys($blocked);
    }

    /**
     * Latest non-running job per root, used to attach a job_id/status to
     * each orphan in the warning banner.
     *
     * @return array<string, array{id: int, status: string}>
     */
    public static function collectLatestJobByRoot($db): array
    {
        $sql = "SELECT id, status, JSON_UNQUOTE(JSON_EXTRACT(param, '$[3]')) AS refresh_root
                FROM   job
                WHERE  class = 'App\\\\Controller\\\\Database'
                  AND  method = 'addRefresh'
                ORDER BY id DESC
                LIMIT  50";

        $res = $db->sql_query($sql);
        $byRoot = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $root = (string) ($row['refresh_root'] ?? '');
            if ($root === '' || isset($byRoot[$root])) {
                continue;
            }
            $byRoot[$root] = [
                'id'     => (int) $row['id'],
                'status' => (string) ($row['status'] ?? ''),
            ];
        }

        return $byRoot;
    }

    /**
     * @return array<int, string>
     */
    private static function listDirectories(string $root): array
    {
        if (!is_dir($root)) {
            return [];
        }
        $entries = @scandir($root);
        if ($entries === false) {
            return [];
        }
        $dirs = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $full = rtrim($root, '/') . '/' . $entry;
            if (!is_dir($full)) {
                continue;
            }
            $dirs[] = $full;
            if (count($dirs) >= self::MAX_DIRS_PER_ROOT) {
                break;
            }
        }

        return $dirs;
    }

    /**
     * @param array{id: int, status: string}|null $latestJob
     * @return array<string, mixed>
     */
    private static function probeDirectory(string $path, string $root, ?array $latestJob): array
    {
        $hasMetadata = is_file($path . '/metadata');
        $mtime = (int) (@filemtime($path) ?: 0);
        $sizeBytes = self::dirSizeBytes($path);

        return [
            'path'              => $path,
            'root'              => $root,
            'mtime'             => $mtime,
            'size_bytes'        => $sizeBytes,
            'has_metadata'      => $hasMetadata,
            'related_job_id'    => $latestJob['id'] ?? null,
            'related_job_status' => $latestJob['status'] ?? null,
        ];
    }

    /**
     * Cheap directory size via `du -sb`. Falls back to 0 on failure.
     */
    private static function dirSizeBytes(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }
        $cmd = 'du -sb ' . escapeshellarg($path) . ' 2>/dev/null';
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
