<?php

declare(strict_types=1);

use App\Controller\Slave;
use App\Library\Extraction;
use PHPUnit\Framework\TestCase;

final class SlaveTest extends TestCase
{
    private Slave $slave;

    protected function setUp(): void
    {
        $this->slave = new Slave('Controller', 'View', []);
    }

    public function testExtractBinlogInfoParsesValidLine(): void
    {
        $line = 'mariadb-bin.009564 2735165 /srv/backup/customer_db_2026-03-01_12h30m.full.sql.gz';

        $info = $this->slave->extractBinlogInfo($line);

        $this->assertSame('mariadb-bin.009564', $info['binlog_file']);
        $this->assertSame('2735165', $info['binlog_pos']);
        $this->assertSame('customer_db', $info['db_name']);
    }

    public function testExtractBinlogInfoReturnsEmptyArrayForInvalidLine(): void
    {
        $this->assertSame([], $this->slave->extractBinlogInfo('invalid line'));
    }

    public function testGroupBinlogInfoByPositionAggregatesDatabaseNames(): void
    {
        $grouped = $this->slave->groupBinlogInfoByPosition([
            ['binlog_file' => 'bin.1', 'binlog_pos' => '100', 'db_name' => 'db1'],
            ['binlog_file' => 'bin.1', 'binlog_pos' => '100', 'db_name' => 'db2'],
            ['binlog_file' => 'bin.2', 'binlog_pos' => '200', 'db_name' => 'db3'],
        ]);

        $this->assertCount(2, $grouped);
        $this->assertSame('db1, db2', $grouped[0]['db_names']);
        $this->assertSame('bin.2', $grouped[1]['binlog_file']);
    }

    public function testProcessBinlogFileReadsAndGroupsEntries(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'binlog-');
        file_put_contents(
            $file,
            implode("\n", [
                'mariadb-bin.009564 2735165 /srv/backup/customer_db_2026-03-01_12h30m.full.sql.gz',
                'mariadb-bin.009564 2735165 /srv/backup/reporting_2026-03-01_12h30m.full.sql.gz',
            ])
        );

        try {
            $grouped = $this->slave->processBinlogFile([$file]);

            $this->assertCount(1, $grouped);
            $this->assertSame('customer_db, reporting', $grouped[0]['db_names']);
        } finally {
            @unlink($file);
        }
    }

    public function testGenerateSecurePasswordIncludesAllCharacterClasses(): void
    {
        $password = $this->slave->generateSecurePassword(20);

        $this->assertSame(20, strlen($password));
        $this->assertMatchesRegularExpression('/[a-z]/', $password);
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
        $this->assertMatchesRegularExpression('/[!@#$%^&*()_+\\-=\\[\\]{}|;:,.<>?]/', $password);
        $this->assertStringNotContainsString("'", $password);
        $this->assertStringNotContainsString('"', $password);
    }

    public function testNormalizeReplicationLagDisplayRowsPrefersSourceLag(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');
        $method->setAccessible(true);

        $rows = [
            1 => [
                '' => [
                    'seconds_behind_master' => '11',
                    'seconds_behind_source' => '3',
                ],
            ],
        ];

        $normalized = $method->invoke($this->slave, $rows);

        $this->assertSame('3', $normalized[1]['']['seconds_behind_master']);
    }

    public function testNormalizeReplicationLagGraphRowsPrefersSourceMetric(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';
        Extraction::$variable[11]['name'] = 'seconds_behind_source';

        $rows = [
            [
                'id_mysql_server' => 1,
                'connection_name' => '',
                'id_ts_variable' => 10,
                'graph' => '{x:1,y:11}',
            ],
            [
                'id_mysql_server' => 1,
                'connection_name' => '',
                'id_ts_variable' => 11,
                'graph' => '{x:1,y:3}',
            ],
        ];

        $normalized = $method->invoke($this->slave, $rows);

        $this->assertCount(1, $normalized);
        $this->assertSame(11, $normalized[0]['id_ts_variable']);
    }

    public function testSanitizeConnectionNameRemovesInjectionChars(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');
        $method->setAccessible(true);

        $this->assertSame('production_fr', $method->invoke(null, 'production_fr'));
        $this->assertSame('slave-01', $method->invoke(null, 'slave-01'));
        $this->assertSame('conn.test', $method->invoke(null, 'conn.test'));
        $this->assertSame('', $method->invoke(null, ''));
        // SQL injection attempts must be stripped
        $this->assertSame('DROPTABLE--', $method->invoke(null, "'; DROP TABLE--"));
        $this->assertSame('testOR11', $method->invoke(null, "test' OR '1'='1"));
        $this->assertSame('nascriptme', $method->invoke(null, 'na<script>me'));
        $this->assertSame('ab', $method->invoke(null, "a\x00b"));
    }

    public function testNormalizeReplicationLagGraphRowsKeepsMultipleDays(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';

        $rows = [
            [
                'id_mysql_server' => 228,
                'connection_name' => 'production_fr',
                'id_ts_variable' => 10,
                'day' => '2026-04-10',
                'graph' => '{x:1,y:0}',
            ],
            [
                'id_mysql_server' => 228,
                'connection_name' => 'production_fr',
                'id_ts_variable' => 10,
                'day' => '2026-04-11',
                'graph' => '{x:2,y:1}',
            ],
            [
                'id_mysql_server' => 228,
                'connection_name' => 'production_uk',
                'id_ts_variable' => 10,
                'day' => '2026-04-10',
                'graph' => '{x:3,y:2}',
            ],
        ];

        $normalized = $method->invoke($this->slave, $rows);

        // Must keep 3 distinct rows: 2 days for production_fr + 1 day for production_uk
        $this->assertCount(3, $normalized);
        $days = array_column($normalized, 'day');
        $this->assertContains('2026-04-10', $days);
        $this->assertContains('2026-04-11', $days);
    }

    // ── normalizeReplicationLagGraphRows edge cases ──

    public function testNormalizeReplicationLagGraphRowsReturnsEmptyForEmptyInput(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        $this->assertSame([], $method->invoke($this->slave, []));
    }

    public function testNormalizeReplicationLagGraphRowsSingleRowPassthrough(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';

        $rows = [
            [
                'id_mysql_server' => 1,
                'connection_name' => 'chan1',
                'id_ts_variable' => 10,
                'day' => '2026-04-12',
                'graph' => '{x:1,y:0}',
            ],
        ];

        $normalized = $method->invoke($this->slave, $rows);

        $this->assertCount(1, $normalized);
        $this->assertSame('chan1', $normalized[0]['connection_name']);
    }

    public function testNormalizeReplicationLagGraphRowsWithoutDayFieldForSparkline(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';
        Extraction::$variable[11]['name'] = 'seconds_behind_source';

        // Sparkline mode: no 'day' key. Two metrics for same channel should dedup to 1.
        $rows = [
            ['id_mysql_server' => 1, 'connection_name' => '', 'id_ts_variable' => 10, 'graph' => '{x:1,y:5}'],
            ['id_mysql_server' => 1, 'connection_name' => '', 'id_ts_variable' => 11, 'graph' => '{x:1,y:3}'],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        $this->assertCount(1, $normalized);
        // seconds_behind_source (id 11) should win
        $this->assertSame(11, $normalized[0]['id_ts_variable']);
    }

    public function testNormalizeReplicationLagGraphRowsTwoChannelsSameDaySameServer(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';

        $rows = [
            ['id_mysql_server' => 228, 'connection_name' => 'production_fr', 'id_ts_variable' => 10, 'day' => '2026-04-10', 'graph' => '{x:1,y:0}'],
            ['id_mysql_server' => 228, 'connection_name' => 'production_uk', 'id_ts_variable' => 10, 'day' => '2026-04-10', 'graph' => '{x:2,y:1}'],
        ];

        $normalized = $method->invoke($this->slave, $rows);

        // Two distinct channels on same day must both survive
        $this->assertCount(2, $normalized);
        $channels = array_column($normalized, 'connection_name');
        $this->assertContains('production_fr', $channels);
        $this->assertContains('production_uk', $channels);
    }

    public function testNormalizeReplicationLagGraphRowsEmptyConnectionName(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';

        // Default (unnamed) connection — connection_name is empty string
        $rows = [
            ['id_mysql_server' => 5, 'connection_name' => '', 'id_ts_variable' => 10, 'day' => '2026-04-10', 'graph' => '{x:1,y:0}'],
            ['id_mysql_server' => 5, 'connection_name' => '', 'id_ts_variable' => 10, 'day' => '2026-04-11', 'graph' => '{x:2,y:1}'],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        $this->assertCount(2, $normalized);
    }

    // ── normalizeReplicationLagDisplayRows edge cases ──

    public function testNormalizeReplicationLagDisplayRowsEmptyInput(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');
        $method->setAccessible(true);

        $this->assertSame([], $method->invoke($this->slave, []));
    }

    public function testNormalizeReplicationLagDisplayRowsOnlyMasterPresent(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');
        $method->setAccessible(true);

        $rows = [
            1 => ['' => ['seconds_behind_master' => '7']],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        // Master value should be kept as-is when source is absent
        $this->assertSame('7', $normalized[1]['']['seconds_behind_master']);
    }

    public function testNormalizeReplicationLagDisplayRowsSourceOverridesMaster(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');
        $method->setAccessible(true);

        $rows = [
            1 => ['' => ['seconds_behind_master' => '99', 'seconds_behind_source' => '2']],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        $this->assertSame('2', $normalized[1]['']['seconds_behind_master']);
    }

    public function testNormalizeReplicationLagDisplayRowsMultipleConnections(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');
        $method->setAccessible(true);

        $rows = [
            228 => [
                'production_fr' => ['seconds_behind_master' => '10', 'seconds_behind_source' => '3'],
                'production_uk' => ['seconds_behind_master' => '5'],
            ],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        $this->assertSame('3', $normalized[228]['production_fr']['seconds_behind_master']);
        $this->assertSame('5', $normalized[228]['production_uk']['seconds_behind_master']);
    }

    public function testNormalizeReplicationLagDisplayRowsBothAbsent(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');
        $method->setAccessible(true);

        $rows = [
            1 => ['' => ['some_other_var' => 'yes']],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        // No seconds_behind_master key should be created when both are absent
        $this->assertSame('', $normalized[1]['']['seconds_behind_master']);
    }

    // ── sanitizeConnectionName edge cases ──

    public function testSanitizeConnectionNameUnicode(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');
        $method->setAccessible(true);

        // Unicode chars should be stripped
        // é is 2 bytes (0xC3 0xA9), regex strips both, keeps 'connction'
        $this->assertSame('connction', $method->invoke(null, "connéction"));
        $this->assertSame('', $method->invoke(null, "日本語"));
    }

    public function testSanitizeConnectionNamePreservesLongValidNames(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');
        $method->setAccessible(true);

        $long = str_repeat('a', 200);
        $this->assertSame($long, $method->invoke(null, $long));
    }

    public function testSanitizeConnectionNameSpacesOnly(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');
        $method->setAccessible(true);

        $this->assertSame('', $method->invoke(null, '   '));
    }

    public function testSanitizeConnectionNameBacktickAndBackslash(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');
        $method->setAccessible(true);

        $this->assertSame('conn', $method->invoke(null, '`conn`'));
        $this->assertSame('ab', $method->invoke(null, 'a\\b'));
    }

    // ── getReplicationLagVariables ──

    public function testGetReplicationLagVariablesReturnsBothMetrics(): void
    {
        $method = new ReflectionMethod(Slave::class, 'getReplicationLagVariables');
        $method->setAccessible(true);

        $vars = $method->invoke($this->slave);

        $this->assertContains('slave::seconds_behind_master', $vars);
        $this->assertContains('slave::seconds_behind_source', $vars);
        $this->assertCount(2, $vars);
    }
}
