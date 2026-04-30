<?php

declare(strict_types=1);

namespace App\Library\Security;

final class RouteExposurePolicy
{
    /**
     * Controllers that are used as CLI/daemon entrypoints only. Blocking them
     * here removes their public methods from the HTTP surface while preserving
     * direct CLI dispatch.
     *
     * @var array<string,string>
     */
    private const DENIED_WEB_CONTROLLERS = [
        'aspirateur' => 'Collector controller is CLI/worker-only; HTTP execution can trigger expensive remote probes.',
        'ventilateur' => 'Legacy ZeroMQ worker prototype is CLI-only and can block HTTP workers.',
        'control' => 'Metric maintenance controller is CLI-only and exposes partition/table rebuild operations.',
        'integrate' => 'Shared-memory integration controller is CLI-only and processes collector payloads.',
        'integratelog' => 'Log integration controller is CLI-only and processes local collector payloads.',
        'aggregatemetric' => 'Metric rollup controller is CLI-only and runs aggregation jobs.',
        'demo' => 'Legacy lab controller runs replication setup, GRANTs and shell commands; HTTP execution is unsafe.',
        'masterslave' => 'Removed duplicate lab replication controller; keep blocked as a tombstone if it is reintroduced.',
    ];

    /**
     * Public controller methods are web-routable in Glial. Keep this list focused
     * on routes that are proven unsafe to expose over HTTP.
     *
     * @var array<string,string>
     */
    private const DENIED_WEB_ROUTES = [
        // Tombstones for the removed Alter controller (#516): keep these routes blocked
        // if destructive legacy maintenance actions are accidentally reintroduced.
        'alter/dropsp' => 'Legacy maintenance route drops stored procedures on remote MySQL servers.',
        'alter/slave' => 'Legacy maintenance route changes replication state on remote MySQL servers.',
        'alter/droproot' => 'Legacy maintenance route drops root accounts on remote MySQL servers.',
        'alter/user' => 'Legacy maintenance route replays MySQL grants on remote MySQL servers.',
        'webservice/decrypt' => 'Legacy route exposes direct URL-driven CRYPT_KEY decryption.',
        'mysql/passwd' => 'Legacy route enumerates mysql.user password hashes across configured servers.',
        'server/passwd' => 'Legacy route decrypts a password from the URL; Server/password is the guarded POST endpoint.',
        'listener/recordevent' => 'Listener/recordEvent is an internal event helper and must not be triggered over HTTP.',
        'listener/closeevent' => 'Listener/closeEvent is an internal event helper and must not be triggered over HTTP.',
        'listener/load' => 'Listener/load is a framework/internal initialization hook and must not be triggered over HTTP.',
        'listener/init' => 'Listener/init is a framework/internal initialization hook and must not be triggered over HTTP.',
        'listener/before' => 'Listener controller is a collector worker; only Listener/status is retained for diagnostics.',
        'listener/checkall' => 'Listener/checkAll is a daemon worker entrypoint and must not be triggered over HTTP.',
        'listener/check' => 'Listener/check computes pending listener work and must not be triggered over HTTP.',
        'listener/getupdatetodo' => 'Listener/getUpdateTodo prepares worker updates and must not be triggered over HTTP.',
        'listener/updatelistener' => 'Listener/updateListener mutates listener state and must not be triggered over HTTP.',
        'listener/updatedatabase' => 'Listener/updateDatabase mutates listener storage and must not be triggered over HTTP.',
        'listener/extract' => 'Listener/extract is an internal collector helper and must not be triggered over HTTP.',
        'listener/updateelem' => 'Listener/updateElem mutates collector tables and must not be triggered over HTTP.',
        'listener/test1' => 'Listener/test1 is a legacy diagnostic worker method and must not be triggered over HTTP.',
        'listener/test2' => 'Listener/test2 is a legacy diagnostic worker method and must not be triggered over HTTP.',
        'listener/test4' => 'Listener/test4 is a legacy diagnostic worker method and must not be triggered over HTTP.',
        'listener/afterupdatevariable' => 'Listener/afterUpdateVariable is a collector post-update hook and must not be triggered over HTTP.',
        'listener/resetall' => 'Listener/resetAll rewrites listener cursors and must not be triggered over HTTP.',
        'listener/index' => 'Listener/index has no UI contract and the worker controller must not be browsed directly.',
        'listener/test5' => 'Listener/test5 is a legacy diagnostic worker method and must not be triggered over HTTP.',
        'listener/purgeall' => 'Listener/purgeAll is an internal purge helper and must not be triggered over HTTP.',
    ];

    /**
     * @return array<string,string>
     */
    public static function deniedWebControllers(): array
    {
        return self::DENIED_WEB_CONTROLLERS;
    }

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
        return self::DENIED_WEB_ROUTES[self::resourceKey($controller, $action)]
            ?? self::DENIED_WEB_CONTROLLERS[self::controllerKey($controller)]
            ?? null;
    }

    public static function resourceName(string $controller, string $action): string
    {
        return trim($controller).'/'.trim($action);
    }

    private static function resourceKey(string $controller, string $action): string
    {
        return strtolower(self::resourceName($controller, $action));
    }

    private static function controllerKey(string $controller): string
    {
        return strtolower(trim($controller));
    }
}
