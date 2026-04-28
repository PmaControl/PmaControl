<?php

declare(strict_types=1);

use App\Library\Kpi\KpiServerDrilldown;
use PHPUnit\Framework\TestCase;

final class KpiServerDrilldownTest extends TestCase
{
    public function testStatusLabelsMatchAvailabilityValues(): void
    {
        $this->assertSame('UP', KpiServerDrilldown::statusLabel(1));
        $this->assertSame('READ ONLY', KpiServerDrilldown::statusLabel(2));
        $this->assertSame('DOWN', KpiServerDrilldown::statusLabel(0));
        $this->assertSame('UNKNOWN', KpiServerDrilldown::statusLabel(null));
    }

    public function testTimelineAggregatesRowsByMinuteUsingServerStatePriority(): void
    {
        $timeline = KpiServerDrilldown::timelineFromRows(
            [
                ['date' => '2026-04-28 10:00:01', 'value' => '1'],
                ['date' => '2026-04-28 10:00:20', 'value' => '0'],
                ['date' => '2026-04-28 10:01:01', 'value' => '1'],
                ['date' => '2026-04-28 10:01:30', 'value' => '2'],
            ],
            new DateTimeImmutable('2026-04-28 10:00:00'),
            new DateTimeImmutable('2026-04-28 10:02:00')
        );

        $this->assertSame(['10:00', '10:01', '10:02'], $timeline['labels']);
        $this->assertSame([0, 2, null], $timeline['values']);
        $this->assertSame(['up' => 0, 'readonly' => 1, 'down' => 1, 'unknown' => 1], $timeline['summary']);
    }

    public function testDetectStatusSinceReturnsOldestContiguousCurrentStatusPoint(): void
    {
        $since = KpiServerDrilldown::detectStatusSince(
            [
                ['date' => '2026-04-28 10:00:00', 'value' => 1],
                ['date' => '2026-04-28 10:01:00', 'value' => 0],
                ['date' => '2026-04-28 10:02:00', 'value' => 0],
            ],
            0
        );

        $this->assertSame('2026-04-28 10:01:00', $since);
    }

    public function testBuildAttemptsSqlFiltersServerWindowAndWorkerPid(): void
    {
        $sql = KpiServerDrilldown::buildAttemptsSql($this->fakeDb(), 217, '2026-04-27 12:00:00');

        $this->assertStringContainsString('FROM `aspirateur_attempt` a', $sql);
        $this->assertStringContainsString('LEFT JOIN `worker_execution` we ON we.id = a.id_worker_execution', $sql);
        $this->assertStringContainsString('LEFT JOIN `worker_run` wr ON wr.id = we.id_worker_run', $sql);
        $this->assertStringContainsString('wr.pid AS worker_pid', $sql);
        $this->assertStringContainsString('WHERE a.id_mysql_server = 217', $sql);
        $this->assertStringContainsString("a.started_at >= '2026-04-27 12:00:00'", $sql);
        $this->assertStringContainsString('ORDER BY a.started_at DESC LIMIT 500', $sql);
    }

    public function testBuildVariableDiffSqlUsesSystemVersionedSnapshot(): void
    {
        $sql = KpiServerDrilldown::buildVariableDiffSql($this->fakeDb(), 217, 200);

        $this->assertStringContainsString('FOR SYSTEM_TIME AS OF TIMESTAMP (NOW() - INTERVAL 1 DAY)', $sql);
        $this->assertStringContainsString('WHERE c.id_mysql_server = 217', $sql);
        $this->assertStringContainsString('c.value <> p.value', $sql);
        $this->assertStringContainsString('LIMIT 201', $sql);
    }

    public function testPingSparklinesGroupByKindAndConvertToMs(): void
    {
        $sparklines = KpiServerDrilldown::buildPingSparklines([
            ['kind' => 'mysql', 'started_at' => '2026-04-28 10:00:00', 'ping_seconds' => '0.012345'],
            ['kind' => 'ssh', 'started_at' => '2026-04-28 10:00:10', 'ping_seconds' => '0.500000'],
            ['kind' => 'mysql', 'started_at' => '2026-04-28 10:00:20', 'ping_seconds' => null],
        ]);

        $this->assertSame([['x' => '2026-04-28 10:00:00', 'y' => 12.345]], $sparklines['mysql']);
        $this->assertSame([['x' => '2026-04-28 10:00:10', 'y' => 500.0]], $sparklines['ssh']);
    }

    public function testPayloadBuilderKeepsMysqlErrorFallbackInSource(): void
    {
        $source = file_get_contents(__DIR__.'/../../../App/Library/Kpi/KpiServerDrilldown.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("'error_class' => 'mysql_error'", $source);
        $this->assertStringContainsString('Current server status is unavailable', $source);
        $this->assertStringContainsString('Server state timeline is unavailable', $source);
    }

    public function testViewAndJavascriptExposeExpectedCharts(): void
    {
        $view = file_get_contents(__DIR__.'/../../../App/view/Kpi/server.view.php');
        $js = file_get_contents(__DIR__.'/../../../App/Webroot/js/Kpi/server.js');

        $this->assertIsString($view);
        $this->assertIsString($js);
        $this->assertStringContainsString('kpi-server-timeline', $view);
        $this->assertStringContainsString('kpi-server-ping', $view);
        $this->assertStringContainsString('aspirateur_attempt 24h', $view);
        $this->assertStringContainsString('Post-mortem', $view);
        $this->assertStringContainsString('postmortem_available', $view);
        $this->assertStringContainsString('More diff rows hidden.', $view);
        $this->assertStringContainsString('window.kpiServerCharts', $js);
        $this->assertStringContainsString('new Chart', $js);
        $this->assertStringContainsString('type: "time"', $js);
        $this->assertStringNotContainsString('parsing: false', $js);
    }

    public function testControllerLoadsChartJsAndKpiServerScript(): void
    {
        $source = file_get_contents(__DIR__.'/../../../App/Controller/Kpi.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('KpiServerDrilldown::buildPayload', $source);
        $this->assertStringContainsString("!empty(\$this->di['js'])", $source);
        $this->assertStringContainsString('chart-4.5.1.umd.min.js', $source);
        $this->assertStringContainsString('Kpi/server.js', $source);
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
