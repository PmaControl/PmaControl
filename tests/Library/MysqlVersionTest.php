<?php

declare(strict_types=1);

use App\Library\MysqlVersion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MysqlVersionTest extends TestCase
{
    #[DataProvider('numericProvider')]
    public function testNumericNormalizesVersionStrings(?string $version, string $expected): void
    {
        $this->assertSame($expected, MysqlVersion::numeric($version));
    }

    public static function numericProvider(): array
    {
        return [
            'null' => [null, ''],
            'empty' => ['', ''],
            'blank' => ['   ', ''],
            'mysql log suffix' => ['5.6.51-log', '5.6.51'],
            'mysql whitespace' => [' 8.0.44-log ', '8.0.44'],
            'mariadb debian suffix' => ['10.11.16-MariaDB-deb12-log', '10.11.16'],
            'percona suffix' => ['8.0.36-28', '8.0.36'],
            'invalid' => ['invalid', ''],
        ];
    }

    #[DataProvider('compareProvider')]
    public function testCompareHandlesMysqlVersionStrings(?string $version, string $reference, string $operator, bool $expected): void
    {
        $this->assertSame($expected, MysqlVersion::compare($version, $reference, $operator));
    }

    public static function compareProvider(): array
    {
        return [
            'null is not comparable' => [null, '5.0.0', '>=', false],
            'null is not less or equal' => [null, '5.6', '<=', false],
            'empty is not comparable' => ['', '5.0.0', '<', false],
            'invalid is not comparable' => ['not-a-version', '5.0.0', '>=', false],
            'mysql 5.0.1 at least 5.0.1' => ['5.0.1-log', '5.0.1', '>=', true],
            'mysql 4.1 less than 5.0' => ['4.1.22-standard-log', '5.0.0', '<', true],
            'mysql 5.0 not less than 5.0' => ['5.0.0-log', '5.0.0', '<', false],
            'mariadb 10.11 at least 10.5' => ['10.11.16-MariaDB-deb12-log', '10.5.0', '>=', true],
            'percona 8.4 at least 8.4' => ['8.4.8-8', '8.4.0', '>=', true],
        ];
    }

    #[DataProvider('atLeastProvider')]
    public function testAtLeast(?string $version, string $minimum, bool $expected): void
    {
        $this->assertSame($expected, MysqlVersion::atLeast($version, $minimum));
    }

    public static function atLeastProvider(): array
    {
        return [
            'null' => [null, '5.0.0', false],
            'empty' => ['', '5.0.0', false],
            'below' => ['5.7.16-log', '5.7.17', false],
            'equal' => ['5.7.17-log', '5.7.17', true],
            'above' => ['8.0.44', '5.7.17', true],
        ];
    }

    #[DataProvider('lessThanProvider')]
    public function testLessThan(?string $version, string $maximum, bool $expected): void
    {
        $this->assertSame($expected, MysqlVersion::lessThan($version, $maximum));
    }

    public static function lessThanProvider(): array
    {
        return [
            'null' => [null, '5.0.0', false],
            'empty' => ['', '5.0.0', false],
            'below' => ['4.1.22-standard-log', '5.0.0', true],
            'equal' => ['5.0.0-log', '5.0.0', false],
            'above' => ['5.0.1-log', '5.0.0', false],
        ];
    }

    #[DataProvider('serverFamilyProvider')]
    public function testServerFamilyDetection(?string $version, ?string $versionComment, bool $isMariaDb, bool $isSingleStore): void
    {
        $this->assertSame($isMariaDb, MysqlVersion::isMariaDb($version, $versionComment));
        $this->assertSame($isSingleStore, MysqlVersion::isSingleStore($version, $versionComment));
    }

    public static function serverFamilyProvider(): array
    {
        return [
            'nulls' => [null, null, false, false],
            'mariadb version' => ['10.11.16-MariaDB-deb12-log', '', true, false],
            'mariadb comment' => ['10.11.16', 'MariaDB Server', true, false],
            'singlestore version' => ['8.0.34-SingleStore', '', false, true],
            'singlestore comment' => ['8.0.34', 'SingleStore DB', false, true],
            'mysql' => ['8.0.44', 'MySQL Community Server', false, false],
        ];
    }

    #[DataProvider('supportsInnodbMetricsProvider')]
    public function testSupportsInnodbMetrics(?string $version, bool $isSingleStore, bool $expected): void
    {
        $this->assertSame($expected, MysqlVersion::supportsInnodbMetrics($version, $isSingleStore));
    }

    public static function supportsInnodbMetricsProvider(): array
    {
        return [
            'null' => [null, false, false],
            'empty' => ['', false, false],
            'mysql 5.5' => ['5.5.62-log', false, false],
            'mysql 5.6' => ['5.6.51-log', false, true],
            'mysql 8.0' => ['8.0.44', false, true],
            'mysql 8.4' => ['8.4.8', false, true],
            'mariadb 10.0' => ['10.0.38-MariaDB-log', false, true],
            'mariadb 10.11' => ['10.11.16-MariaDB-deb12-log', false, true],
            'percona 8.0' => ['8.0.36-28', false, true],
            'singlestore skips metrics' => ['8.0.34', true, false],
        ];
    }

    public function testSupportsInnodbMetricsDoesNotPassNullToVersionCompare(): void
    {
        $errors = [];
        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = [$severity, $message];

            return true;
        });

        try {
            $this->assertFalse(MysqlVersion::supportsInnodbMetrics(null, false));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $errors);
    }

    public function testCompareDoesNotPassNullToVersionCompare(): void
    {
        $errors = [];
        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = [$severity, $message];

            return true;
        });

        try {
            $this->assertFalse(MysqlVersion::compare(null, '5.0.0', '>='));
            $this->assertFalse(MysqlVersion::lessThan(null, '5.0.0'));
            $this->assertFalse(MysqlVersion::atLeast(null, '5.0.0'));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $errors);
    }

    /**
     * (#1268) Debian 13 / MariaDB 11.8 banner caused mysqlbinlog 3.5
     * to bail with "unrecognized MariaDB version". Verify our own
     * parser extracts numeric + isMariaDb correctly so the binary
     * resolver can route the request to a version-specific binary.
     */
    public function testNumericHandlesMariaDb11Debian13Banner(): void
    {
        $this->assertSame('11.8.6', MysqlVersion::numeric('11.8.6-MariaDB-0+deb13u1 from Debian-log'));
    }

    public function testIsMariaDbDetectsDebian13Banner(): void
    {
        $this->assertTrue(MysqlVersion::isMariaDb('11.8.6-MariaDB-0+deb13u1 from Debian-log'));
        $this->assertTrue(MysqlVersion::isMariaDb('10.11.16-MariaDB-deb12-log'));
    }

    /**
     * @return array<string,array{0:?string,1:string}>
     */
    public static function majorMinorProvider(): array
    {
        return [
            'null'                 => [null, ''],
            'empty'                => ['', ''],
            'mariadb 10.11 deb12'  => ['10.11.16-MariaDB-deb12-log', '10.11'],
            'mariadb 11.8 deb13'   => ['11.8.6-MariaDB-0+deb13u1 from Debian-log', '11.8'],
            'mysql 8.0 log'        => ['8.0.44-log', '8.0'],
            'mysql 8.4'            => ['8.4.0', '8.4'],
            'percona 8.0.36-28'    => ['8.0.36-28', '8.0'],
            'mysql 5.6.51-log'     => ['5.6.51-log', '5.6'],
            'leading whitespace'   => ['  8.0.44-log', ''],
            'no number'            => ['invalid', ''],
        ];
    }

    #[DataProvider('majorMinorProvider')]
    public function testMajorMinor(?string $version, string $expected): void
    {
        $this->assertSame($expected, MysqlVersion::majorMinor($version));
    }
}
