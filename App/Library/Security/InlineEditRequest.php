<?php

declare(strict_types=1);

namespace App\Library\Security;

final class InlineEditRequest
{
    public const DEFAULT_MAX_VALUE_BYTES = 4096;

    public static function evaluate(
        array $post,
        array $server,
        array $session,
        string $scope,
        array $allowedFields,
        string $invalidPayloadMessage,
        int $maxValueBytes = self::DEFAULT_MAX_VALUE_BYTES
    ): array {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $update = self::normalize($post, $allowedFields, $maxValueBytes);
        if ($update === null) {
            return self::outcome(400, $invalidPayloadMessage);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'update' => $update,
        ];
    }

    public static function normalize(array $post, array $allowedFields, int $maxValueBytes = self::DEFAULT_MAX_VALUE_BYTES): ?array
    {
        $payload = PayloadValidator::validate($post, [
            'name' => 'string',
            'pk' => 'string',
            'value' => ['type' => 'string', 'max' => $maxValueBytes],
        ]);
        if ($payload === null) {
            return null;
        }

        $field = $payload['name'];
        $id = self::normalizePositiveIntegerId($payload['pk']);
        $value = $payload['value'];

        if (!in_array($field, $allowedFields, true)) {
            return null;
        }

        if ($id === null) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $value,
            'id' => $id,
        ];
    }

    private static function normalizePositiveIntegerId(string $value): ?int
    {
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }

        $normalized = ltrim($value, '0');
        if ($normalized === '') {
            return null;
        }

        $max = (string) PHP_INT_MAX;
        if (
            strlen($normalized) > strlen($max)
            || (strlen($normalized) === strlen($max) && strcmp($normalized, $max) > 0)
        ) {
            return null;
        }

        return (int) $normalized;
    }

    private static function outcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'update' => null,
        ];
    }
}
