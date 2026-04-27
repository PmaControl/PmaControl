<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ServerIdSelection
{
    public const DEFAULT_MAX_IDS = 64;

    public static function normalizeList($raw, int $maxIds = self::DEFAULT_MAX_IDS): ?array
    {
        if (is_array($raw)) {
            $values = $raw;
        } elseif (is_scalar($raw)) {
            $values = explode(',', (string) $raw);
        } else {
            return null;
        }

        $ids = [];
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                return null;
            }

            $id = trim((string) $value);
            if ($id === '' || !ctype_digit($id) || (int) $id < 1) {
                return null;
            }

            $ids[] = (int) $id;
        }

        if ($ids === [] || count($ids) > $maxIds || count($ids) !== count(array_unique($ids))) {
            return null;
        }

        return $ids;
    }

    public static function toCsv(array $ids): string
    {
        return implode(',', array_map('intval', $ids));
    }
}
