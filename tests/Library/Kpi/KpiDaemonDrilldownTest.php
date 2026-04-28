<?php

declare(strict_types=1);

use App\Library\Kpi\KpiDaemonDrilldown;
use PHPUnit\Framework\TestCase;

final class KpiDaemonDrilldownTest extends TestCase
{
    public function testStatusLabelsReflectEnabledAndPidState(): void
    {
        $this->assertSame('DISABLED', KpiDaemonDrilldown::daemonStatusLabel(['is_enabled' => 0, 'pid' => 0]));
        $this->assertSame('ENABLED', KpiDaemonDrilldown::daemonStatusLabel(['is_enabled' => 1, 'pid' => 0]));
        $this->assertSame('RUNNING', KpiDaemonDrilldown::daemonStatusLabel(['is_enabled' => 1, 'pid' => 1234]));

        $this->assertSame('unknown', KpiDaemonDrilldown::daemonStatusClass(['is_enabled' => 0, 'pid' => 0]));
        $this->assertSame('readonly', KpiDaemonDrilldown::daemonStatusClass(['is_enabled' => 1, 'pid' => 0]));
        $this->assertSame('up', KpiDaemonDrilldown::daemonStatusClass(['is_enabled' => 1, 'pid' => 1234]));
    }

    public function testRunStatusClassMapsCrashToDown(): void
    {
        $this->assertSame('up', KpiDaemonDrilldown::runStatusClass('OK'));
        $this->assertSame('down', KpiDaemonDrilldown::runStatusClass('CRASHED'));
        $this->assertSame('unknown', KpiDaemonDrilldown::runStatusClass(null));
    }

    public function testCountPidRestartsIgnoresEmptyPidsAndConsecutiveDuplicates(): void
    {
        $this->assertSame(0, KpiDaemonDrilldown::countPidRestarts([]));
        $this->assertSame(0, KpiDaemonDrilldown::countPidRestarts([
            ['pid' => 0],
            ['pid' => 100],
            ['pid' => 100],
        ]));
        $this->assertSame(2, KpiDaemonDrilldown::countPidRestarts([
            ['pid' => 100],
            ['pid' => 100],
            ['pid' => 200],
            ['pid' => null],
            ['pid' => 200],
            ['pid' => 300],
        ]));
    }

    public function testSeriesFromRowsFillsMinuteGapsWithNulls(): void
    {
        $series = KpiDaemonDrilldown::seriesFromRows(
            [
                ['bucket_at' => '2026-04-28 10:00:00', 'value' => '10.1234'],
                ['bucket_at' => '2026-04-28 10:02:00', 'value' => '30'],
            ],
            new DateTimeImmutable('2026-04-28 10:00:00'),
            new DateTimeImmutable('2026-04-28 10:02:00')
        );

        $this->assertSame(['10:00', '10:01', '10:02'], $series['labels']);
        $this->assertSame([10.123, null, 30.0], $series['values']);
        $this->assertSame(['x' => '2026-04-28 10:01:00', 'y' => null], $series['points'][1]);
        $this->assertSame(['count' => 2, 'min' => 10.123, 'max' => 30.0, 'avg' => 20.062], $series['summary']);
    }

    public function testBuildSeriesSqlUsesDaemonWindowMetricAndMinuteAggregation(): void
    {
        $sql = KpiDaemonDrilldown::buildSeriesSql(
            $this->fakeDb(),
            7,
            '2026-04-27 12:00:00',
            'delay_ms'
        );

        $this->assertStringContainsString("DATE_FORMAT(`cycle_started_at`, '%Y-%m-%d %H:%i:00') AS `bucket_at`", $sql);
        $this->assertStringContainsString('AVG(`delay_ms`) AS `value`', $sql);
        $this->assertStringContainsString('WHERE `id_daemon_main` = 7', $sql);
        $this->assertStringContainsString("`cycle_started_at` >= '2026-04-27 12:00:00'", $sql);
        $this->assertStringContainsString('`delay_ms` IS NOT NULL', $sql);
        $this->assertStringContainsString('GROUP BY `bucket_at`', $sql);
    }

    public function testBuildRunsSqlLimitsLastHundredDaemonRuns(): void
    {
        $sql = KpiDaemonDrilldown::buildRunsSql(7);

        $this->assertStringContainsString('FROM `daemon_run`', $sql);
        $this->assertStringContainsString('WHERE `id_daemon_main` = 7', $sql);
        $this->assertStringContainsString('ORDER BY `cycle_started_at` DESC LIMIT 100', $sql);
    }

    public function testBuildRestartPidSqlKeepsChronologicalOrder(): void
    {
        $sql = KpiDaemonDrilldown::buildRestartPidSql($this->fakeDb(), 7, '2026-04-27 12:00:00');

        $this->assertStringContainsString('SELECT `pid`, `cycle_started_at`', $sql);
        $this->assertStringContainsString('WHERE `id_daemon_main` = 7', $sql);
        $this->assertStringContainsString('ORDER BY `cycle_started_at` ASC', $sql);
    }

    public function testPayloadBuilderKeepsDaemonRunDegradedModeInSource(): void
    {
        $source = file_get_contents(__DIR__.'/../../../App/Library/Kpi/KpiDaemonDrilldown.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("tableExists(\$db, 'daemon_run')", $source);
        $this->assertStringContainsString('Table daemon_run is missing', $source);
        $this->assertStringContainsString('Daemon run KPI is unavailable', $source);
        $this->assertStringContainsString('worker/list', $source);
    }

    public function testViewAndJavascriptExposeExpectedDaemonCharts(): void
    {
        $view = file_get_contents(__DIR__.'/../../../App/view/Kpi/daemon.view.php');
        $js = file_get_contents(__DIR__.'/../../../App/Webroot/js/Kpi/daemon.js');

        $this->assertIsString($view);
        $this->assertIsString($js);
        $this->assertStringContainsString('kpi-daemon-duration', $view);
        $this->assertStringContainsString('kpi-daemon-delay', $view);
        $this->assertStringContainsString('100 derniers daemon_run', $view);
        $this->assertStringContainsString('Workers generes', $view);
        $this->assertStringContainsString('window.kpiDaemonCharts', $js);
        $this->assertStringContainsString('max_delay', $js);
        $this->assertStringContainsString('renderLine("kpi-daemon-delay"', $js);
        $this->assertStringContainsString('], false);', $js);
        $this->assertStringContainsString('new Chart', $js);
        $this->assertStringContainsString('type: "time"', $js);
        $this->assertStringNotContainsString('parsing: false', $js);
    }

    public function testControllerLoadsChartJsAndKpiDaemonScript(): void
    {
        $source = file_get_contents(__DIR__.'/../../../App/Controller/Kpi.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('KpiDaemonDrilldown::buildPayload', $source);
        $this->assertStringContainsString("!empty(\$this->di['js'])", $source);
        $this->assertStringContainsString('chart-4.5.1.umd.min.js', $source);
        $this->assertStringContainsString('Kpi/daemon.js', $source);
        $this->assertStringContainsString('window.kpiDaemonCharts', $source);
        $this->assertStringContainsString('Daemon KPI unavailable', $source);
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
