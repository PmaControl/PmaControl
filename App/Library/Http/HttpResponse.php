<?php

declare(strict_types=1);

namespace App\Library\Http;

final class HttpResponse
{
    public static function error(
        int $statusCode,
        string $message = '',
        array $headers = [],
        ?string $contentType = 'text/plain; charset=UTF-8'
    ): array {
        return self::response($statusCode, $message, $headers, $contentType);
    }

    public static function fromOutcome(
        array $outcome,
        ?string $contentType = 'text/plain; charset=UTF-8'
    ): array {
        return self::response(
            (int) ($outcome['status'] ?? 500),
            is_scalar($outcome['body'] ?? '') ? (string) $outcome['body'] : '',
            is_array($outcome['headers'] ?? null) ? $outcome['headers'] : [],
            $contentType
        );
    }

    public static function sendOutcome(
        array $outcome,
        ?string $contentType = 'text/plain; charset=UTF-8'
    ): void {
        self::send(self::fromOutcome($outcome, $contentType));
    }

    public static function sendError(
        int $statusCode,
        string $message = '',
        array $headers = [],
        ?string $contentType = 'text/plain; charset=UTF-8'
    ): void {
        self::send(self::error($statusCode, $message, $headers, $contentType));
    }

    public static function send(array $response): void
    {
        http_response_code((int) ($response['status'] ?? 500));

        foreach (self::normalizeHeaders($response['headers'] ?? []) as $name => $value) {
            header($name . ': ' . $value);
        }

        $body = (string) ($response['body'] ?? '');
        if ($body !== '') {
            echo $body;
        }
    }

    private static function response(
        int $statusCode,
        string $body,
        array $headers,
        ?string $contentType
    ): array {
        $headers = self::normalizeHeaders($headers);
        if ($contentType !== null && ! self::hasHeader($headers, 'Content-Type')) {
            $headers['Content-Type'] = $contentType;
        }

        return [
            'status' => $statusCode,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            if (! is_string($name) || $name === '' || ! is_scalar($value)) {
                continue;
            }

            $normalized[$name] = (string) $value;
        }

        return $normalized;
    }

    private static function hasHeader(array $headers, string $expected): bool
    {
        foreach ($headers as $name => $_) {
            if (strcasecmp((string) $name, $expected) === 0) {
                return true;
            }
        }

        return false;
    }
}
