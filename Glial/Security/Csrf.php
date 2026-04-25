<?php

namespace Glial\Security;

class Csrf
{
    public const DEFAULT_FIELD = '_csrf_token';

    private const DEFAULT_SCOPE = '_default';
    private const DEFAULT_SESSION_KEY = '_csrf_token';
    private const SCOPED_SESSION_KEY = '_csrf_tokens';

    public static function issueToken(array &$session, string $scope = self::DEFAULT_SCOPE): string
    {
        if ($scope === self::DEFAULT_SCOPE) {
            if (empty($session[self::DEFAULT_SESSION_KEY]) || !is_string($session[self::DEFAULT_SESSION_KEY])) {
                $session[self::DEFAULT_SESSION_KEY] = self::generateToken();
            }

            return $session[self::DEFAULT_SESSION_KEY];
        }

        if (
            empty($session[self::SCOPED_SESSION_KEY][$scope])
            || !is_string($session[self::SCOPED_SESSION_KEY][$scope])
        ) {
            $session[self::SCOPED_SESSION_KEY][$scope] = self::generateToken();
        }

        return $session[self::SCOPED_SESSION_KEY][$scope];
    }

    public static function token(string $scope = self::DEFAULT_SCOPE): string
    {
        return self::issueToken($_SESSION, $scope);
    }

    public static function field(
        string $scope = self::DEFAULT_SCOPE,
        string $field = self::DEFAULT_FIELD
    ): string {
        return '<input type="hidden" name="' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . '" value="'
            . htmlspecialchars(self::token($scope), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateToken(
        array $request,
        array $session,
        string $scope = self::DEFAULT_SCOPE,
        string $field = self::DEFAULT_FIELD
    ): bool {
        $expected = self::getTokenFromSession($session, $scope);
        $provided = $request[$field] ?? null;

        return is_string($expected)
            && is_string($provided)
            && $expected !== ''
            && hash_equals($expected, $provided);
    }

    public static function verify(
        string $scope = self::DEFAULT_SCOPE,
        ?string $posted = null,
        bool $rotate = true,
        string $field = self::DEFAULT_FIELD
    ): bool {
        $request = [$field => $posted ?? ($_POST[$field] ?? null)];

        if (!self::validateToken($request, $_SESSION, $scope, $field)) {
            return false;
        }

        if ($rotate) {
            self::rotate($scope);
        }

        return true;
    }

    public static function rotate(string $scope = self::DEFAULT_SCOPE): void
    {
        if ($scope === self::DEFAULT_SCOPE) {
            unset($_SESSION[self::DEFAULT_SESSION_KEY]);
            return;
        }

        unset($_SESSION[self::SCOPED_SESSION_KEY][$scope]);
    }

    private static function getTokenFromSession(array $session, string $scope): ?string
    {
        if ($scope === self::DEFAULT_SCOPE) {
            return $session[self::DEFAULT_SESSION_KEY] ?? null;
        }

        return $session[self::SCOPED_SESSION_KEY][$scope] ?? null;
    }

    private static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
