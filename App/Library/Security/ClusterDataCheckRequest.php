<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ClusterDataCheckRequest
{
    public const MAX_SQL_BYTES = 65535;
    public const MAX_SERVER_IDS = 64;

    public static function evaluate(array $post, array $server, array $session, string $scope): array
    {
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        if ($method === 'GET' || $method === 'HEAD') {
            return self::outcome(200, '', [], self::emptySelection());
        }

        if ($method !== 'POST') {
            return self::outcome(405, 'Method Not Allowed', ['Allow' => 'GET, HEAD, POST']);
        }

        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $selection = self::normalize($post);
        if ($selection === null || !self::isComplete($selection)) {
            return self::outcome(400, 'Invalid cluster data check payload');
        }

        return self::outcome(200, '', [], $selection);
    }

    public static function normalize(array $source): ?array
    {
        if (!isset($source['mysql_cluster']) && !isset($source['sql'])) {
            return self::emptySelection();
        }

        if (!isset($source['mysql_cluster']) || !is_array($source['mysql_cluster'])) {
            return null;
        }

        foreach ($source['mysql_cluster'] as $field => $value) {
            if (!is_string($field) || !in_array($field, ['id', 'database'], true)) {
                return null;
            }
        }

        $ids = ServerIdSelection::normalizeList($source['mysql_cluster']['id'] ?? null, self::MAX_SERVER_IDS);
        if ($ids === null) {
            return null;
        }

        $database = self::normalizeDatabase($source['mysql_cluster']['database'] ?? null);
        if ($database === null) {
            return null;
        }

        $sql = self::normalizeSql($source['sql'] ?? null);
        if ($sql === null) {
            return null;
        }

        return [
            'ids' => $ids,
            'id_list' => implode(',', $ids),
            'database' => $database,
            'sql' => $sql,
        ];
    }

    public static function isComplete(array $selection): bool
    {
        return !empty($selection['ids'])
            && is_array($selection['ids'])
            && isset($selection['id_list'], $selection['database'], $selection['sql'])
            && $selection['id_list'] !== ''
            && $selection['database'] !== ''
            && $selection['sql'] !== '';
    }

    public static function applyToGet(array $selection): void
    {
        if (!self::isComplete($selection)) {
            unset($_GET['mysql_cluster'], $_GET['sql']);
            return;
        }

        $_GET['mysql_cluster'] = [
            'id' => (string) $selection['id_list'],
            'database' => (string) $selection['database'],
        ];
        $_GET['sql'] = (string) $selection['sql'];
    }

    private static function normalizeDatabase($raw): ?string
    {
        if (!is_scalar($raw)) {
            return null;
        }

        $database = trim((string) $raw);
        if (!Identifier::isDatabaseName($database)) {
            return null;
        }

        return $database;
    }

    private static function normalizeSql($raw): ?string
    {
        if (!is_scalar($raw)) {
            return null;
        }

        $rawSql = (string) $raw;
        if (strpos($rawSql, "\0") !== false) {
            return null;
        }

        $sql = trim($rawSql);
        if ($sql === '' || strlen($sql) > self::MAX_SQL_BYTES) {
            return null;
        }

        return $sql;
    }

    private static function emptySelection(): array
    {
        return [
            'ids' => [],
            'id_list' => '',
            'database' => '',
            'sql' => '',
        ];
    }

    private static function outcome(int $statusCode, string $message, array $headers = [], ?array $selection = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'selection' => $selection,
        ];
    }
}
