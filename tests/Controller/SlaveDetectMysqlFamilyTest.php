<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1194 — pin Slave::detectMysqlFamily so a future refactor
 * cannot accidentally collapse Percona/MariaDB/MySQL into the same
 * bucket. The classification is what gates the GTID Activate button
 * on /slave/show — getting it wrong puts MariaDB into the MySQL
 * branch (or vice versa) and lets the operator brick replication.
 */
final class SlaveDetectMysqlFamilyTest extends TestCase
{
    public static function familyCases(): array
    {
        return [
            // MariaDB family
            'MariaDB Server'                       => ['MariaDB Server',          '10.6.12-MariaDB',        'mariadb'],
            'MariaDB lowercase'                    => ['mariadb',                  '',                       'mariadb'],
            'MariaDB version only'                 => ['',                         '10.5.13-MariaDB-log',    'mariadb'],
            // MySQL family — Percona / Oracle / vanilla
            'Percona Server'                       => ['Percona Server (GPL), Release 28', '8.0.34-26', 'mysql'],
            'Source distribution (Oracle 8.0)'     => ['Source distribution',     '8.0.36',                 'mysql'],
            'MySQL Community'                      => ['MySQL Community Server - GPL', '8.0.32', 'mysql'],
            'plain MySQL'                          => ['MySQL',                    '5.7.42',                 'mysql'],
            'Percona 5.6'                          => ['Percona Server (GPL), wsrep_25.10', '5.6.51-91.0',   'mysql'],
            'version 5.x bare'                     => ['',                         '5.7.40',                 'mysql'],
            'version 8.x bare'                     => ['',                         '8.0.36',                 'mysql'],
            // Unknown / empty
            'empty'                                => ['',                         '',                       'unknown'],
            'unknown product'                      => ['Some weird fork',          '1.2.3',                  'unknown'],
        ];
    }

    #[DataProvider('familyCases')]
    public function testDetectMysqlFamily(string $versionComment, string $version, string $expected): void
    {
        $this->assertSame($expected, Slave::detectMysqlFamily($versionComment, $version));
    }
}
