<?php

declare(strict_types=1);

use App\Library\Kpi\EventLog;
use App\Library\Kpi\KpiBudgetAlert;
use PHPUnit\Framework\TestCase;

final class KpiBudgetAlertTest extends TestCase
{
    public function testRulesExposeExpectedEventTypesAndThresholds(): void
    {
        $rules = KpiBudgetAlert::rules([
            'worker_busy_pct' => 91,
            'worker_busy_min' => 6,
            'aspirateur_failures_total' => 3,
        ]);

        $this->assertArrayHasKey('kpi_workers_saturated', $rules);
        $this->assertArrayHasKey('kpi_worker_stuck', $rules);
        $this->assertArrayHasKey('kpi_daemon_late', $rules);
        $this->assertArrayHasKey('kpi_flapping', $rules);
        $this->assertArrayHasKey('kpi_collect_degraded', $rules);
        $this->assertSame(91.0, $rules['kpi_workers_saturated']['threshold']);
        $this->assertSame(6, $rules['kpi_workers_saturated']['duration_minutes']);
        $this->assertSame(3, $rules['kpi_collect_degraded']['threshold']);
    }

    public function testBuildRuleWindowSqlUsesCompleteWindow(): void
    {
        $rules = KpiBudgetAlert::rules(['worker_busy_pct' => 90, 'worker_busy_min' => 5]);
        $sql = KpiBudgetAlert::buildRuleWindowSql($this->fakeDb(), $rules['kpi_workers_saturated'], '2026-04-28 12:04:37');

        $this->assertStringContainsString('`worker_busy_pct` > 90', $sql);
        $this->assertStringContainsString("`bucket_start` >= '2026-04-28 12:00:00'", $sql);
        $this->assertStringContainsString("`bucket_start` <= '2026-04-28 12:04:00'", $sql);
        $this->assertStringContainsString('COUNT(*) AS `bucket_count`', $sql);
    }

    public function testEventLogSqlIsScopedAndIdempotentFriendly(): void
    {
        $db = $this->fakeDb();
        $lookup = EventLog::buildOpenEventLookupSql($db, 'kpi_workers_saturated', null);
        $insert = EventLog::buildInsertEventSql($db, 'kpi_workers_saturated', 'busy', null, '2026-04-28 12:04:00');
        $close = EventLog::buildCloseEventSql($db, 42, '2026-04-28 12:05:00');
        $lock = EventLog::buildAcquireLockSql($db, 'kpi_workers_saturated', null);
        $unlock = EventLog::buildReleaseLockSql($db, 'kpi_workers_saturated', null);

        $this->assertStringContainsString('`id_mysql_server` IS NULL', $lookup);
        $this->assertStringContainsString('`date_end` IS NULL', $lookup);
        $this->assertStringContainsString("'kpi_workers_saturated'", $insert);
        $this->assertStringContainsString("'2026-04-28 12:04:00'", $insert);
        $this->assertStringContainsString("WHERE `id` = 42 AND `date_end` IS NULL", $close);
        $this->assertStringContainsString('GET_LOCK', $lock);
        $this->assertStringContainsString('pmacontrol_kpi_event_', $lock);
        $this->assertStringContainsString('RELEASE_LOCK', $unlock);
    }

    public function testWorkerSaturationOpensOnceThenClosesWhenMysqlIsAvailable(): void
    {
        $probe = $this->runMysql('SELECT 1;', ['--skip-column-names'], true);
        if ($probe['exitCode'] !== 0 || trim($probe['stdout']) !== '1') {
            self::markTestSkipped('Local mysql client is not available or cannot connect.');
        }

        $database = 'pmacontrol_kpi_alert_test_' . getmypid() . '_' . bin2hex(random_bytes(3));
        $databaseIdentifier = $this->quoteIdentifier($database);
        $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier}; CREATE DATABASE {$databaseIdentifier} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

        try {
            $this->runMysql($this->fixtureSchema(), ["--database={$database}"]);
            $this->runMysql($this->busyRows(), ["--database={$database}"]);

            $db = $this->mysqlCliDb($database);
            $overrides = [
                'worker_busy_pct' => 90,
                'worker_busy_min' => 5,
                'worker_stuck_min' => 2,
                'daemon_late_min' => 5,
                'state_transitions_per_min' => 99,
                'flapping_min' => 5,
                'aspirateur_failures_total' => 99,
                'aspirateur_failures_min' => 1,
            ];

            $first = KpiBudgetAlert::evaluateBucket($db, '2026-04-28 12:04:00', $overrides);
            $second = KpiBudgetAlert::evaluateBucket($db, '2026-04-28 12:04:00', $overrides);
            $this->assertTrue($first['kpi_workers_saturated']['breaching']);
            $this->assertSame('opened', $first['kpi_workers_saturated']['event']['action']);
            $this->assertSame('existing', $second['kpi_workers_saturated']['event']['action']);

            $opened = $this->runMysql("SELECT COUNT(*), MIN(type), MIN(date_start), MAX(date_end) FROM event_log;", ["--database={$database}", '--skip-column-names']);
            $this->assertSame("1\tkpi_workers_saturated\t2026-04-28 12:04:00.000000\tNULL", trim($opened['stdout']));

            $this->runMysql("INSERT INTO kpi_minute VALUES ('2026-04-28 12:05:00', 0, 20.00, 0, 0, 0);", ["--database={$database}"]);
            $closed = KpiBudgetAlert::evaluateBucket($db, '2026-04-28 12:05:00', $overrides);
            $this->assertFalse($closed['kpi_workers_saturated']['breaching']);
            $this->assertSame('closed', $closed['kpi_workers_saturated']['event']['action']);

            $closedRow = $this->runMysql("SELECT COUNT(*), MIN(type), MIN(date_start), MAX(date_end) FROM event_log;", ["--database={$database}", '--skip-column-names']);
            $this->assertSame("1\tkpi_workers_saturated\t2026-04-28 12:04:00.000000\t2026-04-28 12:05:00.000000", trim($closedRow['stdout']));
        } finally {
            $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier};", [], true);
        }
    }

    public function testKpiRollupCallsBudgetAlertAndConfigSampleExists(): void
    {
        $rollup = file_get_contents(__DIR__.'/../../../App/Library/Kpi/KpiMinuteRollup.php');
        $listener = file_get_contents(__DIR__.'/../../../App/Controller/Listener.php');
        $config = file_get_contents(__DIR__.'/../../../config_sample/kpi.config.php');

        $this->assertIsString($rollup);
        $this->assertIsString($listener);
        $this->assertIsString($config);
        $this->assertStringContainsString('KpiBudgetAlert::evaluateBucket', $rollup);
        $this->assertStringContainsString('EventLog::recordEvent', $listener);
        $this->assertStringContainsString('KPI_BUDGET_WORKER_BUSY_PCT', $config);
        $this->assertStringContainsString('KPI_BUDGET_ASPIRATEUR_FAILURES_TOTAL', $config);
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

    private function fixtureSchema(): string
    {
        return <<<'SQL'
CREATE TABLE kpi_minute (
  bucket_start DATETIME NOT NULL PRIMARY KEY,
  aspirateur_failures_total INT UNSIGNED NOT NULL DEFAULT 0,
  worker_busy_pct DECIMAL(5,2) NULL,
  worker_stuck_count INT UNSIGNED NOT NULL DEFAULT 0,
  daemon_late_count INT UNSIGNED NOT NULL DEFAULT 0,
  state_transitions_total INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE event_log (
  id INT NOT NULL AUTO_INCREMENT,
  id_mysql_server INT NULL,
  id_proxysql_server INT NULL,
  id_maxscale_server INT NULL,
  id_docker_host INT NULL,
  type VARCHAR(64) NOT NULL,
  message TEXT NOT NULL,
  date_start DATETIME(6) NOT NULL,
  date_end DATETIME(6) NULL,
  PRIMARY KEY (id),
  KEY idx_type (type),
  KEY idx_kpi_open_incidents (date_start, date_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;
    }

    private function busyRows(): string
    {
        return <<<'SQL'
INSERT INTO kpi_minute
  (bucket_start, aspirateur_failures_total, worker_busy_pct, worker_stuck_count, daemon_late_count, state_transitions_total)
VALUES
  ('2026-04-28 12:00:00', 0, 95.00, 0, 0, 0),
  ('2026-04-28 12:01:00', 0, 95.00, 0, 0, 0),
  ('2026-04-28 12:02:00', 0, 95.00, 0, 0, 0),
  ('2026-04-28 12:03:00', 0, 95.00, 0, 0, 0),
  ('2026-04-28 12:04:00', 0, 95.00, 0, 0, 0);
SQL;
    }

    private function mysqlCliDb(string $database): object
    {
        $runner = function (string $sql, array $args = []) {
            return $this->runMysql($sql, $args);
        };

        return new class($database, $runner) {
            private string $database;
            private Closure $runner;
            private array $rows = [];

            public function __construct(string $database, Closure $runner)
            {
                $this->database = $database;
                $this->runner = $runner;
            }

            public function sql_real_escape_string(string $value): string
            {
                return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
            }

            public function sql_query(string $sql)
            {
                $result = ($this->runner)($sql, ["--database={$this->database}"]);
                $this->rows = $this->parseRows($result['stdout']);

                return true;
            }

            public function sql_fetch_array($result, $mode = null): ?array
            {
                return array_shift($this->rows);
            }

            private function parseRows(string $stdout): array
            {
                $lines = array_values(array_filter(explode("\n", trim($stdout)), static fn ($line) => $line !== ''));
                if (count($lines) < 2) {
                    return [];
                }

                $headers = explode("\t", array_shift($lines));
                $rows = [];
                foreach ($lines as $line) {
                    $values = explode("\t", $line);
                    $row = [];
                    foreach ($headers as $index => $header) {
                        $value = $values[$index] ?? null;
                        $row[$header] = $value === 'NULL' ? null : $value;
                    }
                    $rows[] = $row;
                }

                return $rows;
            }
        };
    }

    /**
     * @param list<string> $args
     * @return array{exitCode:int, stdout:string, stderr:string}
     */
    private function runMysql(string $sql, array $args = [], bool $allowFailure = false): array
    {
        $command = array_merge(['mysql', '--batch', '--raw'], $args);
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        try {
            $process = proc_open($command, $descriptorSpec, $pipes);
        } catch (Throwable $e) {
            if ($allowFailure) {
                return ['exitCode' => 127, 'stdout' => '', 'stderr' => $e->getMessage()];
            }

            $this->fail($e->getMessage());
        }

        if (!is_resource($process)) {
            if ($allowFailure) {
                return ['exitCode' => 127, 'stdout' => '', 'stderr' => 'Unable to start mysql client.'];
            }

            $this->fail('Unable to start mysql client.');
        }

        fwrite($pipes[0], $sql);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        $result = [
            'exitCode' => $exitCode,
            'stdout' => is_string($stdout) ? $stdout : '',
            'stderr' => is_string($stderr) ? $stderr : '',
        ];

        if ($exitCode !== 0 && !$allowFailure) {
            $this->fail(sprintf(
                "mysql exited with %d\nSTDOUT:\n%s\nSTDERR:\n%s",
                $exitCode,
                $result['stdout'],
                $result['stderr']
            ));
        }

        return $result;
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
