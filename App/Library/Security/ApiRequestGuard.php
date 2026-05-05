<?php

declare(strict_types=1);

namespace App\Library\Security;

use Glial\Http\Request;

final class ApiRequestGuard
{
    public static function checkJsonPostBasicAuth(string $body, array $server): array
    {
        if (!Request::isMethod($server, 'POST')) {
            return self::outcome(
                false,
                405,
                ['error' => 'This request method is not allowed : ' . (string)($server['REQUEST_METHOD'] ?? '')],
                ['Allow' => 'POST']
            );
        }

        if (!self::isJson($body)) {
            return self::outcome(
                false,
                400,
                ['error' => 'JSON malformed', 'json' => $body],
                ['WWW-Authenticate' => 'Basic realm="My Realm"']
            );
        }

        if (empty($server['PHP_AUTH_USER']) || !array_key_exists('PHP_AUTH_PW', $server)) {
            return self::outcome(
                false,
                401,
                ['error' => "Vous n'êtes pas autorisé à acceder à la ressource requise, Login or password not good"],
                ['WWW-Authenticate' => 'Basic realm="My Realm"']
            );
        }

        return self::outcome(true, 200, []);
    }

    public static function isJson(string $body): bool
    {
        json_decode($body);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private static function outcome(bool $allowed, int $status, array $body, array $headers = []): array
    {
        return [
            'allowed' => $allowed,
            'status' => $status,
            'body' => $body,
            'headers' => $headers,
        ];
    }
}
