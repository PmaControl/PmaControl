<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CveCatalogPluginMigrationTest extends TestCase
{
    private string $migration;

    protected function setUp(): void
    {
        $path = __DIR__ . '/../../sql/incremental_v2/20260506_cve_catalog_plugin.sql';
        $this->assertFileExists($path);

        $this->migration = (string)file_get_contents($path);
    }

    public function testMigrationCreatesNormalizedCveCatalogTables(): void
    {
        foreach ([
            'cve_product',
            'cve_catalog',
            'cve_product_affected_version',
            'cve_server_cache',
        ] as $table) {
            self::assertStringContainsString("CREATE TABLE IF NOT EXISTS `{$table}`", $this->migration);
        }
    }

    public function testAffectedVersionTableStoresProductTagsAndVersionRanges(): void
    {
        $block = $this->tableBlock('cve_product_affected_version');

        self::assertStringContainsString('`product_code` varchar(64) NOT NULL', $block);
        self::assertStringContainsString('`version_start_including` varchar(64) DEFAULT NULL', $block);
        self::assertStringContainsString('`version_end_excluding` varchar(64) DEFAULT NULL', $block);
        self::assertStringContainsString('`fixed_version` text NULL', $block);
        self::assertStringContainsString('`match_confidence` enum', $block);
    }

    public function testMigrationSeedsProductsAndPluginMenuEntry(): void
    {
        foreach ([
            "('mysql', 'MySQL Server'",
            "('mariadb', 'MariaDB Server'",
            "('percona', 'Percona Server'",
            "('xtrabackup', 'Percona XtraBackup'",
            "('pmm', 'Percona Monitoring and Management'",
            "('mariadb_backup', 'MariaDB Backup'",
            "('proxysql', 'ProxySQL'",
            "('aurora_mysql', 'Amazon Aurora MySQL'",
        ] as $productSeed) {
            self::assertStringContainsString($productSeed, $this->migration);
        }

        self::assertStringContainsString("'cve-inventory'", $this->migration);
        self::assertStringContainsString("'CVE Inventory'", $this->migration);
        self::assertStringContainsString("'{LINK}cve/index'", $this->migration);
        self::assertStringContainsString("'cve'", $this->migration);
        self::assertStringContainsString("'index'", $this->migration);
    }

    public function testComponentTagMigrationsSeedProductsAndSourceScopes(): void
    {
        $productPath = __DIR__ . '/../../sql/incremental_v2/20260506_cve_component_tags.sql';
        $sourcePath = __DIR__ . '/../../sql/incremental_v2/20260506_zz_cve_component_feed_source_products.sql';
        self::assertFileExists($productPath);
        self::assertFileExists($sourcePath);

        $productMigration = (string)file_get_contents($productPath);
        $sourceMigration = (string)file_get_contents($sourcePath);

        foreach ([
            "('xtrabackup', 'Percona XtraBackup'",
            "('pmm', 'Percona Monitoring and Management'",
            "('mariadb_backup', 'MariaDB Backup'",
        ] as $productSeed) {
            self::assertStringContainsString($productSeed, $productMigration);
        }

        foreach ([
            "'nvd' AS `source_code`, 'xtrabackup'",
            "'nvd', 'pmm'",
            "'nvd', 'mariadb_backup'",
            "'percona_advisory', 'xtrabackup'",
            "'percona_advisory', 'pmm'",
        ] as $sourceSeed) {
            self::assertStringContainsString($sourceSeed, $sourceMigration);
        }
    }

    private function tableBlock(string $table): string
    {
        $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \\((.*?)\\) ENGINE=/s';

        self::assertMatchesRegularExpression($pattern, $this->migration);
        preg_match($pattern, $this->migration, $matches);

        return $matches[1] ?? '';
    }
}
