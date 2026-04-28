<?php

declare(strict_types=1);

use App\Library\Kpi\DaemonRunLogger;
use PHPUnit\Framework\TestCase;

final class DaemonRunLoggingTest extends TestCase
{
    public function testBuildCycleRejectsInvalidDaemonOrPid(): void
    {
        $this->assertNull(DaemonRunLogger::buildCycle([
            'id_daemon_main' => 0,
            'pid' => getmypid(),
        ]));

        $this->assertNull(DaemonRunLogger::buildCycle([
            'id_daemon_main' => 7,
            'pid' => 0,
        ]));
    }

    public function testComputeDelayMsReturnsNullForFirstCycle(): void
    {
        $this->assertNull(DaemonRunLogger::computeDelayMs(null, 100.0, 5));
    }

    public function testComputeDelayMsSubtractsRefreshTime(): void
    {
        $this->assertSame(10500, DaemonRunLogger::computeDelayMs(100.0, 115.5, 5));
        $this->assertSame(1, DaemonRunLogger::isOverMaxDelay(10500, 10));
        $this->assertSame(0, DaemonRunLogger::isOverMaxDelay(10000, 10));
    }

    public function testBuildInsertSqlStoresDaemonRunStartPayload(): void
    {
        $cycle = DaemonRunLogger::buildCycle([
            'id_daemon_main' => 7,
            'pid' => 12345,
            'refresh_time' => 5,
            'max_delay' => 5,
            'cycle_started_at' => '2026-04-28 12:00:00.000001',
            'delay_ms' => 6001,
        ]);

        $this->assertIsArray($cycle);
        $sql = DaemonRunLogger::buildInsertSql($this->fakeDb(), $cycle);

        $this->assertStringContainsString('INSERT INTO `daemon_run`', $sql);
        $this->assertStringContainsString('`id_daemon_main`', $sql);
        $this->assertStringContainsString('7, 12345', $sql);
        $this->assertStringContainsString("'2026-04-28 12:00:00.000001'", $sql);
        $this->assertStringContainsString('6001, 0, 1', $sql);
    }

    public function testBuildFinishSqlStoresSkippedCycle(): void
    {
        $sql = DaemonRunLogger::buildFinishSql($this->fakeDb(), [
            'id' => 88,
            'id_daemon_main' => 7,
            'cycle_ended_at' => '2026-04-28 12:00:01.250001',
            'duration_ms' => 1250,
            'status' => 'OK',
            'skipped' => true,
            'over_max_delay' => false,
            'cpu_user_pct' => 12.345,
            'cpu_sys_pct' => null,
            'rss_kb' => 45678,
        ]);

        $this->assertStringContainsString('UPDATE `daemon_run` SET', $sql);
        $this->assertStringContainsString("`cycle_ended_at` = '2026-04-28 12:00:01.250001'", $sql);
        $this->assertStringContainsString('`duration_ms` = 1250', $sql);
        $this->assertStringContainsString('`skipped` = 1', $sql);
        $this->assertStringContainsString('`cpu_user_pct` = 12.35', $sql);
        $this->assertStringContainsString('`cpu_sys_pct` = NULL', $sql);
        $this->assertStringContainsString('`rss_kb` = 45678', $sql);
        $this->assertStringContainsString('WHERE `id` = 88 AND `id_daemon_main` = 7', $sql);
    }

    public function testBuildMarkCrashedSqlTargetsOneDaemonOnly(): void
    {
        $sql = DaemonRunLogger::buildMarkCrashedSql(7);

        $this->assertStringContainsString("`status` = 'CRASHED'", $sql);
        $this->assertStringContainsString('WHERE `id_daemon_main` = 7', $sql);
        $this->assertStringContainsString('AND `cycle_ended_at` IS NULL', $sql);
    }

    public function testAgentLaunchUsesDaemonRunLogger(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Agent.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('DaemonRunLogger::startCycle([', $source);
        $this->assertStringContainsString('System::isRunningPid($lastChildPid)', $source);
        $this->assertStringContainsString('DaemonRunLogger::finishCycle($cycle', $source);
    }

    public function testAgentRestartPathsMarkCrashedRuns(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Agent.php');

        $this->assertIsString($source);
        $this->assertGreaterThanOrEqual(2, substr_count($source, 'DaemonRunLogger::markCrashedRuns'));
        $this->assertStringContainsString('public function check_daemon()', $source);
    }

    private function fakeDb(): object
    {
        return new class {
            public function sql_real_escape_string(string $value): string
            {
                return str_replace("'", "\\'", $value);
            }
        };
    }
}
