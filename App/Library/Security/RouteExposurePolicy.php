<?php

declare(strict_types=1);

namespace App\Library\Security;

final class RouteExposurePolicy
{
    /**
     * Public controller methods are web-routable in Glial. Keep this list focused
     * on routes that are proven unsafe to expose over HTTP.
     *
     * @var array<string,string>
     */
    private const DENIED_WEB_ROUTES = [
        'alter/dropsp' => 'Legacy maintenance route drops stored procedures on remote MySQL servers.',
        'alter/slave' => 'Legacy maintenance route changes replication state on remote MySQL servers.',
        'alter/droproot' => 'Legacy maintenance route drops root accounts on remote MySQL servers.',
        'alter/user' => 'Legacy maintenance route replays MySQL grants on remote MySQL servers.',
        'webservice/decrypt' => 'Legacy route exposes direct URL-driven CRYPT_KEY decryption.',
        'mysql/passwd' => 'Legacy route enumerates mysql.user password hashes across configured servers.',
        'server/passwd' => 'Legacy route decrypts a password from the URL; Server/password is the guarded POST endpoint.',
    ];

    /**
     * @return array<string,string>
     */
    public static function deniedWebRoutes(): array
    {
        return self::DENIED_WEB_ROUTES;
    }

    public static function isDeniedWebRoute(string $controller, string $action): bool
    {
        return self::denialReason($controller, $action) !== null;
    }

    public static function denialReason(string $controller, string $action): ?string
    {
        return self::DENIED_WEB_ROUTES[self::resourceKey($controller, $action)] ?? null;
    }

    public static function resourceName(string $controller, string $action): string
    {
        return trim($controller).'/'.trim($action);
    }

    private static function resourceKey(string $controller, string $action): string
    {
        return strtolower(self::resourceName($controller, $action));
    }
}
