<?php

declare(strict_types=1);

namespace App\Library\Database;

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
        $toDump = '';
        if ($database !== 'ALL') {
            $toDump = ' -B '.escapeshellarg($database);
        }

        return 'mydumper -h '.escapeshellarg($host)
            .' -u '.escapeshellarg($login)
            .' -p '.escapeshellarg($password)
            .' -P '.(int) $port
            .$toDump
            .' -G -E -R -o '.escapeshellarg($path)
            .' 2>&1 ';
    }

    public static function buildLoadCommand(
        string $host,
        string $login,
        string $password,
        int $port,
        string $database,
        string $path
    ): string {
        $toDump = '';
        if ($database !== 'ALL') {
            $toDump = ' -s '.escapeshellarg($database);
        }

        return 'myloader -h '.escapeshellarg($host)
            .' -u '.escapeshellarg($login)
            .' -p '.escapeshellarg($password)
            .' -P '.(int) $port
            .' -o'.$toDump.' -d '.escapeshellarg($path)
            .' 2>&1';
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

        $cmd = implode(' ', array_map('escapeshellarg', $args));
        if ($debug === true) {
            $cmd .= ' --debug';
        }

        return $cmd.' > '.escapeshellarg($log).' 2> '.escapeshellarg($logError).' & echo $!';
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
