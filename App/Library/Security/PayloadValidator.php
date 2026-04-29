<?php

declare(strict_types=1);

namespace App\Library\Security;

final class PayloadValidator
{
    /**
     * @param array<string, string|array<string,mixed>> $schema
     */
    public static function validate(array $post, array $schema): ?array
    {
        $payload = [];

        foreach ($schema as $field => $rule) {
            if (!is_string($field)) {
                return null;
            }

            $normalizedRule = self::normalizeRule($rule);
            if ($normalizedRule === null) {
                return null;
            }

            if (array_key_exists($field, $post)) {
                $value = self::normalizeValue($post[$field], $normalizedRule);
                if ($value === null) {
                    return null;
                }

                $payload[$field] = $value;
                continue;
            }

            if (array_key_exists('default', $normalizedRule)) {
                $value = self::normalizeValue($normalizedRule['default'], $normalizedRule);
                if ($value === null) {
                    return null;
                }

                $payload[$field] = $value;
                continue;
            }

            if (($normalizedRule['required'] ?? true) === true) {
                return null;
            }
        }

        return $payload;
    }

    public static function normalizeValue($raw, array $rule)
    {
        $type = (string) ($rule['type'] ?? (isset($rule['values']) ? 'enum' : 'string'));
        if ($type === 'list') {
            return self::normalizeListValue($raw, $rule);
        }

        if (!is_scalar($raw)) {
            return null;
        }

        $text = (string) $raw;
        $trim = array_key_exists('trim', $rule) ? (bool) $rule['trim'] : true;
        if ($trim) {
            $text = trim($text);
        }

        if ($text === '' && array_key_exists('default', $rule) && !is_bool($raw)) {
            $text = (string) $rule['default'];
            if ($trim) {
                $text = trim($text);
            }
        }

        if ($type === 'int') {
            if (!ctype_digit($text)) {
                return null;
            }

            $value = (int) $text;
            if (!self::matchesNumericBounds($value, $rule)) {
                return null;
            }
            if (!self::matchesAllowedValues($value, $rule)) {
                return null;
            }

            return $value;
        }

        if ($type === 'float') {
            if (filter_var($text, FILTER_VALIDATE_FLOAT) === false) {
                return null;
            }

            $value = (float) $text;
            if (!self::matchesNumericBounds($value, $rule)) {
                return null;
            }
            if (!self::matchesAllowedValues($value, $rule)) {
                return null;
            }

            return $value;
        }

        if ($type === 'bool') {
            $value = self::normalizeBoolValue($raw, $text);
            if ($value === null) {
                return null;
            }
            if (!self::matchesAllowedValues($value, $rule)) {
                return null;
            }

            return $value;
        }

        if (!in_array($type, ['string', 'enum'], true)) {
            return null;
        }

        if (isset($rule['max']) && strlen($text) > (int) $rule['max']) {
            return null;
        }

        if (isset($rule['min']) && strlen($text) < (int) $rule['min']) {
            return null;
        }

        if (isset($rule['pattern']) && preg_match((string) $rule['pattern'], $text) !== 1) {
            return null;
        }

        if (($type === 'enum' || isset($rule['values'])) && !self::matchesAllowedValues($text, $rule)) {
            return null;
        }

        return $text;
    }

    private static function normalizeRule($rule): ?array
    {
        if (is_string($rule)) {
            return [
                'type' => $rule,
                'required' => true,
                'trim' => false,
            ];
        }

        if (!is_array($rule)) {
            return null;
        }

        if (!array_key_exists('type', $rule)) {
            $rule['type'] = isset($rule['values']) ? 'enum' : 'string';
        }

        if (!array_key_exists('required', $rule)) {
            $rule['required'] = true;
        }

        if (!array_key_exists('trim', $rule)) {
            $rule['trim'] = false;
        }

        return $rule;
    }

    private static function normalizeListValue($raw, array $rule): ?array
    {
        if (!is_array($raw)) {
            return null;
        }

        $count = count($raw);
        if (isset($rule['min_items']) && $count < (int) $rule['min_items']) {
            return null;
        }
        if (isset($rule['max_items']) && $count > (int) $rule['max_items']) {
            return null;
        }

        $itemRule = [
            'type' => (string) ($rule['item_type'] ?? 'string'),
        ];
        foreach (['min', 'max', 'pattern', 'values'] as $key) {
            $itemKey = 'item_'.$key;
            if (array_key_exists($itemKey, $rule)) {
                $itemRule[$key] = $rule[$itemKey];
            }
        }

        $items = [];
        foreach ($raw as $value) {
            if (!is_scalar($value)) {
                return null;
            }

            $item = self::normalizeValue($value, $itemRule);
            if ($item === null) {
                return null;
            }

            $items[] = $item;
        }

        if (count($items) !== count(array_unique($items, SORT_REGULAR))) {
            return null;
        }

        return $items;
    }

    private static function normalizeBoolValue($raw, string $text): ?bool
    {
        if (is_bool($raw)) {
            return $raw;
        }

        $normalized = strtolower($text);
        if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
            return true;
        }
        if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
            return false;
        }

        return null;
    }

    /**
     * @param int|float $value
     */
    private static function matchesNumericBounds($value, array $rule): bool
    {
        if (isset($rule['min']) && $value < (float) $rule['min']) {
            return false;
        }

        if (isset($rule['max']) && $value > (float) $rule['max']) {
            return false;
        }

        return true;
    }

    private static function matchesAllowedValues($value, array $rule): bool
    {
        if (!isset($rule['values']) || !is_array($rule['values'])) {
            return !isset($rule['values']);
        }

        return in_array($value, $rule['values'], true);
    }
}
