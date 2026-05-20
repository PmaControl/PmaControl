<?php

declare(strict_types=1);

namespace App\Library\Database;

use App\Library\ShellCommand;
use App\Library\Security\Identifier;

final class RefreshShellCommand
{
    public static function buildDumpCommand(
        string $host,
        string $login,
        string $password,
        int $port,
        string $database,
        string $path
    ): string {
        $command = ShellCommand::of('mydumper')
            ->option('-h', $host)
            ->option('-u', $login)
            ->option('-p', $password)
            ->intOption('-P', $port);

        if ($database !== 'ALL') {
            $command->option('-B', $database);
        }

        return $command
            ->flag('-G')
            ->flag('-E')
            ->flag('-R')
            ->option('-o', $path)
            ->mergeStderrIntoStdout()
            ->toString().' ';
    }

    public static function buildLoadCommand(
        string $host,
        string $login,
        string $password,
        int $port,
        string $database,
        string $path
    ): string {
        $command = ShellCommand::of('myloader')
            ->option('-h', $host)
            ->option('-u', $login)
            ->option('-p', $password)
            ->intOption('-P', $port)
            ->flag('-o');

        if ($database !== 'ALL') {
            $command->option('-s', $database);
        }

        return $command
            ->option('-d', $path)
            ->mergeStderrIntoStdout()
            ->toString();
    }

    public static function buildWorkerCommand(
        string $php,
        string $index,
        string $controller,
        int $sourceServerId,
        int $targetServerId,
        array $databases,
        string $path,
        string $uuid,
        string $log,
        string $logError,
        bool $debug = false
    ): string {
        $args = array(
            $php,
            $index,
            $controller,
            'databaseRefresh',
            (string) $sourceServerId,
            (string) $targetServerId,
            implode(',', $databases),
            $path,
            $uuid,
        );

        $cmd = ShellCommand::fromArguments($args);
        if ($debug === true) {
            $cmd->flag('--debug');
        }

        return $cmd
            ->redirect(1, '>', $log)
            ->redirect(2, '>', $logError)
            ->inBackgroundCapturingPid()
            ->toString();
    }

    public static function removeMysqlMetadataFiles(string $path): void
    {
        if (!Identifier::isSafeAbsolutePath($path)) {
            throw new \InvalidArgumentException('Invalid database refresh path');
        }

        foreach (glob(rtrim($path, '/').'/mysql.*.sql') ?: array() as $file) {
            if (is_file($file) || is_link($file)) {
                unlink($file);
            }
        }
    }
}
