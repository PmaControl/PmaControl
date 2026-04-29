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
}
