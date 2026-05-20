<?php

declare(strict_types=1);

use App\Library\MysqlbinlogBinaryResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * (#1268) Pure tests for the binary picker that maps a server version
 * to a mysqlbinlog path. No filesystem I/O — every test injects a
 * synthetic list of "available" paths.
 */
final class MysqlbinlogBinaryResolverTest extends TestCase
{
    private const DIR = '/srv/www/pmacontrol/bin/mysqlbinlog/x86_64/';

    /**
     * @return array<string,array{0:string,1:list<string>,2:?string}>
     */
    public static function pickProvider(): array
    {
        $dir = self::DIR;

        $fullBundle = [
            $dir . 'mysqlbinlog-5.6',
            $dir . 'mysqlbinlog-5.7',
            $dir . 'mysqlbinlog-8.0',
            $dir . 'mysqlbinlog-8.4',
            $dir . 'mysqlbinlog-9.1',
            $dir . 'mysqlbinlog-9.2',
            $dir . 'mysqlbinlog-9.7',
            $dir . 'mysqlbinlog-mariadb',
        ];

        $bundleWithVersionedMariaDb = array_merge($fullBundle, [
            $dir . 'mysqlbinlog-mariadb-10.11',
            $dir . 'mysqlbinlog-mariadb-11.8',
        ]);

        return [
            // MariaDB — version-specific wins over the generic file.
            'mariadb 11.8 deb13 → versioned' => [
                '11.8.6-MariaDB-0+deb13u1 from Debian-log',
                $bundleWithVersionedMariaDb,
                $dir . 'mysqlbinlog-mariadb-11.8',
            ],
            'mariadb 10.11 deb12 → versioned' => [
                '10.11.16-MariaDB-deb12-log',
                $bundleWithVersionedMariaDb,
                $dir . 'mysqlbinlog-mariadb-10.11',
            ],

            // MariaDB — fallback to the legacy generic binary.
            'mariadb 11.8 deb13 → generic fallback' => [
                '11.8.6-MariaDB-0+deb13u1 from Debian-log',
                $fullBundle,
                $dir . 'mysqlbinlog-mariadb',
            ],
            'mariadb 10.11 deb12 → generic fallback' => [
                '10.11.16-MariaDB-deb12-log',
                $fullBundle,
                $dir . 'mysqlbinlog-mariadb',
            ],

            // MariaDB — neither versioned nor generic available.
            'mariadb 11.8 → no MariaDB binary at all' => [
                '11.8.6-MariaDB-0+deb13u1 from Debian-log',
                [$dir . 'mysqlbinlog-8.4'],
                null,
            ],

            // MySQL Oracle — exact match.
            'mysql 8.0.44 → 8.0' => [
                '8.0.44-log',
                $fullBundle,
                $dir . 'mysqlbinlog-8.0',
            ],
            'mysql 8.4.0 → 8.4' => [
                '8.4.0',
                $fullBundle,
                $dir . 'mysqlbinlog-8.4',
            ],

            // MySQL Oracle — forward-compat: pick the lowest ≥ requested
            // when the exact major.minor is missing.
            'mysql 8.1 → 8.4 (forward)' => [
                '8.1.5-community',
                $fullBundle,
                $dir . 'mysqlbinlog-8.4',
            ],
            'mysql 9.5 → 9.7 (forward)' => [
                '9.5.0',
                $fullBundle,
                $dir . 'mysqlbinlog-9.7',
            ],

            // MySQL Oracle — request older than the entire bundle:
            // exact match still wins (5.6 is bundled).
            'mysql 5.6.51 → 5.6' => [
                '5.6.51-log',
                $fullBundle,
                $dir . 'mysqlbinlog-5.6',
            ],

            // MySQL Oracle — beyond newest available: pick the highest.
            'mysql 11.0 → highest available (9.7)' => [
                '11.0.0',
                $fullBundle,
                $dir . 'mysqlbinlog-9.7',
            ],

            // Edge: empty `available`.
            'empty bundle' => [
                '8.0.44',
                [],
                null,
            ],

            // Edge: unparseable version.
            'invalid version, only mariadb available' => [
                'gibberish',
                [$dir . 'mysqlbinlog-mariadb'],
                null,
            ],
        ];
    }

    /**
     * @param list<string> $available
     */
    #[DataProvider('pickProvider')]
    public function testPick(string $version, array $available, ?string $expected): void
    {
        $this->assertSame($expected, MysqlbinlogBinaryResolver::pick($version, $available));
    }

    public function testSuggestForVersionMentionsExpectedFilename(): void
    {
        $this->assertStringContainsString(
            'mysqlbinlog-mariadb-11.8',
            MysqlbinlogBinaryResolver::suggestForVersion('11.8.6-MariaDB-0+deb13u1 from Debian-log')
        );
        $this->assertStringContainsString(
            'mysqlbinlog-mariadb-10.11',
            MysqlbinlogBinaryResolver::suggestForVersion('10.11.16-MariaDB-deb12-log')
        );
        $this->assertStringContainsString(
            'mysqlbinlog-8.0',
            MysqlbinlogBinaryResolver::suggestForVersion('8.0.44-log')
        );
    }
}
