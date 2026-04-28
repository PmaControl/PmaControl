<?php

declare(strict_types=1);

use App\Library\Kpi\KpiDashboard;
use PHPUnit\Framework\TestCase;

final class KpiDashboardTest extends TestCase
{
    public function testNormalizeRangeDefaultsToTwentyFourHours(): void
    {
        $range = KpiDashboard::normalizeRange([], ['now' => '2026-04-28 12:34:56']);

        $this->assertSame('2026-04-27 12:34:00', $range['from']);
        $this->assertSame('2026-04-28 12:34:00', $range['to']);
        $this->assertSame(1441, $range['limit']);
    }

    public function testNormalizeRangeClampsToSevenDays(): void
    {
        $range = KpiDashboard::normalizeRange([
            'from' => '2026-04-01 00:00:00',
            'to' => '2026-04-28 12:00:00',
        ], ['now' => '2026-04-28 12:34:56']);

        $this->assertSame('2026-04-21 12:00:00', $range['from']);
        $this->assertSame('2026-04-28 12:00:00', $range['to']);
        $this->assertSame(10081, $range['limit']);
    }

    public function testSeriesFromRowsBuildsSixChartDefinitions(): void
    {
        $series = KpiDashboard::seriesFromRows([
            [
                'bucket_start' => '2026-04-28 12:00:00',
                'aspirateur_attempts_total' => '10',
                'aspirateur_failures_total' => '2',
                'aspirateur_readonly_total' => '1',
                'state_transitions_total' => '3',
                'aspirateur_p50_ping_ms' => '12',
                'aspirateur_p95_ping_ms' => '44',
                'aspirateur_p99_ping_ms' => null,
                'worker_busy_pct' => '25.50',
                'worker_queue_depth_max' => '7',
                'worker_stuck_count' => '1',
                'daemon_late_count' => '2',
                'event_log_open_count' => '4',
            ],
        ], ['from' => '2026-04-28 11:00:00', 'to' => '2026-04-28 12:00:00', 'limit' => 61]);

        $this->assertCount(6, $series['charts']);
        $this->assertSame('kpi-dashboard-chart-attempts', $series['charts'][0]['canvas_id']);
        $this->assertSame(10.0, $series['charts'][0]['datasets'][0]['points'][0]['y']);
        $this->assertNull($series['charts'][2]['datasets'][2]['points'][0]['y']);
    }

    public function testSeriesSqlUsesBoundedBucketRange(): void
    {
        $sql = KpiDashboard::buildSeriesSql($this->fakeDb(), '2026-04-28 10:00:00', '2026-04-28 12:00:00', 121);

        $this->assertStringContainsString('FROM `kpi_minute`', $sql);
        $this->assertStringContainsString("`bucket_start` >= '2026-04-28 10:00:00'", $sql);
        $this->assertStringContainsString("`bucket_start` <= '2026-04-28 12:00:00'", $sql);
        $this->assertStringContainsString('ORDER BY `bucket_start` ASC LIMIT 121', $sql);
    }

    public function testTopSqlBuildersAlwaysFilterStartedAt(): void
    {
        $db = $this->fakeDb();
        $failures = KpiDashboard::buildTopFailuresSql($db, '2026-04-27 12:00:00');
        $readonly = KpiDashboard::buildTopReadonlySql($db, '2026-04-27 12:00:00');
        $transitions = KpiDashboard::buildTopTransitionsSql($db, '2026-04-27 12:00:00');

        foreach ([$failures, $readonly, $transitions] as $sql) {
            $this->assertStringContainsString('FROM `aspirateur_attempt` a', $sql);
            $this->assertStringContainsString("a.`started_at` >= '2026-04-27 12:00:00'", $sql);
            $this->assertStringContainsString('LIMIT 10', $sql);
            $this->assertStringContainsString('LEFT JOIN `mysql_server`', $sql);
        }

        $this->assertStringContainsString('a.result = 0', $failures);
        $this->assertStringContainsString('a.result = 2', $readonly);
        $this->assertStringContainsString('a.triggered_state_change = 1', $transitions);
    }

    public function testRealtimeSqlBuildersUseExpectedTables(): void
    {
        $worker = KpiDashboard::buildWorkerExecutionStatusSql($this->fakeDb(), '2026-04-28 11:00:00', true);

        $this->assertStringContainsString('FROM `worker_run`', KpiDashboard::buildWorkerRunStatusSql());
        $this->assertStringContainsString('FROM `worker_execution`', $worker);
        $this->assertStringContainsString("`date_started` >= '2026-04-28 11:00:00'", $worker);
        $this->assertStringNotContainsString('OR `date_end` IS NULL', $worker);
        $this->assertStringContainsString("`status` = 'STUCK'", $worker);
        $this->assertStringContainsString('FROM `daemon_main`', KpiDashboard::buildDaemonStatusSql());
        $this->assertStringContainsString('FROM `event_log`', KpiDashboard::buildOpenEventCountSql());
        $this->assertStringContainsString('ORDER BY `date_start` DESC LIMIT 8', KpiDashboard::buildRecentEventsSql());
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
