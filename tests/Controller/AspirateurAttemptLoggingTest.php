<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use App\Library\Kpi\AspirateurAttemptLogger;
use PHPUnit\Framework\TestCase;

final class AspirateurAttemptLoggingTest extends TestCase
{
    public function testBuildAttemptSkipsWhenWorkerExecutionIdIsMissing(): void
    {
        $this->assertNull(AspirateurAttemptLogger::buildAttempt([
            'id_mysql_server' => 12,
            'kind' => 'mysql',
            'result' => 1,
        ]));
    }

    public function testBuildAttemptNormalizesStateChangeAndTruncatesErrorMessage(): void
    {
        $attempt = AspirateurAttemptLogger::buildAttempt([
            'id_worker_execution' => 99,
            'id_mysql_server' => 12,
            'kind' => 'mysql',
            'phase' => 'connect',
            'result' => 0,
            'previous_result' => 1,
            'ping_seconds' => 0.1234567,
            'error_class' => \RuntimeException::class,
            'error_message' => 'password=secret '.str_repeat('x', 700),
            'started_at' => '2026-04-28 12:00:00.000001',
            'ended_at' => '2026-04-28 12:00:00.123456',
        ]);

        $this->assertIsArray($attempt);
        $this->assertSame(99, $attempt['id_worker_execution']);
        $this->assertSame(12, $attempt['id_mysql_server']);
        $this->assertSame(0, $attempt['result']);
        $this->assertSame(1, $attempt['triggered_state_change']);
        $this->assertSame(0.123457, $attempt['ping_seconds']);
        $this->assertSame(\RuntimeException::class, $attempt['error_class']);
        $this->assertStringStartsWith('password=*** ', (string)$attempt['error_message']);
        $this->assertSame(512, strlen((string)$attempt['error_message']));
    }

    public function testBuildAttemptStoresReadonlyReasonForResultTwo(): void
    {
        $reason = '{"read_only":"ON","super_read_only":"ON"}';
        $attempt = AspirateurAttemptLogger::buildAttempt([
            'id_worker_execution' => 100,
            'id_mysql_server' => 13,
            'kind' => 'mysql',
            'phase' => 'query',
            'result' => 2,
            'set_readonly_reason' => $reason,
        ]);

        $this->assertIsArray($attempt);
        $this->assertSame(2, $attempt['result']);
        $this->assertSame($reason, $attempt['set_readonly_reason']);
    }

    public function testBuildAttemptRejectsMalformedDatetimeInput(): void
    {
        $attempt = AspirateurAttemptLogger::buildAttempt([
            'id_worker_execution' => 100,
            'id_mysql_server' => 13,
            'kind' => 'mysql',
            'phase' => 'connect',
            'result' => 1,
            'started_at' => 'not-a-date',
            'ended_at' => 'still-not-a-date',
        ]);

        $this->assertIsArray($attempt);
        $this->assertNotSame('not-a-date', $attempt['started_at']);
        $this->assertNotSame('still-not-a-date', $attempt['ended_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $attempt['started_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $attempt['ended_at']);
    }

    public function testBuildInsertSqlEscapesStringsAndUsesExpectedColumns(): void
    {
        $attempt = AspirateurAttemptLogger::buildAttempt([
            'id_worker_execution' => 101,
            'id_mysql_server' => 14,
            'kind' => 'mysql_log',
            'phase' => 'export',
            'result' => 0,
            'error_message' => "can't read log",
            'started_at' => '2026-04-28 12:00:00.000001',
            'ended_at' => '2026-04-28 12:00:01.000001',
        ]);

        $sql = AspirateurAttemptLogger::buildInsertSql($this->fakeDb(), $attempt);

        $this->assertStringContainsString('INSERT INTO `aspirateur_attempt`', $sql);
        $this->assertStringContainsString('`id_worker_execution`', $sql);
        $this->assertStringContainsString("'mysql_log'", $sql);
        $this->assertStringContainsString("'export'", $sql);
        $this->assertStringContainsString("'can\\'t read log'", $sql);
    }

    public function testAspirateurSkipsLoggingWhenTargetIsDefaultDatabase(): void
    {
        if (!defined('DB_DEFAULT')) {
            define('DB_DEFAULT', 'pmacontrol');
        }

        $aspirateur = $this->newAspirateur();
        $result = $this->invokePrivate($aspirateur, 'shouldSkipAspirateurAttempt', [[
            'id_worker_execution' => 42,
            'connection_name' => DB_DEFAULT,
        ]]);

        $this->assertTrue($result);
    }

    public function testWorkerPassesWorkerExecutionIdToWorkerMethod(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Worker.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$msg->refresh, $id_worker_execution', $source);
    }

    public function testAspirateurContainsInstrumentationForExpectedKinds(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Aspirateur.php');

        $this->assertIsString($source);
        foreach ([
            "'kind' => 'mysql'",
            "'kind' => 'ssh'",
            "'kind' => 'proxysql'",
            "'kind' => 'maxscale'",
            "'kind' => 'maxscale_service'",
            "'kind' => 'mysqlrouter'",
            "'kind' => 'mysql_log'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);

        return $instanceMethod->invokeArgs($object, $arguments);
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
