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
        if (
            !array_key_exists('name', $post)
            || !array_key_exists('pk', $post)
            || !array_key_exists('value', $post)
            || !is_scalar($post['name'])
            || !is_scalar($post['pk'])
            || !is_scalar($post['value'])
        ) {
            return null;
        }

        $field = (string) $post['name'];
        $id = (string) $post['pk'];
        $value = (string) $post['value'];

        if (!in_array($field, $allowedFields, true)) {
            return null;
        }

        if (!ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        if (strlen($value) > $maxValueBytes) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $value,
            'id' => (int) $id,
        ];
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
