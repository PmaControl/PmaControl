#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Library\Cve\CveCatalogBuilder;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload) && is_file('/srv/www/pmacontrol/vendor/autoload.php')) {
    $autoload = '/srv/www/pmacontrol/vendor/autoload.php';
}

require_once $autoload;

if (!class_exists(CveCatalogBuilder::class)) {
    require_once __DIR__ . '/../App/Library/Cve/CveCatalogBuilder.php';
}

final class CveCatalogRebuildCommand
{
    public static function run(array $argv): int
    {
        $options = self::options($argv);
        $builder = new CveCatalogBuilder(self::pdo($options));
        $summary = $builder->rebuild();

        if (!empty($options['verbose'])) {
            echo 'CVE catalog rebuilt: '
                . $summary['cves'] . ' CVEs, '
                . $summary['affected_versions'] . ' affected version rows, '
                . $summary['products'] . ' products'
                . PHP_EOL;
        }

        return 0;
    }

    public static function options(array $argv): array
    {
        $options = [
            'database' => getenv('PMACONTROL_DB_NAME') ?: 'pmacontrol',
            'host' => getenv('PMACONTROL_DB_HOST') ?: null,
            'socket' => getenv('PMACONTROL_DB_SOCKET') ?: null,
            'user' => getenv('PMACONTROL_DB_USER') ?: null,
            'password' => getenv('PMACONTROL_DB_PASSWORD') ?: null,
            'defaults-file' => null,
            'verbose' => true,
        ];

        foreach (array_slice($argv, 1) as $arg) {
            if ($arg === '--quiet') {
                $options['verbose'] = false;
                continue;
            }

            if ($arg === '--help' || $arg === '-h') {
                self::usage();
                exit(0);
            }

            if (!str_starts_with($arg, '--') || !str_contains($arg, '=')) {
                throw new InvalidArgumentException("Invalid argument: {$arg}");
            }

            [$key, $value] = explode('=', substr($arg, 2), 2);
            if (!array_key_exists($key, $options)) {
                throw new InvalidArgumentException("Unknown option: {$key}");
            }

            $options[$key] = $value;
        }

        $home = getenv('HOME') ?: '';
        $defaultsFile = $options['defaults-file'] ?: (is_file($home . '/.my.cnf') ? $home . '/.my.cnf' : null);
        if (is_string($defaultsFile) && $defaultsFile !== '') {
            $options = self::mergeMysqlDefaults($options, $defaultsFile);
        }

        return $options;
    }

    public static function pdo(array $options): PDO
    {
        $database = (string)$options['database'];
        $user = (string)($options['user'] ?? '');
        $password = (string)($options['password'] ?? '');

        if (!empty($options['socket'])) {
            $dsn = 'mysql:unix_socket=' . $options['socket'] . ';dbname=' . $database . ';charset=utf8mb4';
        } else {
            $host = (string)($options['host'] ?: 'localhost');
            $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';
        }

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private static function mergeMysqlDefaults(array $options, string $path): array
    {
        if (!is_readable($path)) {
            return $options;
        }

        $parsed = parse_ini_file($path, true, INI_SCANNER_RAW);
        if (!is_array($parsed)) {
            return $options;
        }

        $client = is_array($parsed['client'] ?? null) ? $parsed['client'] : [];
        foreach (['user', 'password', 'host', 'socket'] as $key) {
            if (($options[$key] ?? null) === null && isset($client[$key]) && is_scalar($client[$key])) {
                $options[$key] = self::unquoteMysqlOption((string)$client[$key]);
            }
        }

        return $options;
    }

    private static function unquoteMysqlOption(string $value): string
    {
        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    private static function usage(): void
    {
        echo "Usage: php script/cve_catalog_rebuild.php [--database=pmacontrol] [--defaults-file=/path/.my.cnf] [--quiet]\n";
    }
}

if (PHP_SAPI === 'cli' && realpath((string)($argv[0] ?? '')) === __FILE__) {
    try {
        exit(CveCatalogRebuildCommand::run($argv));
    } catch (Throwable $e) {
        fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
