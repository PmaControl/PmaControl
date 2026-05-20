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
            'cve_exclusion',
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
            "('mysql_cluster', 'MySQL Cluster'",
            "('mysql_client', 'MySQL Client'",
            "('mysql_connectors', 'MySQL Connectors'",
            "('mysql_enterprise_backup', 'MySQL Enterprise Backup'",
            "('mysql_enterprise_firewall', 'MySQL Enterprise Firewall'",
            "('mysql_enterprise_monitor', 'MySQL Enterprise Monitor'",
            "('mysql_installer', 'MySQL Installer'",
            "('mysql_shell', 'MySQL Shell'",
            "('mysql_shell_vscode', 'MySQL Shell for VS Code'",
            "('mysql_workbench', 'MySQL Workbench'",
            "('enterprise_manager_mysql', 'Enterprise Manager for MySQL Database'",
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

    public function testExclusionAdminMigrationSeedsFalsePositiveAndSuperAdminMenu(): void
    {
        $path = __DIR__ . '/../../sql/incremental_v2/20260506_zzzz_cve_exclusion_admin.sql';
        self::assertFileExists($path);

        $migration = (string)file_get_contents($path);

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS `cve_exclusion`', $migration);
        self::assertStringContainsString('CVE-2022-22965', $migration);
        self::assertStringContainsString('Spring Framework / Enterprise Manager false positive', $migration);
        self::assertStringContainsString('ON DUPLICATE KEY UPDATE', $migration);
        self::assertStringContainsString("'CVE exclusions'", $migration);
        self::assertStringContainsString("'{LINK}cve/exclusions'", $migration);
        self::assertStringContainsString("'<i class=\"fa fa-ban\" aria-hidden=\"true\"></i>'", $migration);
        self::assertStringContainsString("'cve'", $migration);
        self::assertStringContainsString("'exclusions'", $migration);
        self::assertStringContainsString("`title` = 'SuperAdmin'", $migration);
    }

    public function testComponentTagMigrationsSeedProductsAndSourceScopes(): void
    {
        $productPath = __DIR__ . '/../../sql/incremental_v2/20260506_cve_component_tags.sql';
        $sourcePath = __DIR__ . '/../../sql/incremental_v2/20260506_zz_cve_component_feed_source_products.sql';
        $oracleProductPath = __DIR__ . '/../../sql/incremental_v2/20260506_zzz_cve_oracle_mysql_product_tags.sql';
        self::assertFileExists($productPath);
        self::assertFileExists($sourcePath);
        self::assertFileExists($oracleProductPath);

        $productMigration = (string)file_get_contents($productPath);
        $sourceMigration = (string)file_get_contents($sourcePath);
        $oracleProductMigration = (string)file_get_contents($oracleProductPath);

        foreach ([
            "('xtrabackup', 'Percona XtraBackup'",
            "('pmm', 'Percona Monitoring and Management'",
            "('mariadb_backup', 'MariaDB Backup'",
        ] as $productSeed) {
            self::assertStringContainsString($productSeed, $productMigration);
        }

        foreach ([
            "('mysql_cluster', 'MySQL Cluster'",
            "('mysql_enterprise_monitor', 'MySQL Enterprise Monitor'",
            "('mysql_shell_vscode', 'MySQL Shell for VS Code'",
        ] as $productSeed) {
            self::assertStringContainsString($productSeed, $oracleProductMigration);
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

        foreach ([
            "'mysql_cluster', 'MySQL Cluster'",
            "'mysql_enterprise_monitor', 'MySQL Enterprise Monitor'",
            "'mysql_shell_vscode', 'MySQL Shell for VS Code'",
            "WHERE s.`code` = 'oracle_cpu'",
        ] as $sourceSeed) {
            self::assertStringContainsString($sourceSeed, $oracleProductMigration);
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
