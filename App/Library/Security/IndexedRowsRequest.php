<?php

declare(strict_types=1);

namespace App\Library\Security;

final class IndexedRowsRequest
{
    public const DEFAULT_MAX_ROWS = 1000;

    public static function normalize(array $post, string $group, array $rules, int $maxRows = self::DEFAULT_MAX_ROWS): ?array
    {
        if ($group === '' || !isset($post[$group]) || !is_array($post[$group]) || $post[$group] === []) {
            return null;
        }

        $source = $post[$group];
        if (count($source) > $maxRows) {
            return null;
        }

        $rows = [];
        foreach ($source as $row) {
            if (!is_array($row)) {
                return null;
            }

            foreach ($row as $field => $value) {
                if (!is_string($field) || !array_key_exists($field, $rules)) {
                    return null;
                }
            }

            $normalized = [];
            foreach ($rules as $field => $rule) {
                if (!is_string($field) || !is_array($rule)) {
                    return null;
                }

                $raw = $row[$field] ?? null;
                $value = self::normalizeValue($raw, $rule);
                if ($value === null) {
                    return null;
                }

                $normalized[$field] = $value;
            }

            $rows[] = $normalized;
        }

        return $rows;
    }

    private static function normalizeValue($raw, array $rule)
    {
        if ($raw === null) {
            if (!array_key_exists('default', $rule)) {
                return null;
            }

            $raw = $rule['default'];
        }

        if (!is_scalar($raw)) {
            return null;
        }

        $type = (string) ($rule['type'] ?? 'string');
        $text = trim((string) $raw);
        if ($text === '' && array_key_exists('default', $rule)) {
            $text = trim((string) $rule['default']);
        }

        if ($type === 'bool') {
            if (in_array($text, ['1', 'on', 'true'], true)) {
                return true;
            }
            if (in_array($text, ['', '0', 'off', 'false'], true)) {
                return false;
            }

            return null;
        }

        if ($type === 'int') {
            if (!ctype_digit($text)) {
                return null;
            }

            $value = (int) $text;
            if (isset($rule['min']) && $value < (int) $rule['min']) {
                return null;
            }
            if (isset($rule['max']) && $value > (int) $rule['max']) {
                return null;
            }

            return $value;
        }

        if ($type !== 'string') {
            return null;
        }

        if (isset($rule['max']) && strlen($text) > (int) $rule['max']) {
            return null;
        }
        if (isset($rule['min']) && strlen($text) < (int) $rule['min']) {
            return null;
        }

        return $text;
    }
}
