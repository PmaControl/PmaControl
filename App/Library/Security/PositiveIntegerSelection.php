<?php

declare(strict_types=1);

namespace App\Library\Security;

final class PositiveIntegerSelection
{
    public const DEFAULT_MAX_IDS = 64;

    public static function normalizeSingle($raw, ?int $maxValue = null): ?int
    {
        if (!is_scalar($raw)) {
            return null;
        }

        $id = trim((string) $raw);
        if ($id === '' || !ctype_digit($id)) {
            return null;
        }

        $normalized = ltrim($id, '0');
        if ($normalized === '') {
            return null;
        }

        $maximum = (string) ($maxValue ?? PHP_INT_MAX);
        if (
            strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)
        ) {
            return null;
        }

        return (int) $normalized;
    }

    public static function normalizeList($raw, int $maxIds = self::DEFAULT_MAX_IDS, ?int $maxValue = null): ?array
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
            $id = self::normalizeSingle($value, $maxValue);
            if ($id === null) {
                return null;
            }

            $ids[] = $id;
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
