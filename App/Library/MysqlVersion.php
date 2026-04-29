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
}
