<?php

declare(strict_types=1);

use App\Library\Cve\CveCatalogBuilder;
use PHPUnit\Framework\TestCase;

final class CveCatalogBuilderTest extends TestCase
{
    public function testProductFromCpeCriteriaMapsDatabaseProducts(): void
    {
        self::assertSame('mysql', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:oracle:mysql:*:*:*:*:*:*:*:*'));
        self::assertSame('mariadb', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:mariadb:mariadb:10.6.19:*:*:*:*:*:*:*'));
        self::assertSame('percona', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:percona:percona_server:8.0.36:*:*:*:*:*:*:*'));
        self::assertSame('xtrabackup', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:percona:xtrabackup:2.4.20:*:*:*:*:*:*:*'));
        self::assertSame('xtrabackup', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:percona:percona-xtrabackup:8.0.35:*:*:*:*:*:*:*'));
        self::assertSame('pmm', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:percona:monitoring_and_management:2.37.0:*:*:*:*:*:*:*'));
        self::assertSame('mariadb_backup', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:mariadb:mariadb_backup:10.11.7:*:*:*:*:*:*:*'));
        self::assertSame('mariadb_backup', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:mariadb:mariabackup:10.11.7:*:*:*:*:*:*:*'));
        self::assertNull(CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:percona:percona_server:*:*:*:*:*:mongodb:*:*'));
        self::assertSame('proxysql', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:proxysql:proxysql:2.5.5:*:*:*:*:*:*:*'));
        self::assertSame('aurora_mysql', CveCatalogBuilder::productFromCpeCriteria('cpe:2.3:a:amazon:aurora_mysql:3.08.0:*:*:*:*:*:*:*'));
    }

    public function testVersionTextFromCpeMatchKeepsExactAndRangeVersions(): void
    {
        self::assertSame(
            '5.0.33',
            CveCatalogBuilder::versionTextFromCpeMatch([
                'criteria' => 'cpe:2.3:a:oracle:mysql:5.0.33:*:*:*:*:*:*:*',
            ])
        );

        self::assertSame(
            '>= 4.1.0, < 5.0.0',
            CveCatalogBuilder::versionTextFromCpeMatch([
                'criteria' => 'cpe:2.3:a:oracle:mysql:*:*:*:*:*:*:*:*',
                'versionStartIncluding' => '4.1.0',
                'versionEndExcluding' => '5.0.0',
            ])
        );
    }

    public function testNormalizeProductCodeAcceptsUiAliases(): void
    {
        self::assertSame('mysql', CveCatalogBuilder::normalizeProductCode('mysql_server'));
        self::assertSame('mysql', CveCatalogBuilder::normalizeProductCode('MySQL Server [8478]'));
        self::assertSame('mysql_cluster', CveCatalogBuilder::normalizeProductCode('MySQL Cluster'));
        self::assertSame('mysql_client', CveCatalogBuilder::normalizeProductCode('MySQL Client'));
        self::assertSame('mysql_connectors', CveCatalogBuilder::normalizeProductCode('MySQL Connectors'));
        self::assertSame('mysql_enterprise_backup', CveCatalogBuilder::normalizeProductCode('MySQL Enterprise Backup'));
        self::assertSame('mysql_enterprise_firewall', CveCatalogBuilder::normalizeProductCode('MySQL Enterprise Firewall'));
        self::assertSame('mysql_enterprise_monitor', CveCatalogBuilder::normalizeProductCode('MySQL Enterprise Monitor'));
        self::assertSame('mysql_installer', CveCatalogBuilder::normalizeProductCode('MySQL Installer'));
        self::assertSame('mysql_shell', CveCatalogBuilder::normalizeProductCode('MySQL Shell'));
        self::assertSame('mysql_shell_vscode', CveCatalogBuilder::normalizeProductCode('MySQL Shell for VS Code'));
        self::assertSame('mysql_workbench', CveCatalogBuilder::normalizeProductCode('MySQL Workbench'));
        self::assertSame('enterprise_manager_mysql', CveCatalogBuilder::normalizeProductCode('Enterprise Manager for MySQL Database'));
        self::assertSame('aurora_mysql', CveCatalogBuilder::normalizeProductCode('Aurora MySQL'));
        self::assertSame('rds_mysql', CveCatalogBuilder::normalizeProductCode('RDS for MySQL'));
        self::assertSame('xtrabackup', CveCatalogBuilder::normalizeProductCode('Percona XtraBackup'));
        self::assertSame('pmm', CveCatalogBuilder::normalizeProductCode('PMM Server'));
        self::assertSame('mariadb_backup', CveCatalogBuilder::normalizeProductCode('mariadb-backup'));
        self::assertSame('mariadb_backup', CveCatalogBuilder::normalizeProductCode('mariabackup'));
        self::assertNull(CveCatalogBuilder::normalizeProductCode('postgresql'));
    }

    public function testProductTextHeuristicKeepsOutOfScopePerconaComponentsOut(): void
    {
        $method = new ReflectionMethod(CveCatalogBuilder::class, 'productsFromText');

        self::assertSame(['pmm'], $method->invoke(null, 'Percona Monitoring and Management PMM Server before 2.37.1'));
        self::assertSame(['xtrabackup'], $method->invoke(null, 'Percona-XtraBackup before 8.0.35'));
        self::assertSame(['mariadb_backup'], $method->invoke(null, 'mariabackup before 10.11.7'));
        self::assertSame([], $method->invoke(null, 'percona-toolkit 3.6.0 allows brute forcing'));
        self::assertSame([], $method->invoke(null, 'MongoDB Simple LDAP plugin for Percona Server'));
    }

    public function testPerconaAdvisoryRowsPreferComponentTags(): void
    {
        $method = new ReflectionMethod(CveCatalogBuilder::class, 'productFromPerconaAdvisoryRow');

        self::assertSame('xtrabackup', $method->invoke(null, [
            'product' => 'Percona Server for MySQL',
            'affected_versions' => 'cpe:2.3:a:percona:xtrabackup:*:*:*:*:*:*:*:* versionEndExcluding=2.4.20',
        ]));
        self::assertSame('pmm', $method->invoke(null, [
            'product' => 'Percona Server for MySQL',
            'affected_versions' => 'cpe:2.3:a:percona:monitoring_and_management:*:*:*:*:*:*:*:*',
        ]));
        self::assertNull($method->invoke(null, [
            'product' => 'Percona Server for MySQL',
            'affected_versions' => 'cpe:2.3:a:percona:toolkit:3.6.0:*:*:*:*:*:*:*',
        ]));
    }

    public function testOracleCpuRowsUseProductSpecificTags(): void
    {
        $method = new ReflectionMethod(CveCatalogBuilder::class, 'productFromOracleCpuRow');

        self::assertSame('mysql', $method->invoke(null, [
            'product' => 'MySQL Server',
        ]));
        self::assertSame('mysql_shell_vscode', $method->invoke(null, [
            'product' => 'MySQL Shell for VS Code',
        ]));
        self::assertSame('mysql_enterprise_monitor', $method->invoke(null, [
            'product' => 'MySQL Enterprise Monitor [8480]',
        ]));
        self::assertNull($method->invoke(null, [
            'product' => 'Oracle Security Alert CVE-2021-44228',
        ]));
    }
}
