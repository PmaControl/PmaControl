<?php

declare(strict_types=1);

namespace App\Library\Security;

final class BenchmarkBenchRequest
{
    private const MODE_PATTERN = '/^[A-Za-z0-9_.-]{1,124}\.lua$/';

    public static function evaluate(
        array $post,
        array $server,
        array $session,
        string $scope,
        array $allowedModes = []
    ): array {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $payload = self::normalize($post, $allowedModes);
        if ($payload === null) {
            return self::outcome(400, 'Invalid benchmark bench payload');
        }

        return self::outcome(200, '', [], $payload);
    }

    public static function normalize(array $post, array $allowedModes = []): ?array
    {
        if (($post['benchmark'] ?? null) !== '1') {
            return null;
        }

        if (!isset($post['mysql_server']) || !is_array($post['mysql_server']) || !array_key_exists('id', $post['mysql_server'])) {
            return null;
        }

        $serverIds = PositiveIntegerSelection::normalizeList($post['mysql_server']['id'], 64);
        if ($serverIds === null) {
            return null;
        }

        $main = GroupedFormRequest::normalize($post, 'benchmark_main', self::benchmarkMainRules());
        if ($main === null) {
            return null;
        }

        $modes = self::normalizeModes($main['mode'], $allowedModes);
        if ($modes === null) {
            return null;
        }

        return [
            'server_ids' => $serverIds,
            'threads' => $main['threads'],
            'threads_csv' => PositiveIntegerSelection::toCsv($main['threads']),
            'tables_count' => $main['tables_count'],
            'table_size' => $main['tables_count'],
            'modes' => $modes,
            'max_time' => $main['max_time'],
        ];
    }

    public static function buildInsertSql(
        array $payload,
        int $idMysqlServer,
        string $mode,
        int $idUserMain,
        string $sysbenchVersion,
        string $date
    ): string {
        return "INSERT INTO benchmark_main
                            SET id_mysql_server = '" . (int) $idMysqlServer . "',
                            id_user_main = '" . (int) $idUserMain . "',
                            date = '" . self::quote($date) . "',
                            sysbench_version = '" . self::quote($sysbenchVersion) . "',
                            threads = '" . self::quote((string) $payload['threads_csv']) . "',
                            tables_count = '" . (int) $payload['tables_count'] . "',
                            table_size = '" . (int) $payload['table_size'] . "',
                            mode = '" . self::quote($mode) . "',
                            max_time = '" . (int) $payload['max_time'] . "',
                            status = 'NOT STARTED',
                            date_start='0000-00-00 00:00:00',
                            date_end='0000-00-00 00:00:00',
                            progression=0
                            ";
    }

    private static function benchmarkMainRules(): array
    {
        return [
            'threads' => [
                'type' => 'list',
                'required' => true,
                'min_items' => 1,
                'max_items' => 64,
                'item_type' => 'int',
                'item_min' => 1,
                'item_max' => 1024,
            ],
            'tables_count' => ['type' => 'int', 'required' => true, 'min' => 1, 'max' => 100],
            'mode' => [
                'type' => 'list',
                'required' => true,
                'min_items' => 1,
                'max_items' => 16,
                'item_type' => 'string',
                'item_min' => 1,
                'item_max' => 128,
                'item_pattern' => self::MODE_PATTERN,
            ],
            'max_time' => ['type' => 'int', 'required' => true, 'min' => 0, 'max' => 299],
        ];
    }

    private static function normalizeModes(array $modes, array $allowedModes): ?array
    {
        $whitelist = [];
        foreach ($allowedModes as $allowedMode) {
            if (!is_string($allowedMode)) {
                continue;
            }

            $allowedMode = trim($allowedMode);
            if ($allowedMode !== '' && preg_match(self::MODE_PATTERN, $allowedMode) === 1) {
                $whitelist[$allowedMode] = true;
            }
        }

        if ($whitelist === []) {
            return null;
        }

        $normalized = [];
        foreach ($modes as $mode) {
            if (!is_string($mode) || preg_match(self::MODE_PATTERN, $mode) !== 1) {
                return null;
            }
            if ($whitelist !== [] && !isset($whitelist[$mode])) {
                return null;
            }

            $normalized[] = $mode;
        }

        return $normalized;
    }

    private static function quote(string $value): string
    {
        return str_replace("'", "''", $value);
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
            'benchmark' => $payload,
        ];
    }
}
