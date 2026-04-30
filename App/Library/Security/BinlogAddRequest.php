<?php

declare(strict_types=1);

namespace App\Library\Security;

final class BinlogAddRequest
{
    public static function evaluate(array $post, array $server, array $session, string $scope): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, $scope)) {
            return self::outcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $payload = self::normalize($post);
        if ($payload === null) {
            return self::outcome(400, 'Invalid binlog add payload');
        }

        return self::outcome(200, '', [], $payload);
    }

    public static function normalize(array $post): ?array
    {
        $server = GroupedFormRequest::normalize(
            $post,
            'mysql_server',
            ['id' => ['type' => 'int', 'required' => true, 'min' => 1]]
        );
        $binlog = GroupedFormRequest::normalize(
            $post,
            'binlog_max',
            ['size' => ['type' => 'string', 'required' => true, 'max' => 32]]
        );
        $variables = GroupedFormRequest::normalize(
            $post,
            'variables',
            [
                'max_binlog_size' => ['type' => 'string', 'required' => false, 'max' => 64],
                'file_binlog_size' => ['type' => 'int', 'required' => true, 'min' => 1],
            ]
        );

        if ($server === null || $binlog === null || $variables === null) {
            return null;
        }

        $sizeMax = ByteSize::parse($binlog['size']);
        if ($sizeMax === null || $sizeMax < $variables['file_binlog_size']) {
            return null;
        }

        return [
            'id_mysql_server' => $server['id'],
            'size_max' => $sizeMax,
            'file_binlog_size' => $variables['file_binlog_size'],
            'number_file_max' => (int) ceil($sizeMax / $variables['file_binlog_size']),
        ];
    }

    public static function buildReplaceSql(array $payload): string
    {
        $idMysqlServer = (int) $payload['id_mysql_server'];
        $sizeMax = (int) $payload['size_max'];
        $numberFileMax = (int) $payload['number_file_max'];

        return "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('"
            . $idMysqlServer . "', '" . $sizeMax . "', '" . $numberFileMax . "')";
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
            'binlog' => $payload,
        ];
    }
}
