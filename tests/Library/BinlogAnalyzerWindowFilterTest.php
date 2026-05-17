<?php

declare(strict_types=1);

use App\Library\BinlogAnalyzer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * (#1273 / MDEV-39640) Tests for `BinlogAnalyzer::mysqlbinlogLineMatchesWindow()`,
 * the PHP-side substitute for `mariadb-binlog --stop-datetime` that we
 * had to add to work around the upstream bug where `--stop-datetime`
 * truncates the output to 3 header events on a relay binlog produced
 * with `log_slave_updates=ON`.
 *
 * The helper is intentionally pure: takes a mysqlbinlog header line +
 * `[startTs, endTs]` and returns whether the event should pass. We
 * exercise:
 *   - the YYMMDD prefix expected from MariaDB's mysqlbinlog output
 *   - lines that are not headers (continuation lines, BINLOG payload,
 *     standalone error messages) — they must pass through unchanged
 *   - boundary cases (event exactly at start, exactly at end)
 */
final class BinlogAnalyzerWindowFilterTest extends TestCase
{
    /**
     * @return array<string,array{0:string,1:int,2:int,3:bool}>
     */
    public static function lineProvider(): array
    {
        // 2026-05-13 15:03:09 UTC vicinity — same data the empirical
        // investigation in issue #1273 captured.
        $start = strtotime('2026-05-13 15:00:00');
        $end   = strtotime('2026-05-13 15:10:00');

        return [
            // ── Header lines ────────────────────────────────────────
            'header inside window' => [
                '#260513 15:03:09 server id 999456  end_log_pos 256 CRC32 0x792d0426 	Start: binlog v 4',
                $start, $end, true,
            ],
            'header at exact start (inclusive)' => [
                '#260513 15:00:00 server id 111  end_log_pos 463 	GTID 0-111-553930867 trans',
                $start, $end, true,
            ],
            'header at exact end (inclusive)' => [
                '#260513 15:10:00 server id 111  end_log_pos 463 	Query',
                $start, $end, true,
            ],
            'header before start' => [
                '#260513 14:59:59 server id 111  end_log_pos 256 	GTID',
                $start, $end, false,
            ],
            'header after end' => [
                '#260513 15:10:01 server id 111  end_log_pos 256 	GTID',
                $start, $end, false,
            ],
            // The exact bug reproducer: a file-close Binlog_checkpoint
            // dated days after the data events. Without the workaround,
            // mariadb-binlog notices this and truncates everything;
            // with our PHP-side filter we just drop the offending line.
            'file-close checkpoint (days after data)' => [
                '#260517 19:06:43 server id 999456  end_log_pos 421 CRC32 0x4923852a 	Binlog checkpoint mariadb-bin.011945',
                $start, $end, false,
            ],

            // ── Non-header lines must pass through ───────────────────
            'BINLOG payload line' => [
                "BINLOG 'jXYEag8gQA8A/AAAAAABAAAAAAQAMTEuOC42LU1hcmlhREItMCtkZWIxM3UxIGZyb20gRGViaWFu",
                $start, $end, true,
            ],
            'comment-only line' => [
                '# at 463',
                $start, $end, true,
            ],
            'SQL line (BEGIN/COMMIT/SET)' => [
                'SET timestamp=1778670789/*!*/;',
                $start, $end, true,
            ],
            'empty line' => [
                '',
                $start, $end, true,
            ],
            'random text' => [
                'DELIMITER /*!*/;',
                $start, $end, true,
            ],
        ];
    }

    #[DataProvider('lineProvider')]
    public function testLineMatchesWindow(string $line, int $start, int $end, bool $expected): void
    {
        $this->assertSame(
            $expected,
            BinlogAnalyzer::mysqlbinlogLineMatchesWindow($line, $start, $end),
            "Window [$start..$end] on line: " . substr($line, 0, 80)
        );
    }

    /**
     * Round-trip check: passing PHP_INT_MAX as end means "no upper
     * bound" — every header in the past should pass.
     */
    public function testUnboundedEndPassesAllPastHeaders(): void
    {
        $now = time();
        $this->assertTrue(BinlogAnalyzer::mysqlbinlogLineMatchesWindow(
            '#260513 15:03:09 server id 111  end_log_pos 0 	Write_rows: table id 65860',
            0, PHP_INT_MAX
        ));
        $this->assertTrue(BinlogAnalyzer::mysqlbinlogLineMatchesWindow(
            '#' . date('ymd H:i:s', $now) . ' server id 111  end_log_pos 0 	Write_rows',
            0, PHP_INT_MAX
        ));
    }

    /**
     * Sanity: a malformed header (truncated prefix, unparsable date)
     * should not crash. We let it pass — the conservative choice; the
     * downstream grep pipeline will discard it if it doesn't match the
     * event-type regex anyway.
     */
    public function testMalformedHeaderPasses(): void
    {
        $start = strtotime('2026-05-13 15:00:00');
        $end   = strtotime('2026-05-13 15:10:00');
        $this->assertTrue(BinlogAnalyzer::mysqlbinlogLineMatchesWindow(
            '#9999XX YY:ZZ:WW garbage',
            $start, $end
        ));
        $this->assertTrue(BinlogAnalyzer::mysqlbinlogLineMatchesWindow(
            '#260513 99:99:99 server id 1 end_log_pos 1',
            $start, $end
        ));
    }
}
