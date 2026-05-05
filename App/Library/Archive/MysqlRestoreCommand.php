<?php

declare(strict_types=1);

namespace App\Library\Archive;

use App\Library\ShellCommand;
use App\Library\Security\Identifier;

final class MysqlRestoreCommand
{
    private const SQL_MODE_STATEMENT = 'SET sql_mode="ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";';

    public static function prefixDumpWithSqlMode(string $dumpPath): void
    {
        self::assertReadableFile($dumpPath, 'Dump file');

        $directory = dirname($dumpPath);
        if (!is_dir($directory) || !is_writable($directory)) {
            throw new \InvalidArgumentException('Dump directory is not writable');
        }

        $temporaryPath = tempnam($directory, '.pmacontrol-restore-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create temporary dump file');
        }

        $input = null;
        $output = null;
        $renamed = false;

        try {
            $input = fopen($dumpPath, 'rb');
            if ($input === false) {
                throw new \RuntimeException('Unable to read dump file');
            }

            $output = fopen($temporaryPath, 'wb');
            if ($output === false) {
                throw new \RuntimeException('Unable to write temporary dump file');
            }

            $prefix = self::sqlModePrefix();
            if (fwrite($output, $prefix) !== strlen($prefix)) {
                throw new \RuntimeException('Unable to write SQL mode prefix');
            }

            if (stream_copy_to_stream($input, $output) === false) {
                throw new \RuntimeException('Unable to copy dump file');
            }

            fflush($output);
            chmod($temporaryPath, self::fileMode($dumpPath));

            if (!rename($temporaryPath, $dumpPath)) {
                throw new \RuntimeException('Unable to replace dump file');
            }
            $renamed = true;
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            if ($renamed === false) {
                self::deleteFile($temporaryPath);
            }
        }
    }

    public static function sqlModePrefix(): string
    {
        return self::SQL_MODE_STATEMENT.PHP_EOL;
    }

    public static function createClientDefaultsFile(array $conf, ?string $temporaryDirectory = null): string
    {
        $host = self::requireOptionValue($conf['hostname'] ?? '', 'hostname');
        $user = self::requireOptionValue($conf['user'] ?? '', 'user');
        $password = self::requireOptionValue($conf['password'] ?? '', 'password', true);
        $port = self::normalizePort($conf['port'] ?? 3306);

        $temporaryPath = self::createPrivateTemporaryFile('mysqlcli_', $temporaryDirectory);

        $content = '[client]'.PHP_EOL
            .'host='.self::quoteOptionValue($host).PHP_EOL
            .'port='.$port.PHP_EOL
            .'user='.self::quoteOptionValue($user).PHP_EOL
            .'password='.self::quoteOptionValue($password).PHP_EOL
            .'protocol=TCP'.PHP_EOL;

        if (file_put_contents($temporaryPath, $content, LOCK_EX) === false) {
            self::deleteFile($temporaryPath);
            throw new \RuntimeException('Unable to write MySQL client defaults file');
        }

        chmod($temporaryPath, 0600);

        return $temporaryPath;
    }

    public static function createErrorLogFile(?string $temporaryDirectory = null): string
    {
        return self::createPrivateTemporaryFile('mysql_load_', $temporaryDirectory);
    }

    public static function buildLoadCommand(
        string $defaultsFile,
        string $dumpPath,
        string $database,
        string $logPath
    ): string {
        self::assertReadableFile($dumpPath, 'Dump file');
        self::assertReadableFile($defaultsFile, 'MySQL client defaults file');
        self::assertSafeLogPath($logPath);
        if (!Identifier::isDatabaseName($database)) {
            throw new \InvalidArgumentException('Invalid database name');
        }

        $pv = ShellCommand::of('pv')
            ->flag('--')
            ->arg($dumpPath);
        $mysql = ShellCommand::of('mysql')
            ->option('--defaults-extra-file', $defaultsFile, '=')
            ->option('--database', $database, '=');

        return $pv
            ->pipeTo($mysql)
            ->redirect(2, '>', $logPath)
            ->toString();
    }

    public static function deleteFile(?string $path): void
    {
        if ($path === null || $path === '' || strpos($path, "\0") !== false) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            @unlink($path);
        }
    }

    private static function createPrivateTemporaryFile(string $prefix, ?string $temporaryDirectory): string
    {
        $directory = self::normalizeTemporaryDirectory($temporaryDirectory);
        $oldUmask = umask(0077);
        try {
            $temporaryPath = tempnam($directory, $prefix);
        } finally {
            umask($oldUmask);
        }

        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create temporary file');
        }

        chmod($temporaryPath, 0600);

        return $temporaryPath;
    }

    private static function normalizeTemporaryDirectory(?string $temporaryDirectory): string
    {
        if ($temporaryDirectory === null) {
            $temporaryDirectory = defined('TMP') ? TMP : sys_get_temp_dir().DIRECTORY_SEPARATOR;
        }

        $temporaryDirectory = rtrim($temporaryDirectory, DIRECTORY_SEPARATOR);
        if ($temporaryDirectory === '' || strpos($temporaryDirectory, "\0") !== false) {
            throw new \InvalidArgumentException('Invalid temporary directory');
        }

        $realPath = realpath($temporaryDirectory);
        if ($realPath === false || !is_dir($realPath) || !is_writable($realPath)) {
            throw new \InvalidArgumentException('Temporary directory is not writable');
        }

        return $realPath;
    }

    private static function assertReadableFile(string $path, string $label): void
    {
        if ($path === '' || strpos($path, "\0") !== false || !is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException($label.' is not readable');
        }
    }

    private static function assertSafeLogPath(string $path): void
    {
        if ($path === '' || strpos($path, "\0") !== false) {
            throw new \InvalidArgumentException('Invalid MySQL error log path');
        }
    }

    private static function requireOptionValue($value, string $label, bool $allowEmpty = false): string
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Invalid MySQL '.$label);
        }

        $value = (string) $value;
        if (($value === '' && !$allowEmpty) || preg_match('/[\x00\r\n]/', $value) === 1) {
            throw new \InvalidArgumentException('Invalid MySQL '.$label);
        }

        return $value;
    }

    private static function normalizePort($port): int
    {
        if (!is_scalar($port) || preg_match('/^[0-9]+$/', (string) $port) !== 1) {
            throw new \InvalidArgumentException('Invalid MySQL port');
        }

        $port = (int) $port;
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('Invalid MySQL port');
        }

        return $port;
    }

    private static function quoteOptionValue(string $value): string
    {
        return '"'.str_replace(array('\\', '"'), array('\\\\', '\\"'), $value).'"';
    }

    private static function fileMode(string $path): int
    {
        $mode = fileperms($path);
        if ($mode === false) {
            return 0600;
        }

        return $mode & 0777;
    }
}
