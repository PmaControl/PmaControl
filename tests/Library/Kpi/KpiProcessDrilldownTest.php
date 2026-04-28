<?php

declare(strict_types=1);

use App\Library\Kpi\KpiProcessDrilldown;
use PHPUnit\Framework\TestCase;

final class KpiProcessDrilldownTest extends TestCase
{
    public function testPidValidationRejectsUnsafeValues(): void
    {
        $this->assertFalse(KpiProcessDrilldown::validPid(0));
        $this->assertFalse(KpiProcessDrilldown::validPid(-1));
        $this->assertFalse(KpiProcessDrilldown::validPid(KpiProcessDrilldown::MAX_PID + 1));
        $this->assertTrue(KpiProcessDrilldown::validPid(1234));
    }

    public function testParseStatusFileExtractsProcStatusValues(): void
    {
        $status = KpiProcessDrilldown::parseStatusFile("Name:\tphp\nState:\tS (sleeping)\nPPid:\t42\nVmRSS:\t12345 kB\nThreads:\t3\n");

        $this->assertSame('php', $status['Name']);
        $this->assertSame('S (sleeping)', $status['State']);
        $this->assertSame('42', $status['PPid']);
        $this->assertSame('12345 kB', $status['VmRSS']);
        $this->assertSame('3', $status['Threads']);
    }

    public function testParseStatFileExtractsCpuAndStartTicks(): void
    {
        $stat = KpiProcessDrilldown::parseStatFile(
            '1234 (php worker) S 42 1 1 0 -1 4194560 10 0 0 0 200 50 0 0 20 0 1 0 123456 0 0',
            100
        );

        $this->assertSame('php worker', $stat['comm']);
        $this->assertSame('S', $stat['state']);
        $this->assertSame(42, $stat['ppid']);
        $this->assertSame(2.5, $stat['cpu_seconds']);
        $this->assertSame(123456, $stat['start_time_ticks']);
    }

    public function testCommandLineSanitizationMasksSecrets(): void
    {
        $cmdline = KpiProcessDrilldown::sanitizeCommandLine("php script.php --password=secret -pabc token=clear");

        $this->assertStringContainsString('--password=***', $cmdline);
        $this->assertStringContainsString('-p***', $cmdline);
        $this->assertStringContainsString('token=***', $cmdline);
        $this->assertStringNotContainsString('secret', $cmdline);
    }

    public function testReadStartTimeTicksUsesProcStatIdentity(): void
    {
        $base = sys_get_temp_dir().'/pmacontrol-proc-'.bin2hex(random_bytes(4));
        $pid = 1234;
        mkdir($base.'/'.$pid, 0777, true);
        file_put_contents($base.'/'.$pid.'/stat', '1234 (php worker) S 42 1 1 0 -1 4194560 10 0 0 0 200 50 0 0 20 0 1 0 123456 0 0');

        try {
            $this->assertSame(123456, KpiProcessDrilldown::readStartTimeTicks($pid, $base));
        } finally {
            @unlink($base.'/'.$pid.'/stat');
            @rmdir($base.'/'.$pid);
            @rmdir($base);
        }
    }

    public function testComputeUptimeSecondsUsesStartTicksAndSystemUptime(): void
    {
        $this->assertSame(40.0, KpiProcessDrilldown::computeUptimeSeconds(6000, 100.0, 100));
        $this->assertSame(0.0, KpiProcessDrilldown::computeUptimeSeconds(12000, 100.0, 100));
        $this->assertNull(KpiProcessDrilldown::computeUptimeSeconds(null, 100.0, 100));
        $this->assertNull(KpiProcessDrilldown::computeUptimeSeconds(6000, null, 100));
    }

    public function testLookupSqlTargetsWorkerDaemonAndJobPid(): void
    {
        $this->assertStringContainsString('FROM `worker_run` wr', KpiProcessDrilldown::buildWorkerLookupSql(1234));
        $this->assertStringContainsString('INNER JOIN `worker_queue` wq', KpiProcessDrilldown::buildWorkerLookupSql(1234));
        $this->assertStringContainsString('WHERE wr.pid = 1234', KpiProcessDrilldown::buildWorkerLookupSql(1234));
        $this->assertStringContainsString('FROM `daemon_main` WHERE `pid` = 1234', KpiProcessDrilldown::buildDaemonLookupSql(1234));
        $this->assertStringContainsString('FROM `job` WHERE `pid` = 1234', KpiProcessDrilldown::buildJobLookupSql(1234));
    }

    public function testWorkerExecutionsSqlLimitsByWorkerRun(): void
    {
        $sql = KpiProcessDrilldown::buildWorkerExecutionsSql(77);

        $this->assertStringContainsString('FROM `worker_execution`', $sql);
        $this->assertStringContainsString('WHERE `id_worker_run` = 77', $sql);
        $this->assertStringContainsString('ORDER BY `date_started` DESC LIMIT 100', $sql);
    }

    public function testControllerViewAndWorkerExposeProcessKillSafety(): void
    {
        $controller = file_get_contents(__DIR__.'/../../../App/Controller/Kpi.php');
        $view = file_get_contents(__DIR__.'/../../../App/view/Kpi/process.view.php');
        $worker = file_get_contents(__DIR__.'/../../../App/Controller/Worker.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($worker);
        $this->assertStringContainsString("KPI_PROCESS_KILL_CSRF_SCOPE = 'kpi.process.kill'", $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::KPI_PROCESS_KILL_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('KpiProcessDrilldown::buildPayload', $controller);
        $this->assertStringContainsString('http_response_code(404)', $controller);
        $this->assertStringContainsString('Mark safe kill', $view);
        $this->assertStringContainsString('Uptime', $view);
        $this->assertStringContainsString('name="start_time_ticks"', $view);
        $this->assertStringContainsString('killByPid', $worker);
        $this->assertStringContainsString('readStartTimeTicks', $worker);
        $this->assertStringContainsString('posix_kill', $worker);
    }
}
