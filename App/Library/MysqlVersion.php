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
        $numeric = self::numeric($version);
        if ($numeric === '') {
            return false;
        }

        return version_compare($numeric, $minimum, '>=');
    }

    public static function supportsInnodbMetrics(?string $version, bool $isSingleStore): bool
    {
        return !$isSingleStore && self::atLeast($version, '5.6.0');
    }
}
