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
}
