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
        self::assertSame('aurora_mysql', CveCatalogBuilder::normalizeProductCode('Aurora MySQL'));
        self::assertSame('rds_mysql', CveCatalogBuilder::normalizeProductCode('RDS for MySQL'));
        self::assertNull(CveCatalogBuilder::normalizeProductCode('postgresql'));
    }
}
