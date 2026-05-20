<?php

declare(strict_types=1);

use App\Library\Kpi\KpiSanitizer;
use App\Library\Kpi\WorkerExecutionLogger;
use PHPUnit\Framework\TestCase;

final class WorkerExecutionStatusTest extends TestCase
{
    public function testBuildStartSqlCreatesExecutionWithDefaultAttempt(): void
    {
        $payload = WorkerExecutionLogger::buildStartPayload([
            'worker_pid' => 12345,
            'id_mysql_server' => 42,
            'date_started' => '2026-04-28 14:00:00.000001',
        ]);

        $this->assertIsArray($payload);
        $sql = WorkerExecutionLogger::buildStartSql($this->fakeDb(), $payload);

        $this->assertStringContainsString('INSERT INTO `worker_execution`', $sql);
        $this->assertStringContainsString('`status`', $sql);
        $this->assertStringContainsString("'OK'", $sql);
        $this->assertStringContainsString('WHERE `pid` = 12345', $sql);
        $this->assertStringContainsString('AND `is_working` = 1', $sql);
    }

    public function testBuildFinishPayloadForOkStatus(): void
    {
        $payload = WorkerExecutionLogger::buildFinishPayload([
            'id' => 10,
            'status' => 'OK',
            'date_end' => '2026-04-28 14:00:01.000001',
            'execution_time_ms' => 999,
            'max_execution_time' => 1,
            'peak_rss_kb' => 2048,
            'attempt_n' => 1,
        ]);

        $this->assertIsArray($payload);
        $this->assertSame('OK', $payload['status']);
        $this->assertSame(0, $payload['exit_code']);
        $this->assertSame(2048, $payload['peak_rss_kb']);
    }

    public function testBuildFinishPayloadForErrorStatusSanitizesSecrets(): void
    {
        $payload = WorkerExecutionLogger::buildFinishPayload([
            'id' => 11,
            'status' => 'OK',
            'execution_time_ms' => 10,
            'throwable' => new RuntimeException('password=secret mysql://user:pass@example/db', 0),
        ]);

        $this->assertIsArray($payload);
        $this->assertSame('ERROR', $payload['status']);
        $this->assertSame(RuntimeException::class, $payload['error_class']);
        $this->assertSame('password=*** mysql://user:***@example/db', $payload['error_message']);
        $this->assertSame(1, $payload['exit_code']);
    }

    public function testBuildFinishPayloadForTimeoutStatus(): void
    {
        $payload = WorkerExecutionLogger::buildFinishPayload([
            'id' => 12,
            'status' => 'OK',
            'execution_time_ms' => 5001,
            'max_execution_time' => 5,
        ]);

        $this->assertIsArray($payload);
        $this->assertSame('TIMEOUT', $payload['status']);
        $this->assertSame(0, $payload['exit_code']);
    }

    public function testBuildFinishPayloadForSkippedStatus(): void
    {
        $payload = WorkerExecutionLogger::buildFinishPayload([
            'id' => 13,
            'status' => 'SKIPPED',
            'execution_time_ms' => 0,
            'error_message' => 'already locked',
        ]);

        $this->assertIsArray($payload);
        $this->assertSame('SKIPPED', $payload['status']);
        $this->assertSame(0, $payload['exit_code']);
        $this->assertSame('already locked', $payload['error_message']);
    }

    public function testBuildMarkLatestOpenSqlForKilledStatus(): void
    {
        $sql = WorkerExecutionLogger::buildMarkLatestOpenSql(99, 'KILLED');

        $this->assertStringContainsString("`status` = 'KILLED'", $sql);
        $this->assertStringContainsString('`exit_code` = COALESCE(`exit_code`, 137)', $sql);
        $this->assertStringContainsString('WHERE `id_worker_run` = 99', $sql);
        $this->assertStringContainsString('AND `date_end` IS NULL', $sql);
    }

    public function testBuildMarkLatestOpenSqlForStuckStatus(): void
    {
        $sql = WorkerExecutionLogger::buildMarkLatestOpenSql(100, 'STUCK');

        $this->assertStringContainsString("`status` = 'STUCK'", $sql);
        $this->assertStringContainsString('`exit_code` = COALESCE(`exit_code`, 1)', $sql);
        $this->assertStringContainsString('ORDER BY `id` DESC LIMIT 1', $sql);
    }

    public function testBuildFinishSqlStoresKpiColumns(): void
    {
        $payload = WorkerExecutionLogger::buildFinishPayload([
            'id' => 14,
            'status' => 'OK',
            'date_end' => '2026-04-28 14:00:01.000001',
            'execution_time_ms' => 123,
            'db_queries_count' => 5,
            'db_queries_time_ms' => 42,
            'attempt_n' => 2,
        ]);

        $this->assertIsArray($payload);
        $sql = WorkerExecutionLogger::buildFinishSql($this->fakeDb(), $payload);

        $this->assertStringContainsString('`date_end` = ', $sql);
        $this->assertStringContainsString('`execution_time` = 123', $sql);
        $this->assertStringContainsString('`db_queries_count` = 5', $sql);
        $this->assertStringContainsString('`db_queries_time_ms` = 42', $sql);
        $this->assertStringContainsString('`attempt_n` = 2', $sql);
        $this->assertStringContainsString('WHERE `id` = 14', $sql);
        $this->assertStringContainsString("AND `status` NOT IN ('KILLED', 'STUCK')", $sql);
    }

    public function testBuildFinishSqlEscapesErrorMessageApostrophes(): void
    {
        $payload = WorkerExecutionLogger::buildFinishPayload([
            'id' => 15,
            'status' => 'ERROR',
            'execution_time_ms' => 10,
            'error_class' => RuntimeException::class,
            'error_message' => "can't connect password=secret",
            'exit_code' => 1,
        ]);

        $this->assertIsArray($payload);
        $sql = WorkerExecutionLogger::buildFinishSql($this->fakeDb(), $payload);

        $this->assertStringContainsString("'can\\'t connect password=***'", $sql);
    }

    public function testQueryCountersStayNullWhenProfilingIsDisabled(): void
    {
        $previous = getenv('KPI_PROFILE_QUERIES');
        putenv('KPI_PROFILE_QUERIES');

        try {
            $this->assertNull(WorkerExecutionLogger::snapshotDbCounters());
            $this->assertSame([
                'db_queries_count' => null,
                'db_queries_time_ms' => null,
            ], WorkerExecutionLogger::diffDbCounters(null));
        } finally {
            if ($previous !== false) {
                putenv('KPI_PROFILE_QUERIES='.$previous);
            }
        }
    }

    public function testKpiSanitizerMasksSharedSecretPatterns(): void
    {
        $this->assertSame(
            'token=*** mysql://user:***@example/db',
            KpiSanitizer::errorMessage('token=abc mysql://user:secret@example/db')
        );
    }

    public function testWorkerUsesExecutionLoggerHooks(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Worker.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('WorkerExecutionLogger::startForWorkerPid', $source);
        $this->assertStringContainsString('memory_reset_peak_usage', $source);
        $this->assertStringContainsString('catch (WorkerSkipException $e)', $source);
        $this->assertStringContainsString('WorkerExecutionLogger::finish($id_worker_execution', $source);
        $this->assertStringContainsString('WorkerExecutionLogger::markStuckForRun', $source);
        $this->assertStringContainsString('WorkerExecutionLogger::markKilledForRun', $source);
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
