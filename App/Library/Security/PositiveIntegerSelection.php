<?php

declare(strict_types=1);

namespace App\Library\Security;

final class PositiveIntegerSelection
{
    public const DEFAULT_MAX_IDS = 64;

    public static function normalizeList($raw, int $maxIds = self::DEFAULT_MAX_IDS): ?array
    {
        if (is_array($raw)) {
            $values = $raw;
        } elseif (is_scalar($raw)) {
            $text = trim((string) $raw);
            if ($text === '') {
                return null;
            }

            if (strncmp($text, '[', 1) === 0) {
                $decoded = json_decode($text, true);
                if (!is_array($decoded) || array_values($decoded) !== $decoded) {
                    return null;
                }
                $values = $decoded;
            } else {
                $values = explode(',', $text);
            }
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

    public static function toBracketedList(array $ids): string
    {
        return '[' . self::toCsv($ids) . ']';
    }
}
