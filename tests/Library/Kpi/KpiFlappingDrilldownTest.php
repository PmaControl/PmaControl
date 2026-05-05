<?php

declare(strict_types=1);

use App\Library\Kpi\KpiFlappingDrilldown;
use PHPUnit\Framework\TestCase;

final class KpiFlappingDrilldownTest extends TestCase
{
    public function testClassifyErrorMessageExtractsKnownCodes(): void
    {
        $this->assertSame('MaxScaleSessionLost', KpiFlappingDrilldown::classifyErrorMessage('MaxScaleSessionLost while routing')['code']);
        $this->assertSame('Connection refused', KpiFlappingDrilldown::classifyErrorMessage('Connection refused')['code']);
        $this->assertSame('Timed out', KpiFlappingDrilldown::classifyErrorMessage('Operation timed out')['code']);
        $this->assertSame('Access denied', KpiFlappingDrilldown::classifyErrorMessage('Access denied for user')['code']);
        $this->assertSame('Other', KpiFlappingDrilldown::classifyErrorMessage('unexpected router failure')['code']);
        $this->assertSame('-', KpiFlappingDrilldown::classifyErrorMessage('')['code']);
    }

    public function testDetectFlappingWindowsRequiresConsecutiveBreachingMinutes(): void
    {
        $windows = KpiFlappingDrilldown::detectFlappingWindows([
            ['bucket_start' => '2026-04-28 12:00:00', 'state_transitions_total' => 3],
            ['bucket_start' => '2026-04-28 12:01:00', 'state_transitions_total' => 4],
            ['bucket_start' => '2026-04-28 12:02:00', 'state_transitions_total' => 3],
            ['bucket_start' => '2026-04-28 12:03:00', 'state_transitions_total' => 5],
            ['bucket_start' => '2026-04-28 12:04:00', 'state_transitions_total' => 3],
            ['bucket_start' => '2026-04-28 12:06:00', 'state_transitions_total' => 3],
        ], 2, 5);

        $this->assertCount(1, $windows);
        $this->assertSame('2026-04-28 12:00:00', $windows[0]['start']);
        $this->assertSame('2026-04-28 12:04:00', $windows[0]['end']);
        $this->assertSame(5, $windows[0]['minutes']);
        $this->assertSame(5, $windows[0]['max_value']);
        $this->assertSame(18, $windows[0]['total_transitions']);
    }

    public function testDetectFlappingWindowsRejectsIsolatedTransitions(): void
    {
        $windows = KpiFlappingDrilldown::detectFlappingWindows([
            ['bucket_start' => '2026-04-28 12:00:00', 'state_transitions_total' => 3],
            ['bucket_start' => '2026-04-28 12:01:00', 'state_transitions_total' => 0],
            ['bucket_start' => '2026-04-28 12:02:00', 'state_transitions_total' => 3],
        ], 2, 5);

        $this->assertSame([], $windows);
    }

    public function testRouterSqlBuildersTargetMysqlRouterAttempts(): void
    {
        $db = $this->fakeDb();
        $router = KpiFlappingDrilldown::buildRouterSql($db, 4);
        $linked = KpiFlappingDrilldown::buildLinkedServersSql($db, 4);
        $histogram = KpiFlappingDrilldown::buildRouterHistogramSql($db, 4, '2026-04-27 12:00:00');
        $attempts = KpiFlappingDrilldown::buildRouterAttemptsSql($db, 4, '2026-04-27 12:00:00');

        $this->assertStringContainsString('FROM `mysqlrouter_server`', $router);
        $this->assertStringContainsString('WHERE `id` = 4', $router);
        $this->assertStringContainsString('FROM `mysqlrouter_server__mysql_server` l', $linked);
        $this->assertStringContainsString('l.id_mysqlrouter_server = 4', $linked);
        $this->assertStringContainsString("WHERE `kind` = 'mysqlrouter'", $histogram);
        $this->assertStringContainsString('`id_mysqlrouter_server` = 4', $histogram);
        $this->assertStringContainsString('GROUP BY `bucket_start`', $histogram);
        $this->assertStringContainsString('FROM `aspirateur_attempt` a', $attempts);
        $this->assertStringContainsString("a.`kind` = 'mysqlrouter'", $attempts);
        $this->assertStringContainsString('wr.pid AS worker_pid', $attempts);
        $this->assertStringContainsString('ORDER BY a.`started_at` DESC LIMIT 500', $attempts);
    }

    public function testGlobalSqlBuildersUseKpiMinuteEventLogAndTopRouters(): void
    {
        $db = $this->fakeDb();
        $buckets = KpiFlappingDrilldown::buildGlobalBucketsSql($db, '2026-04-27 12:00:00');
        $events = KpiFlappingDrilldown::buildFlappingEventsSql($db, '2026-04-27 12:00:00');
        $top = KpiFlappingDrilldown::buildTopRoutersSql($db, '2026-04-27 12:00:00');

        $this->assertStringContainsString('FROM `kpi_minute`', $buckets);
        $this->assertStringContainsString('`state_transitions_total`', $buckets);
        $this->assertStringContainsString('LIMIT 1440', $buckets);
        $this->assertStringContainsString('FROM `event_log`', $events);
        $this->assertStringContainsString("'kpi_flapping'", $events);
        $this->assertStringContainsString('FROM `aspirateur_attempt` a', $top);
        $this->assertStringContainsString("a.kind = 'mysqlrouter'", $top);
        $this->assertStringContainsString('ORDER BY transitions_total DESC', $top);
        $this->assertStringContainsString('LIMIT 10', $top);
    }

    public function testControllerViewsAndAclExposeFlappingRoutes(): void
    {
        $controller = file_get_contents(__DIR__.'/../../../App/Controller/Kpi.php');
        $flappingView = file_get_contents(__DIR__.'/../../../App/view/Kpi/flapping.view.php');
        $routerView = file_get_contents(__DIR__.'/../../../App/view/Kpi/router.view.php');
        $acl = file_get_contents(__DIR__.'/../../../config_sample/acl.config.ini');

        $this->assertIsString($controller);
        $this->assertIsString($flappingView);
        $this->assertIsString($routerView);
        $this->assertIsString($acl);
        $this->assertStringContainsString('KpiFlappingDrilldown::buildGlobalPayload', $controller);
        $this->assertStringContainsString('KpiFlappingDrilldown::buildRouterPayload', $controller);
        $this->assertStringContainsString('public function flapping($param)', $controller);
        $this->assertStringContainsString('public function router($param)', $controller);
        $this->assertStringContainsString('Top 10 MySQL Router instables 24h', $flappingView);
        $this->assertStringContainsString('event_log kpi_flapping', $flappingView);
        $this->assertStringContainsString('Histogramme mysqlrouter transitions/minute', $routerView);
        $this->assertStringContainsString('logs_path', $routerView);
        $this->assertStringContainsString('ReadOnly[] = "Kpi/flapping"', $acl);
        $this->assertStringContainsString('ReadOnly[] = "Kpi/router"', $acl);
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
