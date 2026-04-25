<?php

namespace App\Library;

final class CliRootGuard
{
    public const DEFAULT_TARGET_USER = 'www-data';

    /**
     * @param array<int,string> $argv
     * @param array<string,string|false|null> $env
     */
    public static function shouldReexec(bool $isCli, int $effectiveUserId, array $argv, array $env): bool
    {
        if (!$isCli || $effectiveUserId !== 0) {
            return false;
        }

        if (!empty($env['PMACONTROL_CLI_REEXEC']) || !empty($env['PMACONTROL_ALLOW_ROOT'])) {
            return false;
        }

        return !self::shouldKeepRoot($argv, $env);
    }

    /**
     * @param array<int,string> $argv
     * @param array<string,string|false|null> $env
     */
    public static function shouldKeepRoot(array $argv, array $env = []): bool
    {
        if (!empty($env['PMACONTROL_CLI_KEEP_ROOT'])) {
            return true;
        }

        $controller = strtolower((string)($argv[1] ?? ''));
        $action = strtolower((string)($argv[2] ?? ''));

        if ($controller === 'install') {
            return true;
        }

        $rootActions = [
            'webservice' => [
                'addaccount',
                'exportmysqlserverplain',
                'importmysqlserverplain',
            ],
        ];

        return isset($rootActions[$controller]) && in_array($action, $rootActions[$controller], true);
    }

    /**
     * @param array<int,string> $argv
     * @return array<int,string>
     */
    public static function buildPhpCommand(array $argv, string $scriptFilename, string $phpBinary): array
    {
        $args = $argv;
        array_shift($args);

        return array_values(array_merge([$phpBinary, $scriptFilename], $args));
    }

    /**
     * @param array<string,string|false|null> $env
     */
    public static function targetUser(array $env): string
    {
        $targetUser = trim((string)($env['PMACONTROL_CLI_USER'] ?? ''));

        return $targetUser !== '' ? $targetUser : self::DEFAULT_TARGET_USER;
    }
}
