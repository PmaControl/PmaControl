<?php

declare(strict_types=1);

namespace App\Library\Security;

final class GroupedFormRequest
{
    public static function evaluate(
        array $post,
        array $server,
        array $session,
        string $scope,
        string $group,
        array $rules,
        string $invalidPayloadMessage
    ): array {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $payload = self::normalize($post, $group, $rules);
        if ($payload === null) {
            return self::outcome(400, $invalidPayloadMessage);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'payload' => $payload,
        ];
    }

    public static function normalize(array $post, string $group, array $rules): ?array
    {
        if ($group === '' || !isset($post[$group]) || !is_array($post[$group]) || $post[$group] === []) {
            return null;
        }

        $source = $post[$group];
        $payload = [];

        foreach ($rules as $field => $rule) {
            if (!is_string($field) || !is_array($rule)) {
                return null;
            }

            if (array_key_exists('default', $rule)) {
                $default = self::normalizeValue($rule['default'], $rule);
                if ($default === null) {
                    return null;
                }

                $payload[$field] = $default;
            }
        }

        foreach ($source as $field => $value) {
            if (!is_string($field) || !array_key_exists($field, $rules)) {
                return null;
            }

            $rule = $rules[$field];
            $payloadValue = self::normalizeValue($value, $rule);
            if ($payloadValue === null) {
                return null;
            }

            $payload[$field] = $payloadValue;
        }

        foreach ($rules as $field => $rule) {
            if (($rule['required'] ?? false) && (!array_key_exists($field, $payload) || $payload[$field] === '')) {
                return null;
            }
        }

        $orderedPayload = [];
        foreach ($rules as $field => $rule) {
            if (array_key_exists($field, $payload)) {
                $orderedPayload[$field] = $payload[$field];
            }
        }

        return $orderedPayload;
    }

    private static function normalizeValue($raw, array $rule)
    {
        $type = (string) ($rule['type'] ?? (isset($rule['values']) ? 'enum' : 'string'));
        if ($type === 'list') {
            return self::normalizeListValue($raw, $rule);
        }

        if (!is_scalar($raw)) {
            return null;
        }

        $text = trim((string) $raw);
        if ($text === '' && array_key_exists('default', $rule)) {
            $text = trim((string) $rule['default']);
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

        if ($type === 'enum') {
            if (!isset($rule['values']) || !is_array($rule['values']) || !in_array($text, $rule['values'], true)) {
                return null;
            }
        }

        return $text;
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

    private static function outcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'payload' => null,
        ];
    }
}
