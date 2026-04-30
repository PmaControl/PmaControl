<?php

declare(strict_types=1);

namespace App\Library\Security;

use Glial\Http\Request;
use Glial\Security\Csrf;

final class CsrfGuard
{
    public const TRUSTED_ORIGIN = 'PMACONTROL_TRUSTED_ORIGIN';

    public static function trustedOrigin(?string $configuredOrigin = null): ?string
    {
        if ($configuredOrigin !== null) {
            return self::normalizeOrigin($configuredOrigin);
        }

        if (defined(self::TRUSTED_ORIGIN)) {
            $constantOrigin = self::normalizeOrigin((string) constant(self::TRUSTED_ORIGIN));
            if ($constantOrigin !== null) {
                return $constantOrigin;
            }
        }

        $envOrigin = getenv(self::TRUSTED_ORIGIN);
        if (is_string($envOrigin)) {
            return self::normalizeOrigin($envOrigin);
        }

        return null;
    }

    private static function normalizeOrigin(string $configuredOrigin): ?string
    {
        $origin = trim($configuredOrigin);
        if ($origin === '') {
            return null;
        }

        return rtrim($origin, '/');
    }

    public static function isPost(array $server): bool
    {
        return Request::isMethod($server, 'POST');
    }

    public static function isSameSite(array $server, ?string $trustedOrigin = null): bool
    {
        return Request::isSameSite($server, self::trustedOrigin($trustedOrigin));
    }

    public static function check(
        array $post,
        array $server,
        array $session,
        string $scope,
        ?string $trustedOrigin = null,
        string $field = Csrf::DEFAULT_FIELD
    ): array {
        if (!self::isPost($server)) {
            return self::outcome(false, 405, 'Method Not Allowed', ['Allow' => 'POST']);
        }

        if (!self::isSameSite($server, $trustedOrigin)) {
            return self::outcome(false, 403, 'Invalid request origin');
        }

        if (!Csrf::validateToken($post, $session, $scope, $field)) {
            return self::outcome(false, 403, 'Invalid CSRF token');
        }

        return self::outcome(true, 200, '');
    }

    public static function ensureOrFail(
        array $post,
        array $server,
        array $session,
        string $scope,
        ?string $trustedOrigin = null,
        string $field = Csrf::DEFAULT_FIELD
    ): ?array {
        $guard = self::check($post, $server, $session, $scope, $trustedOrigin, $field);
        if ($guard['allowed']) {
            return null;
        }

        return [
            'status' => $guard['status'],
            'body' => $guard['body'],
            'headers' => $guard['headers'],
        ];
    }

    private static function outcome(bool $allowed, int $status, string $body, array $headers = []): array
    {
        return [
            'allowed' => $allowed,
            'status' => $status,
            'body' => $body,
            'headers' => $headers,
        ];
    }
}
