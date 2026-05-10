<?php

declare(strict_types=1);

use App\Library\SlaveDurability;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1185 — pin every value → severity mapping for the durability
 * helper rendered on /slave/show/<id>/<conn>/.
 */
final class SlaveDurabilityTest extends TestCase
{
    private function pluck(array $rows, string $name): ?array
    {
        foreach ($rows as $r) {
            if ($r['name'] === $name) {
                return $r;
            }
        }
        return null;
    }

    public function testSyncBinlogLevels(): void
    {
        $r = $this->pluck(SlaveDurability::rows(['sync_binlog' => '0']), 'sync_binlog');
        $this->assertSame(SlaveDurability::LEVEL_RISK, $r['level']);

        $r = $this->pluck(SlaveDurability::rows(['sync_binlog' => '1']), 'sync_binlog');
        $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);

        $r = $this->pluck(SlaveDurability::rows(['sync_binlog' => '100']), 'sync_binlog');
        $this->assertSame(SlaveDurability::LEVEL_WARN, $r['level']);

        $r = $this->pluck(SlaveDurability::rows([]), 'sync_binlog');
        $this->assertSame(SlaveDurability::LEVEL_UNKNOWN, $r['level']);
        $this->assertStringContainsString('Recommended', $r['tooltip']);
    }

    public function testTrxCommitLevels(): void
    {
        foreach ([
            '0' => SlaveDurability::LEVEL_RISK,
            '1' => SlaveDurability::LEVEL_OK,
            '2' => SlaveDurability::LEVEL_WARN,
        ] as $val => $expected) {
            $r = $this->pluck(
                SlaveDurability::rows(['innodb_flush_log_at_trx_commit' => $val]),
                'innodb_flush_log_at_trx_commit'
            );
            $this->assertSame($expected, $r['level'], "value $val should map to $expected");
        }
    }

    public function testTrxCommitTooltipExplainsAllThreeValues(): void
    {
        $r = $this->pluck(
            SlaveDurability::rows(['innodb_flush_log_at_trx_commit' => '1']),
            'innodb_flush_log_at_trx_commit'
        );
        // The tooltip is the operator's reference card — it must
        // describe every legal value.
        foreach (['0 =', '1 =', '2 ='] as $needle) {
            $this->assertStringContainsString($needle, $r['tooltip']);
        }
        $this->assertStringContainsString('ACID', $r['tooltip']);
    }

    public function testBinlogFormatLevels(): void
    {
        $cases = [
            'ROW'       => SlaveDurability::LEVEL_OK,
            'MIXED'     => SlaveDurability::LEVEL_WARN,
            'STATEMENT' => SlaveDurability::LEVEL_RISK,
        ];
        foreach ($cases as $value => $expected) {
            $r = $this->pluck(
                SlaveDurability::rows(['binlog_format' => $value]),
                'binlog_format'
            );
            $this->assertSame($expected, $r['level'], $value);
        }
    }

    public function testBinlogRowImageLevels(): void
    {
        foreach ([
            'FULL'    => SlaveDurability::LEVEL_OK,
            'MINIMAL' => SlaveDurability::LEVEL_WARN,
            'NOBLOB'  => SlaveDurability::LEVEL_WARN,
        ] as $val => $expected) {
            $r = $this->pluck(
                SlaveDurability::rows(['binlog_row_image' => $val]),
                'binlog_row_image'
            );
            $this->assertSame($expected, $r['level']);
        }
    }

    public function testPreserveCommitOrderRiskWhenParallelGreaterThanOne(): void
    {
        // OFF + parallel > 1 = real risk
        $r = $this->pluck(
            SlaveDurability::rows(
                ['replica_preserve_commit_order' => 'OFF'],
                ['parallel_threads' => 8]
            ),
            'replica_preserve_commit_order'
        );
        $this->assertSame(SlaveDurability::LEVEL_RISK, $r['level']);
        $this->assertStringContainsString('parallel', strtolower($r['label']));

        // OFF + serial applier = warning only (no concrete data-loss)
        $r = $this->pluck(
            SlaveDurability::rows(
                ['replica_preserve_commit_order' => 'OFF'],
                ['parallel_threads' => 0]
            ),
            'replica_preserve_commit_order'
        );
        $this->assertSame(SlaveDurability::LEVEL_WARN, $r['level']);

        // ON = always OK
        $r = $this->pluck(
            SlaveDurability::rows(
                ['replica_preserve_commit_order' => '1'],
                ['parallel_threads' => 4]
            ),
            'replica_preserve_commit_order'
        );
        $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);

        // Legacy alias on MariaDB / MySQL 5.7
        $r = $this->pluck(
            SlaveDurability::rows(
                ['slave_preserve_commit_order' => 'ON'],
                ['parallel_threads' => 4]
            ),
            'replica_preserve_commit_order'
        );
        $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);
    }

    public function testInfoRepositoryFileIsWarning(): void
    {
        foreach (['source_info_repository', 'relay_log_info_repository'] as $var) {
            $r = $this->pluck(SlaveDurability::rows([$var => 'TABLE']), $var);
            $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);

            $r = $this->pluck(SlaveDurability::rows([$var => 'FILE']), $var);
            $this->assertSame(SlaveDurability::LEVEL_WARN, $r['level']);
        }

        // Legacy alias master_info_repository → source_info_repository
        $r = $this->pluck(
            SlaveDurability::rows(['master_info_repository' => 'TABLE']),
            'source_info_repository'
        );
        $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);
    }

    public function testRelayLogRecoveryOffIsRisk(): void
    {
        $r = $this->pluck(SlaveDurability::rows(['relay_log_recovery' => 'ON']),  'relay_log_recovery');
        $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);

        $r = $this->pluck(SlaveDurability::rows(['relay_log_recovery' => 'OFF']), 'relay_log_recovery');
        $this->assertSame(SlaveDurability::LEVEL_RISK, $r['level']);
    }

    public function testSuperReadOnlyOffIsWarning(): void
    {
        $r = $this->pluck(SlaveDurability::rows(['super_read_only' => 'ON']),  'super_read_only');
        $this->assertSame(SlaveDurability::LEVEL_OK, $r['level']);

        $r = $this->pluck(SlaveDurability::rows(['super_read_only' => 'OFF']), 'super_read_only');
        $this->assertSame(SlaveDurability::LEVEL_WARN, $r['level']);
    }

    public function testEveryRowExposesTooltipText(): void
    {
        // Operator reads the badge tooltip — every row must have one.
        $rows = SlaveDurability::rows(['sync_binlog' => '1', 'innodb_flush_log_at_trx_commit' => '1']);
        foreach ($rows as $r) {
            $this->assertNotEmpty($r['tooltip'], $r['name'] . ' must have a tooltip');
            $this->assertContains($r['level'], [
                SlaveDurability::LEVEL_OK,
                SlaveDurability::LEVEL_WARN,
                SlaveDurability::LEVEL_RISK,
                SlaveDurability::LEVEL_UNKNOWN,
            ]);
        }
    }
}
