<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ServerIdSelection
{
    public const DEFAULT_MAX_IDS = 64;

    public static function normalizeList($raw, int $maxIds = self::DEFAULT_MAX_IDS): ?array
    {
        return PositiveIntegerSelection::normalizeList($raw, $maxIds);
    }

    public static function toCsv(array $ids): string
    {
        return PositiveIntegerSelection::toCsv($ids);
    }
}
