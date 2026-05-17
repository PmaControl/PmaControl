<?php

declare(strict_types=1);

namespace App\Library;

final class MysqlVersion
{
    public static function numeric(?string $version): string
    {
        $version = trim((string) $version);
        if ($version === '') {
            return '';
        }

        $numeric = preg_replace('/[^0-9.].*/', '', $version);

        return is_string($numeric) ? $numeric : '';
    }

    public static function atLeast(?string $version, string $minimum): bool
    {
        return self::compare($version, $minimum, '>=');
    }

    public static function lessThan(?string $version, string $maximum): bool
    {
        return self::compare($version, $maximum, '<');
    }

    public static function compare(?string $version, string $reference, string $operator): bool
    {
        $numeric = self::numeric($version);
        if ($numeric === '') {
            return false;
        }

        return version_compare($numeric, $reference, $operator);
    }

    public static function isMariaDb(?string $version, ?string $versionComment = ''): bool
    {
        return stripos((string) $version, 'MariaDB') !== false
            || stripos((string) $versionComment, 'MariaDB') !== false;
    }

    public static function isSingleStore(?string $version, ?string $versionComment = ''): bool
    {
        return stripos((string) $version, 'SingleStore') !== false
            || stripos((string) $versionComment, 'SingleStore') !== false;
    }

    public static function supportsInnodbMetrics(?string $version, bool $isSingleStore): bool
    {
        return !$isSingleStore && self::atLeast($version, '5.6.0');
    }

    /**
     * Return the `major.minor` token of a server version string, or
     * empty string when none can be extracted. Used by the binary
     * resolver to map a server version to its mysqlbinlog binary.
     * (#1268)
     *
     * Examples (Debian/Ubuntu version banners):
     *   '10.11.16-MariaDB-deb12-log'              → '10.11'
     *   '11.8.6-MariaDB-0+deb13u1 from Debian-log'→ '11.8'
     *   '8.0.44-0ubuntu0.22.04.1'                 → '8.0'
     *   ''                                        → ''
     */
    public static function majorMinor(?string $version): string
    {
        if (!preg_match('/^(\d+\.\d+)/', (string) $version, $m)) {
            return '';
        }
        return $m[1];
    }
}
