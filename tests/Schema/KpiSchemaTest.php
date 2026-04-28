<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class KpiSchemaTest extends TestCase
{
    private const MIGRATION = __DIR__ . '/../../sql/incremental_v2/20260428_kpi_schema.sql';

    private string $sql;

    protected function setUp(): void
    {
        $sql = file_get_contents(self::MIGRATION);

        $this->assertIsString($sql);
        $this->sql = $sql;
    }

    public function testMigrationCreatesExpectedTables(): void
    {
        foreach ([
            'daemon_run',
            'aspirateur_attempt',
            'dot3_run',
            'dot3_run_group',
            'kpi_minute',
        ] as $table) {
            $this->assertStringContainsString("CREATE TABLE IF NOT EXISTS `{$table}`", $this->sql);
        }
    }

    public function testPartitionedTablesUsePartitionCompatiblePrimaryKeys(): void
    {
        $daemonRun = $this->tableDefinition('daemon_run');
        $this->assertStringContainsString('PRIMARY KEY (`id`, `cycle_started_at`)', $daemonRun);
        $this->assertStringContainsString('PARTITION BY RANGE (TO_DAYS(`cycle_started_at`))', $daemonRun);
        $this->assertStringContainsString('PARTITION `pmax` VALUES LESS THAN MAXVALUE', $daemonRun);

        $aspirateurAttempt = $this->tableDefinition('aspirateur_attempt');
        $this->assertStringContainsString('PRIMARY KEY (`id`, `started_at`)', $aspirateurAttempt);
        $this->assertStringContainsString('PARTITION BY RANGE (TO_DAYS(`started_at`))', $aspirateurAttempt);
        $this->assertStringContainsString('PARTITION `pmax` VALUES LESS THAN MAXVALUE', $aspirateurAttempt);
    }

    public function testWorkerExecutionAlterIsIdempotentAndOnlineFriendly(): void
    {
        foreach ([
            '`status`',
            '`error_class`',
            '`error_message`',
            '`exit_code`',
            '`peak_rss_kb`',
            '`db_queries_count`',
            '`db_queries_time_ms`',
            '`attempt_n`',
        ] as $column) {
            $this->assertStringContainsString("ADD COLUMN IF NOT EXISTS {$column}", $this->sql);
        }

        $this->assertStringNotContainsString(' AFTER ', $this->sql);
        $this->assertStringContainsString('ALGORITHM=INSTANT', $this->sql);
        $this->assertStringContainsString('ADD INDEX IF NOT EXISTS `idx_status` (`status`, `date_started`)', $this->sql);
        $this->assertStringContainsString('ALGORITHM=INPLACE', $this->sql);
        $this->assertStringContainsString('LOCK=NONE', $this->sql);
    }

    public function testAspirateurAttemptHasOperationalLookupIndexes(): void
    {
        foreach ([
            'KEY `idx_worker_execution` (`id_worker_execution`, `started_at`)',
            'KEY `idx_mysql` (`id_mysql_server`, `started_at`)',
            'KEY `idx_proxy` (`id_proxysql_server`, `started_at`)',
            'KEY `idx_maxscale` (`id_maxscale_server`, `started_at`)',
            'KEY `idx_mysqlrouter` (`id_mysqlrouter_server`, `started_at`)',
            'KEY `idx_kind_started` (`kind`, `started_at`)',
            'KEY `idx_state_change` (`triggered_state_change`, `started_at`)',
            'KEY `idx_result_started` (`result`, `started_at`)',
        ] as $index) {
            $this->assertStringContainsString($index, $this->sql);
        }
    }

    public function testDot3RunGroupKeepsItsCascadeForeignKey(): void
    {
        $dot3RunGroup = $this->tableDefinition('dot3_run_group');

        $this->assertStringContainsString('PRIMARY KEY (`id_dot3_run`, `group_kind`)', $dot3RunGroup);
        $this->assertStringContainsString(
            'CONSTRAINT `fk_dot3_group_run` FOREIGN KEY (`id_dot3_run`) REFERENCES `dot3_run` (`id`) ON DELETE CASCADE',
            $dot3RunGroup
        );
    }

    public function testKpiMinuteHasReportingIndexes(): void
    {
        $kpiMinute = $this->tableDefinition('kpi_minute');

        foreach ([
            'KEY `idx_worker_busy` (`worker_busy_pct`, `bucket_start`)',
            'KEY `idx_daemon_late` (`daemon_late_count`, `bucket_start`)',
            'KEY `idx_state_transitions` (`state_transitions_total`, `bucket_start`)',
        ] as $index) {
            $this->assertStringContainsString($index, $kpiMinute);
        }
    }

    public function testMigrationCanBeAppliedTwiceWhenMysqlIsAvailable(): void
    {
        $probe = $this->runMysql('SELECT 1;', [], true);
        if ($probe['exitCode'] !== 0 || trim($probe['stdout']) !== '1') {
            self::markTestSkipped('Local mysql client is not available or cannot connect.');
        }

        $database = 'pmacontrol_kpi_schema_test_' . getmypid() . '_' . bin2hex(random_bytes(3));
        $databaseIdentifier = $this->quoteIdentifier($database);

        $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier}; CREATE DATABASE {$databaseIdentifier} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

        try {
            $this->runMysql($this->baseWorkerExecutionSql(), ["--database={$database}"]);
            $this->runMysql($this->sql . "\n" . $this->sql, ["--database={$database}"]);

            $checks = $this->runMysql(<<<SQL
SELECT 'tables', COUNT(*)
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN ('daemon_run', 'aspirateur_attempt', 'dot3_run', 'dot3_run_group', 'kpi_minute');

SELECT 'worker_columns', COUNT(*)
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'worker_execution'
  AND column_name IN ('status', 'error_class', 'error_message', 'exit_code', 'peak_rss_kb', 'db_queries_count', 'db_queries_time_ms', 'attempt_n');

SELECT 'worker_status_index', COUNT(*)
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = 'worker_execution'
  AND index_name = 'idx_status';

SELECT 'dot3_fk', COUNT(*)
FROM information_schema.referential_constraints
WHERE constraint_schema = DATABASE()
  AND constraint_name = 'fk_dot3_group_run';

SELECT 'partitions', COUNT(*)
FROM information_schema.partitions
WHERE table_schema = DATABASE()
  AND table_name IN ('daemon_run', 'aspirateur_attempt')
  AND partition_name IS NOT NULL;
SQL, ["--database={$database}"]);

            $rows = $this->parseMysqlKeyValueRows($checks['stdout']);

            $this->assertSame(5, $rows['tables'] ?? null);
            $this->assertSame(8, $rows['worker_columns'] ?? null);
            $this->assertSame(2, $rows['worker_status_index'] ?? null);
            $this->assertSame(1, $rows['dot3_fk'] ?? null);
            $this->assertGreaterThanOrEqual(2, $rows['partitions'] ?? 0);
        } finally {
            $this->runMysql("DROP DATABASE IF EXISTS {$databaseIdentifier};", [], true);
        }
    }

    private function tableDefinition(string $table): string
    {
        $pattern = sprintf(
            '/CREATE TABLE IF NOT EXISTS `%s`.*?(?=\\n\\nCREATE TABLE IF NOT EXISTS|\\n\\nALTER TABLE|\\z)/s',
            preg_quote($table, '/')
        );

        $this->assertMatchesRegularExpression($pattern, $this->sql);
        preg_match($pattern, $this->sql, $matches);

        return $matches[0];
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

    private function baseWorkerExecutionSql(): string
    {
        return <<<'SQL'
CREATE TABLE `worker_execution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_worker_run` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `date_started` datetime NOT NULL DEFAULT current_timestamp(),
  `date_end` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL COMMENT 'in ms',
  PRIMARY KEY (`id`),
  KEY `id_worker_run` (`id_worker_run`),
  KEY `date_started` (`date_started`,`id_mysql_server`),
  KEY `idx_mysql_server_date_started_run` (`id_mysql_server`,`date_started`,`id_worker_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;
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

            $rows[$parts[0]] = (int) $parts[1];
        }

        return $rows;
    }
}
