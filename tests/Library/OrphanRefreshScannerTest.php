<?php

declare(strict_types=1);

namespace Tests\Library;

use App\Library\OrphanRefreshScanner;
use PHPUnit\Framework\TestCase;

/**
 * Issue #584 — orphan refresh dump warning on /home.
 *
 * The scanner is split in two: `evaluate()` is pure (no IO) and is
 * what we test here, with hand-crafted candidates. The IO layer
 * (`scan()`, `collectRefreshRoots`, …) is exercised functionally by
 * /home itself when there is real data on disk.
 */
final class OrphanRefreshScannerTest extends TestCase
{
    private const NOW = 1_800_000_000;

    public function testEvaluateFlagsDirectoryWithMetadataAndOldMtime(): void
    {
        $candidate = $this->candidate([
            'path'         => '/srv/backup/abc',
            'root'         => '/srv/backup',
            'mtime'        => self::NOW - 7200,
            'size_bytes'   => 5_120_000_000,
            'has_metadata' => true,
            'related_job_id'     => 3,
            'related_job_status' => 'INTERRUPTED',
        ]);

        $report = OrphanRefreshScanner::evaluate([$candidate], [], self::NOW);

        $this->assertCount(1, $report['orphans']);
        $this->assertSame('/srv/backup/abc', $report['orphans'][0]['path']);
        $this->assertSame(7200, $report['orphans'][0]['age_seconds']);
        $this->assertSame(5_120_000_000, $report['total_size_bytes']);
        $this->assertSame(3, $report['orphans'][0]['related_job_id']);
        $this->assertSame('INTERRUPTED', $report['orphans'][0]['related_job_status']);
    }

    public function testEvaluateIgnoresDirectoryWithoutMydumperMetadata(): void
    {
        $candidate = $this->candidate([
            'path'         => '/srv/backup/random',
            'has_metadata' => false,
            'mtime'        => self::NOW - 86_400,
        ]);

        $report = OrphanRefreshScanner::evaluate([$candidate], [], self::NOW);

        $this->assertSame([], $report['orphans']);
        $this->assertSame(0, $report['total_size_bytes']);
    }

    public function testEvaluateIgnoresFreshlyWrittenDirectory(): void
    {
        $candidate = $this->candidate([
            'path'         => '/srv/backup/inflight',
            'has_metadata' => true,
            'mtime'        => self::NOW - 60, // just 1 min old
        ]);

        $report = OrphanRefreshScanner::evaluate([$candidate], [], self::NOW);

        $this->assertSame([], $report['orphans'], 'an in-flight dump must not be flagged as orphan');
    }

    public function testEvaluateIgnoresRootBlockedByActiveRunningJob(): void
    {
        $candidate = $this->candidate([
            'path'         => '/srv/backup/abc',
            'root'         => '/srv/backup',
            'has_metadata' => true,
            'mtime'        => self::NOW - 7200,
        ]);

        $report = OrphanRefreshScanner::evaluate(
            [$candidate],
            ['/srv/backup'],
            self::NOW
        );

        $this->assertSame([], $report['orphans'], 'root blocked by a running addRefresh must be skipped');
    }

    public function testEvaluateRespectsCustomMinAgeSeconds(): void
    {
        $candidate = $this->candidate([
            'path'         => '/srv/backup/abc',
            'has_metadata' => true,
            'mtime'        => self::NOW - 600, // 10 min
            'size_bytes'   => 1234,
        ]);

        $strict = OrphanRefreshScanner::evaluate([$candidate], [], self::NOW, 1800);
        $relaxed = OrphanRefreshScanner::evaluate([$candidate], [], self::NOW, 300);

        $this->assertSame([], $strict['orphans']);
        $this->assertCount(1, $relaxed['orphans']);
    }

    public function testEvaluateSortsBySizeDescAndCapsResults(): void
    {
        $candidates = [
            $this->candidate(['path' => '/srv/backup/small',   'has_metadata' => true, 'mtime' => self::NOW - 3600, 'size_bytes' => 100]),
            $this->candidate(['path' => '/srv/backup/biggest', 'has_metadata' => true, 'mtime' => self::NOW - 3600, 'size_bytes' => 9_000]),
            $this->candidate(['path' => '/srv/backup/medium',  'has_metadata' => true, 'mtime' => self::NOW - 3600, 'size_bytes' => 1_000]),
        ];

        $capped = OrphanRefreshScanner::evaluate($candidates, [], self::NOW, OrphanRefreshScanner::MIN_AGE_SECONDS, 2);

        $this->assertCount(2, $capped['orphans']);
        $this->assertSame('/srv/backup/biggest', $capped['orphans'][0]['path']);
        $this->assertSame('/srv/backup/medium',  $capped['orphans'][1]['path']);
        $this->assertSame(10_100, $capped['total_size_bytes'], 'total includes ALL orphans, not just the ones reported');
    }

    public function testEvaluateAcceptsMissingOptionalFields(): void
    {
        $candidate = [
            'path'         => '/srv/backup/abc',
            'root'         => '/srv/backup',
            'mtime'        => self::NOW - 7200,
            'size_bytes'   => 0,
            'has_metadata' => true,
            // related_job_id / related_job_status absent on purpose
        ];

        $report = OrphanRefreshScanner::evaluate([$candidate], [], self::NOW);

        $this->assertCount(1, $report['orphans']);
        $this->assertNull($report['orphans'][0]['related_job_id']);
        $this->assertNull($report['orphans'][0]['related_job_status']);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function candidate(array $overrides): array
    {
        return $overrides + [
            'path'               => '/srv/backup/default',
            'root'               => '/srv/backup',
            'mtime'              => self::NOW - 7200,
            'size_bytes'         => 0,
            'has_metadata'       => true,
            'related_job_id'     => null,
            'related_job_status' => null,
        ];
    }
}
