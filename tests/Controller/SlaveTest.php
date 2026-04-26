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

    public function testBuildBinlogAnalysisLagDataDeduplicatesTimestampAndPrefersSourceMetric(): void
    {
        $method = new ReflectionMethod(Slave::class, 'buildBinlogAnalysisLagData');
        $method->setAccessible(true);

        Extraction::$variable[10]['name'] = 'seconds_behind_master';
        Extraction::$variable[11]['name'] = 'seconds_behind_source';

        $lagData = $method->invoke($this->slave, [
            [
                'id_mysql_server' => 1,
                'connection_name' => 'channel_a',
                'id_ts_variable' => 10,
                'date' => '2026-04-15 10:00:00',
                'value' => '12',
            ],
            [
                'id_mysql_server' => 1,
                'connection_name' => 'channel_a',
                'id_ts_variable' => 11,
                'date' => '2026-04-15 10:00:00',
                'value' => '3',
            ],
            [
                'id_mysql_server' => 1,
                'connection_name' => 'channel_a',
                'id_ts_variable' => 10,
                'date' => '2026-04-15 10:00:01',
                'value' => '9',
            ],
        ]);

        $this->assertSame(
            [
                ['ts' => '2026-04-15 10:00:00', 'lag' => 3],
                ['ts' => '2026-04-15 10:00:01', 'lag' => 9],
            ],
            $lagData
        );
    }

    public function testSanitizeConnectionNameRemovesInjectionChars(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');

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

    public function testBuildInFlightBinlogAnalysisCriteriaMatchesDuplicateWindow(): void
    {
        $criteria = Slave::buildInFlightBinlogAnalysisCriteria(
            12,
            34,
            'production_fr',
            '2026-04-15 10:00:00',
            '2026-04-15 11:00:00'
        );

        $this->assertSame(12, $criteria['id_mysql_server']);
        $this->assertSame(34, $criteria['id_mysql_server__master']);
        $this->assertSame('production_fr', $criteria['connection_name']);
        $this->assertSame('2026-04-15 10:00:00', $criteria['time_start']);
        $this->assertSame('2026-04-15 11:00:00', $criteria['time_end']);
        $this->assertSame(['pending', 'running'], $criteria['statuses']);
    }

    public function testBuildBinlogAnalysisLockNameIsStableAndBounded(): void
    {
        $criteria = Slave::buildInFlightBinlogAnalysisCriteria(
            12,
            34,
            'production_fr',
            '2026-04-15 10:00:00',
            '2026-04-15 11:00:00'
        );
        $sameCriteria = Slave::buildInFlightBinlogAnalysisCriteria(
            12,
            34,
            'production_fr',
            '2026-04-15 10:00:00',
            '2026-04-15 11:00:00'
        );
        $differentWindow = Slave::buildInFlightBinlogAnalysisCriteria(
            12,
            34,
            'production_fr',
            '2026-04-15 10:30:00',
            '2026-04-15 11:00:00'
        );

        $lockName = Slave::buildBinlogAnalysisLockName($criteria);

        $this->assertSame($lockName, Slave::buildBinlogAnalysisLockName($sameCriteria));
        $this->assertNotSame($lockName, Slave::buildBinlogAnalysisLockName($differentWindow));
        $this->assertStringStartsWith('pmacontrol:ba:', $lockName);
        $this->assertLessThanOrEqual(64, strlen($lockName));
    }

    public function testNormalizeReplicationLagGraphRowsKeepsMultipleDays(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');

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

        $this->assertSame([], $method->invoke($this->slave, []));
    }

    public function testNormalizeReplicationLagGraphRowsSingleRowPassthrough(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');

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

        $this->assertSame([], $method->invoke($this->slave, []));
    }

    public function testNormalizeReplicationLagDisplayRowsOnlyMasterPresent(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');

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

        $rows = [
            1 => ['' => ['seconds_behind_master' => '99', 'seconds_behind_source' => '2']],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        $this->assertSame('2', $normalized[1]['']['seconds_behind_master']);
    }

    public function testNormalizeReplicationLagDisplayRowsMultipleConnections(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');

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

        // Unicode chars should be stripped
        // é is 2 bytes (0xC3 0xA9), regex strips both, keeps 'connction'
        $this->assertSame('connction', $method->invoke(null, "connéction"));
        $this->assertSame('', $method->invoke(null, "日本語"));
    }

    public function testSanitizeConnectionNamePreservesLongValidNames(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');

        $long = str_repeat('a', 200);
        $this->assertSame($long, $method->invoke(null, $long));
    }

    public function testSanitizeConnectionNameSpacesOnly(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');

        $this->assertSame('', $method->invoke(null, '   '));
    }

    public function testSanitizeConnectionNameBacktickAndBackslash(): void
    {
        $method = new ReflectionMethod(Slave::class, 'sanitizeConnectionName');

        $this->assertSame('conn', $method->invoke(null, '`conn`'));
        $this->assertSame('ab', $method->invoke(null, 'a\\b'));
    }

    public function testNormalizeReplicationLagGraphRowsMixedMetricsAcrossDays(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagGraphRows');

        Extraction::$variable[10]['name'] = 'seconds_behind_master';
        Extraction::$variable[11]['name'] = 'seconds_behind_source';

        $rows = [
            // Day 1: only master metric available
            ['id_mysql_server' => 1, 'connection_name' => 'ch1', 'id_ts_variable' => 10, 'day' => '2026-04-10', 'graph' => '{x:1,y:5}'],
            // Day 2: both metrics — source should win
            ['id_mysql_server' => 1, 'connection_name' => 'ch1', 'id_ts_variable' => 10, 'day' => '2026-04-11', 'graph' => '{x:2,y:99}'],
            ['id_mysql_server' => 1, 'connection_name' => 'ch1', 'id_ts_variable' => 11, 'day' => '2026-04-11', 'graph' => '{x:2,y:3}'],
        ];

        $normalized = $method->invoke($this->slave, $rows);

        $this->assertCount(2, $normalized);
        // Day 1 keeps master (only option)
        $day1 = array_values(array_filter($normalized, fn($r) => $r['day'] === '2026-04-10'));
        $this->assertSame(10, $day1[0]['id_ts_variable']);
        $this->assertSame('{x:1,y:5}', $day1[0]['graph']);
        // Day 2 picks source over master
        $day2 = array_values(array_filter($normalized, fn($r) => $r['day'] === '2026-04-11'));
        $this->assertSame(11, $day2[0]['id_ts_variable']);
        $this->assertSame('{x:2,y:3}', $day2[0]['graph']);
    }

    public function testNormalizeReplicationLagDisplayRowsWhitespaceLagIgnored(): void
    {
        $method = new ReflectionMethod(Slave::class, 'normalizeReplicationLagDisplayRows');

        $rows = [
            1 => ['' => ['seconds_behind_master' => '7', 'seconds_behind_source' => '  ']],
        ];

        $normalized = $method->invoke($this->slave, $rows);
        // Whitespace source should not override real master value
        $this->assertSame('7', $normalized[1]['']['seconds_behind_master']);
    }

    // ── buildReplicationCmd ──
    //
    // Signature : (verb, isMariaDB, useReplica, connectionName='')
    // useReplica est ignoré quand isMariaDB=true (MariaDB n'a jamais REPLICA).
    // Sur MySQL, useReplica vaut true ssi version >= 8.0.22 (gh#144).

    public function testBuildReplicationCmdMariaDBNoChannel(): void
    {
        $method = new ReflectionMethod(Slave::class, 'buildReplicationCmd');

        // useReplica ignoré sur MariaDB
        $this->assertSame('STOP SLAVE', $method->invoke(null, 'STOP', true, false, ''));
        $this->assertSame('START SLAVE', $method->invoke(null, 'START', true, false, ''));
        $this->assertSame('STOP SLAVE', $method->invoke(null, 'STOP', true, true, ''));
    }

    public function testBuildReplicationCmdMariaDBWithChannel(): void
    {
        $method = new ReflectionMethod(Slave::class, 'buildReplicationCmd');

        $this->assertSame("STOP SLAVE 'production_fr'", $method->invoke(null, 'STOP', true, false, 'production_fr'));
        $this->assertSame("START SLAVE 'chan-1'", $method->invoke(null, 'START', true, false, 'chan-1'));
    }

    public function testBuildReplicationCmdMySQLModernNoChannel(): void
    {
        // MySQL >= 8.0.22 : REPLICA
        $method = new ReflectionMethod(Slave::class, 'buildReplicationCmd');

        $this->assertSame('STOP REPLICA', $method->invoke(null, 'STOP', false, true, ''));
        $this->assertSame('START REPLICA', $method->invoke(null, 'START', false, true, ''));
    }

    public function testBuildReplicationCmdMySQLModernWithChannel(): void
    {
        $method = new ReflectionMethod(Slave::class, 'buildReplicationCmd');

        $this->assertSame("STOP REPLICA FOR CHANNEL 'pmacontrol'", $method->invoke(null, 'STOP', false, true, 'pmacontrol'));
        $this->assertSame("START REPLICA FOR CHANNEL 'chan.test'", $method->invoke(null, 'START', false, true, 'chan.test'));
    }

    public function testBuildReplicationCmdMySQLLegacyKeepsSlaveSyntax(): void
    {
        // gh#144 — MySQL 5.7 et MySQL 8.0.0-8.0.21 ne connaissent que SLAVE
        $method = new ReflectionMethod(Slave::class, 'buildReplicationCmd');

        $this->assertSame('STOP SLAVE', $method->invoke(null, 'STOP', false, false, ''));
        $this->assertSame('START SLAVE', $method->invoke(null, 'START', false, false, ''));
        $this->assertSame("STOP SLAVE FOR CHANNEL 'legacy_57'", $method->invoke(null, 'STOP', false, false, 'legacy_57'));
        $this->assertSame("START SLAVE FOR CHANNEL 'legacy_57'", $method->invoke(null, 'START', false, false, 'legacy_57'));
    }

    // ── usesReplicaSyntax ──

    public function testUsesReplicaSyntaxMariaDBAlwaysFalse(): void
    {
        $method = new ReflectionMethod(Slave::class, 'usesReplicaSyntax');

        $this->assertFalse($method->invoke(null, $this->fakeDb('mariadb', '10.11.6')));
        $this->assertFalse($method->invoke(null, $this->fakeDb('MariaDB', '11.4.2')));
    }

    public function testUsesReplicaSyntaxMySQLPre8022False(): void
    {
        // gh#144 — MySQL 5.7 et 8.0.0-8.0.21 doivent rester en SLAVE
        $method = new ReflectionMethod(Slave::class, 'usesReplicaSyntax');

        $this->assertFalse($method->invoke(null, $this->fakeDb('MySQL', '5.7.44')));
        $this->assertFalse($method->invoke(null, $this->fakeDb('mysql', '8.0.0')));
        $this->assertFalse($method->invoke(null, $this->fakeDb('mysql', '8.0.21')));
    }

    public function testUsesReplicaSyntaxMySQL8022PlusTrue(): void
    {
        $method = new ReflectionMethod(Slave::class, 'usesReplicaSyntax');

        $this->assertTrue($method->invoke(null, $this->fakeDb('mysql', '8.0.22')));
        $this->assertTrue($method->invoke(null, $this->fakeDb('mysql', '8.0.36')));
        $this->assertTrue($method->invoke(null, $this->fakeDb('MySQL', '8.4.0')));
    }

    private function fakeDb(string $serverType, string $version): object
    {
        return new class($serverType, $version) {
            public function __construct(private string $serverType, private string $version) {}
            public function getServerType(): string { return $this->serverType; }
            public function getVersion(): string { return $this->version; }
        };
    }

    // ── getReplicationLagVariables ──

    public function testGetReplicationLagVariablesReturnsBothMetrics(): void
    {
        $method = new ReflectionMethod(Slave::class, 'getReplicationLagVariables');

        $vars = $method->invoke($this->slave);

        $this->assertContains('slave::seconds_behind_master', $vars);
        $this->assertContains('slave::seconds_behind_source', $vars);
        $this->assertCount(2, $vars);
    }
}
