<?php

declare(strict_types=1);

namespace App\Library\Http;

final class HttpOutcome
{
    public static function build(
        int $statusCode,
        string $body = '',
        array $headers = [],
        array $extra = []
    ): array {
        $base = [
            'status' => $statusCode,
            'body' => $body,
            'headers' => $headers,
        ];

        return $base + $extra;
    }

    public static function ok(array $extra = [], string $body = '', array $headers = []): array
    {
        return self::build(200, $body, $headers, $extra);
    }

    public static function error(int $statusCode, string $body, array $headers = [], array $extra = []): array
    {
        return self::build($statusCode, $body, $headers, $extra);
    }

    public static function fromGuard(array $guard, array $extra = []): array
    {
        if (($guard['allowed'] ?? false) === true) {
            return self::ok($extra);
        }

        return self::error(
            (int) ($guard['status'] ?? 500),
            is_scalar($guard['body'] ?? '') ? (string) $guard['body'] : '',
            is_array($guard['headers'] ?? null) ? $guard['headers'] : [],
            $extra
        );
    }
}
