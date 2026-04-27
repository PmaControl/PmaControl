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
            if (!is_string($field) || !array_key_exists($field, $rules) || !is_scalar($value)) {
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
        if (!is_scalar($raw)) {
            return null;
        }

        $text = trim((string) $raw);
        if ($text === '' && array_key_exists('default', $rule)) {
            $text = trim((string) $rule['default']);
        }

        $type = (string) ($rule['type'] ?? (isset($rule['values']) ? 'enum' : 'string'));
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

        if ($type === 'enum') {
            if (!isset($rule['values']) || !is_array($rule['values']) || !in_array($text, $rule['values'], true)) {
                return null;
            }
        }

        return $text;
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
