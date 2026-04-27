<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ArchiveRestoreRequest
{
    public static function evaluate(array $post, array $server, array $session, string $scope): array
    {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $payload = self::normalize($post);
        if ($payload === null) {
            return self::outcome(400, 'Invalid archive restore payload');
        }

        return self::outcome(200, '', [], $payload);
    }

    public static function normalize(array $post): ?array
    {
        if (
            !array_key_exists('id_cleaner_main', $post)
            || !array_key_exists('mysql_server', $post)
            || !is_scalar($post['id_cleaner_main'])
        ) {
            return null;
        }

        $cleanerIds = PositiveIntegerSelection::normalizeList($post['id_cleaner_main'], 1);
        if ($cleanerIds === null) {
            return null;
        }

        if (!is_array($post['mysql_server']) || count($post['mysql_server']) !== 1) {
            return null;
        }

        $target = reset($post['mysql_server']);
        if (
            !is_array($target)
            || array_diff(array_keys($target), ['id', 'database']) !== []
            || !array_key_exists('id', $target)
            || !array_key_exists('database', $target)
            || !is_scalar($target['id'])
            || !is_scalar($target['database'])
        ) {
            return null;
        }

        $serverIds = PositiveIntegerSelection::normalizeList($target['id'], 1);
        if ($serverIds === null) {
            return null;
        }

        $database = trim((string) $target['database']);
        if (!Identifier::isDatabaseName($database)) {
            return null;
        }

        return [
            'id_cleaner_main' => $cleanerIds[0],
            'id_mysql_server' => $serverIds[0],
            'database' => $database,
        ];
    }

    private static function outcome(
        int $statusCode,
        string $message,
        array $headers = [],
        ?array $payload = null
    ): array {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'restore' => $payload,
        ];
    }
}
