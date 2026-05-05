<?php

declare(strict_types=1);

use App\Library\Kpi\KpiReadonlyDrilldown;
use PHPUnit\Framework\TestCase;

final class KpiReadonlyDrilldownTest extends TestCase
{
    public function testParseReadonlyReasonExtractsGlobalVariables(): void
    {
        $reason = KpiReadonlyDrilldown::parseReadonlyReason(json_encode([
            '@@global.read_only' => 'ON',
            '@@global.super_read_only' => 'OFF',
            'source' => 'maxscale',
        ]));

        $this->assertTrue($reason['valid']);
        $this->assertSame('ON', $reason['read_only']);
        $this->assertSame('OFF', $reason['super_read_only']);
        $this->assertSame('maxscale', $reason['values']['source']);
    }

    public function testParseReadonlyReasonHandlesMissingAndInvalidPayloads(): void
    {
        $missing = KpiReadonlyDrilldown::parseReadonlyReason(null);
        $invalid = KpiReadonlyDrilldown::parseReadonlyReason('{invalid');

        $this->assertFalse($missing['valid']);
        $this->assertSame('No set_readonly_reason payload captured.', $missing['message']);
        $this->assertFalse($invalid['valid']);
        $this->assertSame('Invalid JSON payload.', $invalid['message']);
    }

    public function testBuildTransitionsSqlTargetsReadonlyStateChangesAndWorkerPid(): void
    {
        $sql = KpiReadonlyDrilldown::buildTransitionsSql($this->fakeDb(), 217, '2026-04-27 12:00:00');

        $this->assertStringContainsString('FROM `aspirateur_attempt` a', $sql);
        $this->assertStringContainsString('LEFT JOIN `worker_execution` we ON we.id = a.id_worker_execution', $sql);
        $this->assertStringContainsString('LEFT JOIN `worker_run` wr ON wr.id = we.id_worker_run', $sql);
        $this->assertStringContainsString('wr.pid AS worker_pid', $sql);
        $this->assertStringContainsString('a.id_mysql_server = 217', $sql);
        $this->assertStringContainsString('a.result = 2', $sql);
        $this->assertStringContainsString('a.triggered_state_change = 1', $sql);
        $this->assertStringContainsString("a.started_at >= '2026-04-27 12:00:00'", $sql);
        $this->assertStringContainsString('ORDER BY a.started_at DESC LIMIT 500', $sql);
    }

    public function testBuildEventsSqlUsesOverlapWindowAndServerFilter(): void
    {
        $sql = KpiReadonlyDrilldown::buildEventsSql(
            $this->fakeDb(),
            217,
            '2026-04-28 09:55:00',
            '2026-04-28 10:05:00'
        );

        $this->assertStringContainsString('FROM `event_log`', $sql);
        $this->assertStringContainsString('`id_mysql_server` = 217', $sql);
        $this->assertStringContainsString("`date_start` <= '2026-04-28 10:05:00'", $sql);
        $this->assertStringContainsString("`date_end` IS NULL OR `date_end` >= '2026-04-28 09:55:00'", $sql);
        $this->assertStringContainsString('ORDER BY `date_start` DESC LIMIT 500', $sql);
    }

    public function testCorrelateEventsAttachesOnlyOverlappingRows(): void
    {
        $transitions = KpiReadonlyDrilldown::correlateEvents(
            [
                ['started_at' => '2026-04-28 10:00:00'],
                ['started_at' => '2026-04-28 12:00:00'],
            ],
            [
                ['id' => 1, 'date_start' => '2026-04-28 09:57:00', 'date_end' => '2026-04-28 10:01:00'],
                ['id' => 2, 'date_start' => '2026-04-28 11:00:00', 'date_end' => '2026-04-28 11:01:00'],
                ['id' => 3, 'date_start' => '2026-04-28 11:59:00', 'date_end' => null],
            ],
            300
        );

        $this->assertSame([1], array_column($transitions[0]['events'], 'id'));
        $this->assertSame([3], array_column($transitions[1]['events'], 'id'));
    }

    public function testBuildContextPathTargetsMysqlServerRunDetail(): void
    {
        $this->assertSame(
            'MysqlServer/runDetail/217/20260428100102',
            KpiReadonlyDrilldown::buildContextPath(217, '2026-04-28 10:01:02.123456')
        );

        $this->assertSame('', KpiReadonlyDrilldown::buildContextPath(0, '2026-04-28 10:01:02'));
    }

    public function testControllerViewAndAclExposeReadonlyDrilldown(): void
    {
        $controller = file_get_contents(__DIR__.'/../../../App/Controller/Kpi.php');
        $view = file_get_contents(__DIR__.'/../../../App/view/Kpi/aspirateur.view.php');
        $acl = file_get_contents(__DIR__.'/../../../config_sample/acl.config.ini');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($acl);
        $this->assertStringContainsString('KpiReadonlyDrilldown::buildPayload', $controller);
        $this->assertStringContainsString('public function aspirateur($param)', $controller);
        $this->assertStringContainsString("(\$param[0] ?? '') !== 'readonly'", $controller);
        $this->assertStringContainsString('read_only transitions', $view);
        $this->assertStringContainsString('set_readonly_reason', $view);
        $this->assertStringContainsString('event_log +/-5min', $view);
        $this->assertStringContainsString('Correlation history_action', $view);
        $this->assertStringContainsString('context_path', $view);
        $this->assertStringContainsString('ReadOnly[] = "Kpi/aspirateur"', $acl);
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
