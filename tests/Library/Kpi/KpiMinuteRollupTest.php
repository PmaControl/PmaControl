<?php

declare(strict_types=1);

use App\Library\Kpi\KpiMinuteRollup;
use PHPUnit\Framework\TestCase;

final class KpiMinuteRollupTest extends TestCase
{
    private const MIGRATION = __DIR__ . '/../../../sql/incremental_v2/20260428_kpi_rollup_daemon.sql';

    public function testBuildDueBucketsStartsAtPreviousCompleteMinuteWithoutWatermark(): void
    {
        $this->assertSame(
            ['2026-04-28 12:02:00'],
            KpiMinuteRollup::buildDueBuckets(null, '2026-04-28 12:03:45', 60)
        );
    }

    public function testBuildDueBucketsResumesAfterLastBucketAndHonorsLimit(): void
    {
        $this->assertSame(
            [
                '2026-04-28 12:01:00',
                '2026-04-28 12:02:00',
            ],
            KpiMinuteRollup::buildDueBuckets('2026-04-28 12:00:00', '2026-04-28 12:05:20', 2)
        );
    }

    public function testBuildDueBucketsDoesNotIncludeCurrentIncompleteMinute(): void
    {
        $this->assertSame(
            [],
            KpiMinuteRollup::buildDueBuckets('2026-04-28 12:02:00', '2026-04-28 12:03:20', 60)
        );
    }

    public function testBuildRollupSqlContainsLockSafeIdempotentInsert(): void
    {
        $sql = KpiMinuteRollup::buildRollupSql($this->fakeDb(), '2026-04-28 12:00:00');

        $this->assertStringContainsString('INSERT INTO `kpi_minute`', $sql);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
        $this->assertStringContainsString('PERCENTILE_CONT(0.95)', $sql);
        $this->assertStringContainsString('FROM `worker_execution`', $sql);
        $this->assertStringContainsString('NULL AS `worker_queue_depth_max`', $sql);
        $this->assertStringContainsString("`date_start` < '2026-04-28 12:01:00'", $sql);
    }

    public function testLockSqlUsesNamedMysqlLock(): void
    {
        $this->assertStringContainsString('GET_LOCK', KpiMinuteRollup::buildAcquireLockSql($this->fakeDb()));
        $this->assertStringContainsString('pmacontrol_kpi_minute_rollup', KpiMinuteRollup::buildAcquireLockSql($this->fakeDb()));
        $this->assertStringContainsString('RELEASE_LOCK', KpiMinuteRollup::buildReleaseLockSql($this->fakeDb()));
    }

    public function testRollupSqlComputesExpectedBucketCountersWhenMysqlIsAvailable(): void
    {
        $probe = $this->runMysql('SELECT 1;', [], true);
        if ($probe['exitCode'] !== 0 || trim($probe['stdout']) !== '1') {
            self::markTestSkipped('Local mysql client is not available or cannot connect.');
        }

        $database = 'pmacontrol_kpi_rollup_test_' . getmypid() . '_' . bin2hex(random_bytes(3));
        $databaseIdentifier = $this->quoteIdentifier($database);

        $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier}; CREATE DATABASE {$databaseIdentifier} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

        try {
            $this->runMysql($this->fixtureSchema(), ["--database={$database}"]);
            $this->runMysql($this->fixtureData(), ["--database={$database}"]);
            $this->runMysql(KpiMinuteRollup::buildRollupSql($this->fakeDb(), '2026-04-28 12:00:00'), ["--database={$database}"]);
            $this->runMysql(KpiMinuteRollup::buildRollupSql($this->fakeDb(), '2026-04-28 12:00:00'), ["--database={$database}"]);

            $result = $this->runMysql(<<<'SQL'
SELECT
  COUNT(*) AS rows_total,
  MAX(aspirateur_attempts_total),
  MAX(aspirateur_failures_total),
  MAX(aspirateur_readonly_total),
  MAX(aspirateur_p50_ping_ms),
  MAX(aspirateur_p95_ping_ms),
  MAX(aspirateur_p99_ping_ms),
  MAX(worker_busy_pct),
  MAX(worker_queue_depth_max),
  MAX(worker_stuck_count),
  MAX(daemon_late_count),
  MAX(state_transitions_total),
  MAX(event_log_open_count)
FROM kpi_minute;
SQL, ["--database={$database}"]);

            $row = explode("\t", trim($result['stdout']));

            $this->assertSame('1', $row[0]);
            $this->assertSame('5', $row[1]);
            $this->assertSame('2', $row[2]);
            $this->assertSame('1', $row[3]);
            $this->assertSame('30', $row[4]);
            $this->assertSame('88', $row[5]);
            $this->assertSame('98', $row[6]);
            $this->assertSame('44.44', $row[7]);
            $this->assertSame('NULL', $row[8]);
            $this->assertSame('1', $row[9]);
            $this->assertSame('1', $row[10]);
            $this->assertSame('2', $row[11]);
            $this->assertSame('2', $row[12]);
        } finally {
            $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier};", [], true);
        }
    }

    public function testRollupDaemonMigrationIsIdempotentAndIndexesEventLog(): void
    {
        $sql = file_get_contents(self::MIGRATION);

        $this->assertIsString($sql);
        $this->assertStringContainsString('INSERT IGNORE INTO `daemon_main`', $sql);
        $this->assertStringContainsString("'KPI minute rollup'", $sql);
        $this->assertStringContainsString("'KpiRollup'", $sql);
        $this->assertStringContainsString("'rollupMinute'", $sql);
        $this->assertStringContainsString('ADD INDEX IF NOT EXISTS `idx_kpi_open_incidents` (`date_start`, `date_end`)', $sql);
    }

    public function testRollupDaemonMigrationCanBeAppliedTwiceWhenMysqlIsAvailable(): void
    {
        $probe = $this->runMysql('SELECT 1;', [], true);
        if ($probe['exitCode'] !== 0 || trim($probe['stdout']) !== '1') {
            self::markTestSkipped('Local mysql client is not available or cannot connect.');
        }

        $migration = file_get_contents(self::MIGRATION);
        $this->assertIsString($migration);

        $database = 'pmacontrol_kpi_rollup_migration_test_' . getmypid() . '_' . bin2hex(random_bytes(3));
        $databaseIdentifier = $this->quoteIdentifier($database);

        $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier}; CREATE DATABASE {$databaseIdentifier} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

        try {
            $this->runMysql($this->migrationFixtureSchema(), ["--database={$database}"]);
            $this->runMysql($migration . "\n" . $migration, ["--database={$database}"]);

            $checks = $this->runMysql(<<<'SQL'
SELECT 'daemon_rows', COUNT(*)
FROM daemon_main
WHERE name = 'KPI minute rollup'
  AND class = 'KpiRollup'
  AND method = 'rollupMinute'
  AND refresh_time = 60
  AND max_delay = 30
  AND is_enabled = 1;

SELECT 'event_log_index', COUNT(*)
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = 'event_log'
  AND index_name = 'idx_kpi_open_incidents';
SQL, ["--database={$database}"]);

            $rows = $this->parseMysqlKeyValueRows($checks['stdout']);
            $this->assertSame(1, $rows['daemon_rows'] ?? null);
            $this->assertSame(2, $rows['event_log_index'] ?? null);
        } finally {
            $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier};", [], true);
        }
    }

    public function testControllerDelegatesToMinuteRollup(): void
    {
        $source = file_get_contents(__DIR__.'/../../../App/Controller/KpiRollup.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('if (!IS_CLI)', $source);
        $this->assertStringContainsString('KpiMinuteRollup::rollupMinute', $source);
        $this->assertStringContainsString('max_buckets', $source);
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
  aspirateur_attempts_total INT UNSIGNED NOT NULL DEFAULT 0,
  aspirateur_failures_total INT UNSIGNED NOT NULL DEFAULT 0,
  aspirateur_readonly_total INT UNSIGNED NOT NULL DEFAULT 0,
  aspirateur_p50_ping_ms INT UNSIGNED NULL,
  aspirateur_p95_ping_ms INT UNSIGNED NULL,
  aspirateur_p99_ping_ms INT UNSIGNED NULL,
  worker_busy_pct DECIMAL(5,2) NULL,
  worker_queue_depth_max INT UNSIGNED NULL,
  worker_stuck_count INT UNSIGNED NOT NULL DEFAULT 0,
  daemon_late_count INT UNSIGNED NOT NULL DEFAULT 0,
  state_transitions_total INT UNSIGNED NOT NULL DEFAULT 0,
  event_log_open_count INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE aspirateur_attempt (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  result TINYINT NOT NULL,
  ping_seconds DECIMAL(10,6) NULL,
  triggered_state_change TINYINT(1) NOT NULL DEFAULT 0,
  started_at DATETIME(6) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE daemon_run (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  over_max_delay TINYINT(1) NOT NULL DEFAULT 0,
  cycle_started_at DATETIME(6) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_started (cycle_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE worker_queue (
  id INT NOT NULL AUTO_INCREMENT,
  nb_worker INT NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE worker_run (
  id INT NOT NULL AUTO_INCREMENT,
  id_worker_queue INT NOT NULL,
  date_created DATETIME NOT NULL,
  date_killed DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_run_dates (date_created, date_killed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE worker_execution (
  id INT NOT NULL AUTO_INCREMENT,
  status ENUM('OK','ERROR','TIMEOUT','SKIPPED','KILLED','STUCK') NOT NULL DEFAULT 'OK',
  date_started DATETIME NOT NULL,
  date_end DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_status (status, date_started)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE event_log (
  id INT NOT NULL AUTO_INCREMENT,
  date_start DATETIME(6) NOT NULL,
  date_end DATETIME(6) NULL,
  PRIMARY KEY (id),
  KEY idx_kpi_open_incidents (date_start, date_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;
    }

    private function fixtureData(): string
    {
        return <<<'SQL'
INSERT INTO aspirateur_attempt (result, ping_seconds, triggered_state_change, started_at) VALUES
  (1, 0.010000, 0, '2026-04-28 12:00:01.000000'),
  (0, 0.020000, 1, '2026-04-28 12:00:02.000000'),
  (2, 0.030000, 0, '2026-04-28 12:00:03.000000'),
  (1, 0.040000, 0, '2026-04-28 12:00:04.000000'),
  (0, 0.100000, 1, '2026-04-28 12:00:05.000000'),
  (1, 0.500000, 1, '2026-04-28 12:01:00.000000');

INSERT INTO daemon_run (over_max_delay, cycle_started_at) VALUES
  (1, '2026-04-28 12:00:10.000000'),
  (0, '2026-04-28 12:00:20.000000'),
  (1, '2026-04-28 12:01:00.000000');

INSERT INTO worker_queue (id, nb_worker) VALUES
  (1, 2),
  (2, 1);

INSERT INTO worker_run (id_worker_queue, date_created, date_killed) VALUES
  (1, '2026-04-28 12:00:00', '2026-04-28 12:01:00'),
  (2, '2026-04-28 12:00:30', '2026-04-28 12:00:50');

INSERT INTO worker_execution (status, date_started, date_end) VALUES
  ('OK', '2026-04-28 12:00:00', '2026-04-28 12:01:00'),
  ('STUCK', '2026-04-28 11:59:30', '2026-04-28 12:00:10'),
  ('OK', '2026-04-28 12:00:10', '2026-04-28 12:00:20'),
  ('STUCK', '2026-04-28 12:01:00', '2026-04-28 12:01:10');

INSERT INTO event_log (date_start, date_end) VALUES
  ('2026-04-28 11:00:00.000000', NULL),
  ('2026-04-28 12:00:10.000000', '2026-04-28 12:00:40.000000'),
  ('2026-04-28 12:00:55.000000', '2026-04-28 12:02:00.000000');
SQL;
    }

    private function migrationFixtureSchema(): string
    {
        return <<<'SQL'
CREATE TABLE daemon_main (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  date DATETIME NOT NULL,
  pid INT NOT NULL,
  refresh_time INT NOT NULL,
  max_delay INT NOT NULL,
  class VARCHAR(64) NOT NULL,
  method VARCHAR(64) NOT NULL,
  params VARCHAR(255) NOT NULL,
  debug INT NOT NULL,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE event_log (
  id INT NOT NULL AUTO_INCREMENT,
  date_start DATETIME(6) NOT NULL,
  date_end DATETIME(6) NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;
    }

    /**
     * @param list<string> $args
     * @return array{exitCode:int, stdout:string, stderr:string}
     */
    private function runMysql(string $sql, array $args = [], bool $allowFailure = false): array
    {
        $command = array_merge(['mysql', '--batch', '--raw', '--skip-column-names'], $args);
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
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * @return array<string, int>
     */
    private function parseMysqlKeyValueRows(string $stdout): array
    {
        $rows = [];
        foreach (array_filter(explode("\n", trim($stdout))) as $line) {
            $parts = explode("\t", $line);
            if (count($parts) !== 2) {
                continue;
            }

            $rows[$parts[0]] = (int)$parts[1];
        }

        return $rows;
    }
}
