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
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, $scope)) {
            return self::outcome($failure['status'], $failure['body'], $failure['headers']);
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
                $default = PayloadValidator::normalizeValue($rule['default'], $rule);
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
            $payloadValue = PayloadValidator::normalizeValue($value, $rule);
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
