<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CveSourceFeedsMigrationTest extends TestCase
{
    private string $migration;

    protected function setUp(): void
    {
        $path = __DIR__ . '/../../sql/incremental_v2/20260506_cve_source_feeds.sql';
        $this->assertFileExists($path);

        $this->migration = (string) file_get_contents($path);
    }

    public function testMigrationCreatesOneRawHistoryTablePerCveSourceFormat(): void
    {
        $expectedTables = [
            'cve_source_nvd',
            'cve_source_osv',
            'cve_source_ghsa',
            'cve_source_cisa_kev',
            'cve_source_oracle_cpu',
            'cve_source_mariadb_security',
            'cve_source_percona_advisory',
            'cve_source_component_ghsa',
            'cve_source_aws_security_bulletin',
        ];

        preg_match_all('/CREATE TABLE IF NOT EXISTS `(?<table>cve_source_[a-z0-9_]+)`/', $this->migration, $matches);

        $this->assertSame($expectedTables, $matches['table']);
    }

    public function testEachRawSourceTableKeepsPayloadHistory(): void
    {
        foreach ($this->sourceTables() as $table) {
            $block = $this->tableBlock($table);

            $this->assertStringContainsString('`id_cve_feed_run` bigint(20) unsigned DEFAULT NULL', $block);
            $this->assertStringContainsString('`raw_json` longtext NOT NULL', $block);
            $this->assertStringContainsString('`payload_hash` char(64) NOT NULL', $block);
            $this->assertStringContainsString('`is_current` tinyint(1) NOT NULL DEFAULT 1', $block);
            $this->assertStringContainsString('`date_first_seen` datetime NOT NULL DEFAULT current_timestamp()', $block);
            $this->assertStringContainsString('`date_last_seen` datetime NOT NULL DEFAULT current_timestamp()', $block);
            $this->assertMatchesRegularExpression('/UNIQUE KEY `uniq_' . preg_quote($table, '/') . '_history`/', $block);
        }
    }

    public function testSourceSpecificTablesHaveSourceSpecificColumns(): void
    {
        $this->assertStringContainsString('`cpe_match_json` longtext NULL', $this->tableBlock('cve_source_nvd'));
        $this->assertStringContainsString('`affected_json` longtext NULL', $this->tableBlock('cve_source_osv'));
        $this->assertStringContainsString('`vulnerabilities_json` longtext NULL', $this->tableBlock('cve_source_ghsa'));
        $this->assertStringContainsString('`known_ransomware_campaign_use` varchar(32) DEFAULT NULL', $this->tableBlock('cve_source_cisa_kev'));
        $this->assertStringContainsString('`cpu_cycle` varchar(64) DEFAULT NULL', $this->tableBlock('cve_source_oracle_cpu'));
        $this->assertStringContainsString('`branch` varchar(64) DEFAULT NULL', $this->tableBlock('cve_source_mariadb_security'));
        $this->assertStringContainsString('`advisory_id` varchar(128) DEFAULT NULL', $this->tableBlock('cve_source_percona_advisory'));
        $this->assertStringContainsString("`component` enum('proxysql','maxscale','haproxy','vitess','other') NOT NULL", $this->tableBlock('cve_source_component_ghsa'));
        $this->assertStringContainsString('`engine` varchar(128) DEFAULT NULL', $this->tableBlock('cve_source_aws_security_bulletin'));
    }

    public function testMigrationSeedsTheNineConfiguredCveSources(): void
    {
        $expectedSources = [
            'nvd',
            'osv',
            'ghsa',
            'cisa_kev',
            'oracle_cpu',
            'mariadb_security',
            'percona_advisory',
            'component_ghsa',
            'aws_security_bulletin',
        ];

        foreach ($expectedSources as $source) {
            $this->assertStringContainsString("('" . $source . "'", $this->migration);
        }

        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $this->migration);
    }

    public function testMigrationSeedsProductScopeIncludingMysql41HistoryAndAwsCloudProducts(): void
    {
        $this->assertStringContainsString('Historical coverage must include MySQL 4.1 and newer.', $this->migration);
        $this->assertStringContainsString("'aurora_mysql', 'Amazon Aurora MySQL'", $this->migration);
        $this->assertStringContainsString("'rds_mysql', 'Amazon RDS for MySQL'", $this->migration);
        $this->assertStringContainsString("'component_ghsa', 'proxysql', 'ProxySQL'", $this->migration);
    }

    /**
     * @return array<int,string>
     */
    private function sourceTables(): array
    {
        return [
            'cve_source_nvd',
            'cve_source_osv',
            'cve_source_ghsa',
            'cve_source_cisa_kev',
            'cve_source_oracle_cpu',
            'cve_source_mariadb_security',
            'cve_source_percona_advisory',
            'cve_source_component_ghsa',
            'cve_source_aws_security_bulletin',
        ];
    }

    private function tableBlock(string $table): string
    {
        $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \\((.*?)\\) ENGINE=/s';

        $this->assertMatchesRegularExpression($pattern, $this->migration);
        preg_match($pattern, $this->migration, $matches);

        return $matches[1] ?? '';
    }
}
