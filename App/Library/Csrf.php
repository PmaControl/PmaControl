<?php

namespace App\Library;

class Csrf
{
    public const DEFAULT_FIELD = 'csrf_token';

    private const SESSION_KEY = 'csrf_tokens';

    public static function issueToken(array &$session, string $scope): string
    {
        if (
            empty($session[self::SESSION_KEY][$scope])
            || !is_string($session[self::SESSION_KEY][$scope])
        ) {
            $session[self::SESSION_KEY][$scope] = bin2hex(random_bytes(32));
        }

        return $session[self::SESSION_KEY][$scope];
    }

    public static function validateToken(
        array $request,
        array $session,
        string $scope,
        string $field = self::DEFAULT_FIELD
    ): bool {
        $expected = $session[self::SESSION_KEY][$scope] ?? null;
        $provided = $request[$field] ?? null;

        return is_string($expected)
            && is_string($provided)
            && $expected !== ''
            && hash_equals($expected, $provided);
    }
}
