<?php

declare(strict_types=1);

use App\Controller\Kpi;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class KpiProcessSecurityTest extends TestCase
{
    public function testProcessKillRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, Kpi::KPI_PROCESS_KILL_CSRF_SCOPE);

        $outcome = Kpi::evaluateProcessKillRequest(
            [Csrf::DEFAULT_FIELD => $token, 'pid' => '1234', 'start_time_ticks' => '987654'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session,
            [1234]
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(1234, $outcome['pid']);
        $this->assertSame(987654, $outcome['start_time_ticks']);
    }

    public function testProcessKillRejectsGetExternalOriginAndPidMismatch(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, Kpi::KPI_PROCESS_KILL_CSRF_SCOPE);

        $get = Kpi::evaluateProcessKillRequest([], ['REQUEST_METHOD' => 'GET'], $session, [1234]);
        $external = Kpi::evaluateProcessKillRequest(
            [Csrf::DEFAULT_FIELD => $token, 'pid' => '1234', 'start_time_ticks' => '987654'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            [1234]
        );
        $mismatch = Kpi::evaluateProcessKillRequest(
            [Csrf::DEFAULT_FIELD => $token, 'pid' => '9999', 'start_time_ticks' => '987654'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session,
            [1234]
        );

        $this->assertSame(405, $get['status']);
        $this->assertSame('POST', $get['headers']['Allow']);
        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertSame(400, $mismatch['status']);
        $this->assertSame('Process id mismatch', $mismatch['body']);
    }

    public function testProcessKillRejectsMissingOrInvalidProcessIdentity(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, Kpi::KPI_PROCESS_KILL_CSRF_SCOPE);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];

        $missing = Kpi::evaluateProcessKillRequest(
            [Csrf::DEFAULT_FIELD => $token, 'pid' => '1234'],
            $server,
            $session,
            [1234]
        );
        $invalid = Kpi::evaluateProcessKillRequest(
            [Csrf::DEFAULT_FIELD => $token, 'pid' => '1234', 'start_time_ticks' => 'abc'],
            $server,
            $session,
            [1234]
        );

        $this->assertSame(400, $missing['status']);
        $this->assertSame('Invalid process identity', $missing['body']);
        $this->assertSame(400, $invalid['status']);
        $this->assertSame('Invalid process identity', $invalid['body']);
    }

    public function testWorkerKillSqlIsScopedToActivePid(): void
    {
        $sql = \App\Controller\Worker::buildActiveWorkerRunByPidSql(1234);
        $update = \App\Controller\Worker::buildMarkWorkerRunKilledSql(77, '2026-04-28 12:00:00');

        $this->assertStringContainsString('WHERE a.is_working = 1 AND a.pid = 1234', $sql);
        $this->assertStringContainsString('ORDER BY a.id DESC LIMIT 1', $sql);
        $this->assertSame(
            "UPDATE worker_run SET is_working=0, is_safe_kill=1, date_killed='2026-04-28 12:00:00' WHERE id=77;",
            $update
        );
    }
}
