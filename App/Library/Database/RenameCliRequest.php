<?php

declare(strict_types=1);

namespace App\Library\Database;

use App\Library\Security\Identifier;

final class RenameCliRequest
{
    private const VALUE_OPTIONS = ['server-id', 'host', 'port', 'user', 'password', 'old', 'new'];

    public static function fromArgv(array $argv, ?array $env = null): array
    {
        $env = $env ?? getenv();
        $parsed = self::parseArgv($argv);

        if ($parsed['help']) {
            return ['help' => true];
        }

        if (self::looksLikeDirectPositional($parsed['positionals'])) {
            return self::normalizeDirectPositional($parsed, $env);
        }

        if (isset($parsed['options']['host']) || isset($parsed['options']['user'])) {
            return self::normalizeDirectOptions($parsed, $env);
        }

        return self::normalizeServerOptions($parsed);
    }

    public static function parseArgv(array $argv): array
    {
        $options = [];
        $flags = [];
        $positionals = [];
        $args = array_values(array_slice($argv, 1));

        for ($i = 0; $i < count($args); $i++) {
            $arg = (string) $args[$i];

            if ($arg === '-h' || $arg === '--help') {
                return ['help' => true, 'options' => $options, 'flags' => $flags, 'positionals' => $positionals];
            }

            if (strncmp($arg, '--', 2) !== 0) {
                $positionals[] = $arg;
                continue;
            }

            $arg = substr($arg, 2);
            if ($arg === '') {
                throw new \InvalidArgumentException('Invalid empty option');
            }

            if (strpos($arg, '=') !== false) {
                [$key, $value] = explode('=', $arg, 2);
                self::assertKnownOption($key);
                $options[$key] = $value;
                continue;
            }

            self::assertKnownOption($arg);
            if (in_array($arg, self::VALUE_OPTIONS, true)) {
                if (!isset($args[$i + 1]) || strncmp((string) $args[$i + 1], '--', 2) === 0) {
                    throw new \InvalidArgumentException('Missing value for --'.$arg);
                }

                $options[$arg] = (string) $args[++$i];
                continue;
            }

            $flags[$arg] = true;
        }

        return ['help' => false, 'options' => $options, 'flags' => $flags, 'positionals' => $positionals];
    }

    public static function parseHostPort(string $host, ?string $port = null): array
    {
        $host = trim($host);
        $candidatePort = $port !== null && $port !== '' ? $port : null;

        if (preg_match('/^\[([^\]]+)\](?::([0-9]+))?$/', $host, $match) === 1) {
            $host = $match[1];
            if ($candidatePort === null && isset($match[2]) && $match[2] !== '') {
                $candidatePort = $match[2];
            }
        } elseif ($candidatePort === null && substr_count($host, ':') === 1 && preg_match('/^(.+):([0-9]+)$/', $host, $match) === 1) {
            $host = $match[1];
            $candidatePort = $match[2];
        }

        if (!Identifier::isHostName($host)) {
            throw new \InvalidArgumentException('Invalid host');
        }

        $portNumber = self::normalizePort($candidatePort ?? '3306');

        return ['host' => $host, 'port' => $portNumber];
    }

    public static function usage(): string
    {
        return implode("\n", [
            'Usage:',
            '  php bin/rename_database.php --server-id=12 --old=old_db --new=new_db [--adjust-privileges] [--force] [--dry-run]',
            '  php bin/rename_database.php 12 old_db new_db [adjust_privileges] [--force] [--dry-run]',
            '  php bin/rename_database.php --host=127.0.0.1:3306 --user=root --password=secret --old=old_db --new=new_db [--force] [--dry-run]',
            '  MYSQL_PWD=secret php bin/rename_database.php 127.0.0.1:3306 root old_db new_db [--force] [--dry-run]',
            '',
        ]);
    }

    private static function normalizeServerOptions(array $parsed): array
    {
        $options = $parsed['options'];
        $positionals = $parsed['positionals'];

        if (!isset($options['server-id']) && isset($positionals[0])) {
            $options['server-id'] = array_shift($positionals);
        }

        if (!isset($options['old']) && isset($positionals[0])) {
            $options['old'] = array_shift($positionals);
        }

        if (!isset($options['new']) && isset($positionals[0])) {
            $options['new'] = array_shift($positionals);
        }

        $adjustPrivileges = self::flag($parsed, 'adjust-privileges');
        if (isset($positionals[0]) && $positionals[0] !== '') {
            $adjustPrivileges = true;
        }

        self::assertRequired($options, ['server-id', 'old', 'new']);
        self::assertServerReference((string) $options['server-id']);
        self::assertDatabasePair((string) $options['old'], (string) $options['new']);

        return [
            'help' => false,
            'mode' => 'server',
            'server_id' => (string) $options['server-id'],
            'old' => (string) $options['old'],
            'new' => (string) $options['new'],
            'adjust_privileges' => $adjustPrivileges,
            'force' => self::flag($parsed, 'force'),
            'dry_run' => self::flag($parsed, 'dry-run'),
        ];
    }

    private static function normalizeDirectOptions(array $parsed, array $env): array
    {
        $options = $parsed['options'];
        $positionals = $parsed['positionals'];

        if (!isset($options['old']) && isset($positionals[0])) {
            $options['old'] = array_shift($positionals);
        }

        if (!isset($options['new']) && isset($positionals[0])) {
            $options['new'] = array_shift($positionals);
        }

        self::assertRequired($options, ['host', 'user', 'old', 'new']);
        self::assertDatabasePair((string) $options['old'], (string) $options['new']);
        self::assertUser((string) $options['user']);

        $hostPort = self::parseHostPort((string) $options['host'], isset($options['port']) ? (string) $options['port'] : null);

        return [
            'help' => false,
            'mode' => 'direct',
            'host' => $hostPort['host'],
            'port' => $hostPort['port'],
            'user' => (string) $options['user'],
            'password' => self::password($options, $env),
            'old' => (string) $options['old'],
            'new' => (string) $options['new'],
            'adjust_privileges' => self::flag($parsed, 'adjust-privileges'),
            'force' => self::flag($parsed, 'force'),
            'dry_run' => self::flag($parsed, 'dry-run'),
        ];
    }

    private static function normalizeDirectPositional(array $parsed, array $env): array
    {
        $positionals = $parsed['positionals'];
        $options = $parsed['options'];
        $password = null;

        $host = array_shift($positionals);
        $user = array_shift($positionals);

        if (count($positionals) >= 3) {
            $password = array_shift($positionals);
        }

        if ($password === null && isset($options['password'])) {
            $password = (string) $options['password'];
        }

        if ($password === null && isset($env['MYSQL_PWD']) && (string) $env['MYSQL_PWD'] !== '') {
            $password = (string) $env['MYSQL_PWD'];
        }

        if ($password === null) {
            throw new \InvalidArgumentException('Direct mode requires --password or MYSQL_PWD');
        }

        $old = array_shift($positionals);
        $new = array_shift($positionals);

        if ($old === null || $new === null) {
            throw new \InvalidArgumentException('Direct mode requires old and new database names');
        }

        self::assertDatabasePair((string) $old, (string) $new);
        self::assertUser((string) $user);

        $hostPort = self::parseHostPort((string) $host, isset($options['port']) ? (string) $options['port'] : null);

        return [
            'help' => false,
            'mode' => 'direct',
            'host' => $hostPort['host'],
            'port' => $hostPort['port'],
            'user' => (string) $user,
            'password' => $password,
            'old' => (string) $old,
            'new' => (string) $new,
            'adjust_privileges' => self::flag($parsed, 'adjust-privileges'),
            'force' => self::flag($parsed, 'force'),
            'dry_run' => self::flag($parsed, 'dry-run'),
        ];
    }

    private static function looksLikeDirectPositional(array $positionals): bool
    {
        if (count($positionals) < 4) {
            return false;
        }

        $first = (string) $positionals[0];

        return $first === 'localhost'
            || strpos($first, ':') !== false
            || preg_match('/^[0-9]+(?:\.[0-9]+){3}(?::[0-9]+)?$/', $first) === 1
            || strpos($first, '.') !== false;
    }

    private static function password(array $options, array $env): string
    {
        $password = isset($options['password']) ? (string) $options['password'] : (string) ($env['MYSQL_PWD'] ?? '');

        if (strpos($password, "\0") !== false) {
            throw new \InvalidArgumentException('Invalid password');
        }

        return $password;
    }

    private static function normalizePort(string $port): int
    {
        if (!ctype_digit($port)) {
            throw new \InvalidArgumentException('Invalid port');
        }

        $portNumber = (int) $port;
        if ($portNumber < 1 || $portNumber > 65535) {
            throw new \InvalidArgumentException('Invalid port');
        }

        return $portNumber;
    }

    private static function flag(array $parsed, string $name): bool
    {
        return !empty($parsed['flags'][$name]);
    }

    private static function assertRequired(array $options, array $required): void
    {
        foreach ($required as $option) {
            if (!isset($options[$option]) || (string) $options[$option] === '') {
                throw new \InvalidArgumentException('Missing required option --'.$option);
            }
        }
    }

    private static function assertKnownOption(string $option): void
    {
        $known = array_merge(self::VALUE_OPTIONS, ['help', 'force', 'dry-run', 'adjust-privileges']);
        if (!in_array($option, $known, true)) {
            throw new \InvalidArgumentException('Unknown option --'.$option);
        }
    }

    private static function assertDatabasePair(string $oldDatabase, string $newDatabase): void
    {
        if (!Identifier::isDatabaseName($oldDatabase) || !Identifier::isDatabaseName($newDatabase)) {
            throw new \InvalidArgumentException('Invalid database name');
        }
    }

    private static function assertUser(string $user): void
    {
        if (!Identifier::isAccountName($user)) {
            throw new \InvalidArgumentException('Invalid user');
        }
    }

    private static function assertServerReference(string $serverId): void
    {
        if (preg_match('/^[A-Za-z0-9_.:-]{1,255}$/', $serverId) !== 1) {
            throw new \InvalidArgumentException('Invalid --server-id value');
        }
    }
}
