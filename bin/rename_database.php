#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Controller\Database;
use App\Library\Database\RenameCliRequest;
use App\Library\Database\Renamer;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Config;

require_once dirname(__DIR__).'/vendor/autoload.php';

final class DatabaseRenameCli
{
    public static function main(array $argv, ?array $env = null): int
    {
        try {
            $request = RenameCliRequest::fromArgv($argv, $env);

            if (!empty($request['help'])) {
                fwrite(STDOUT, RenameCliRequest::usage());
                return 0;
            }

            self::bootstrap();

            if ($request['mode'] === 'server') {
                $renamed = self::runServerMode($request);
            } else {
                $renamed = self::runDirectMode($request);
            }

            echo (!empty($request['dry_run']) ? 'Dry-run tables: ' : 'Renamed tables: ').(int) $renamed.PHP_EOL;

            return 0;
        } catch (\Throwable $e) {
            fwrite(STDERR, $e->getMessage().PHP_EOL.PHP_EOL);
            fwrite(STDERR, RenameCliRequest::usage());
            return 1;
        }
    }

    private static function runServerMode(array $request): int
    {
        $controller = new Database('', '', []);
        $param = [
            $request['server_id'],
            $request['old'],
            $request['new'],
            !empty($request['adjust_privileges']) ? '1' : '',
        ];

        if (!empty($request['force'])) {
            $param[] = '--force';
        }

        if (!empty($request['dry_run'])) {
            $db = Sgbd::sql(DB_DEFAULT);
            $serverRef = $db->sql_real_escape_string((string) $request['server_id']);
            $sql = "SELECT * FROM `mysql_server` where `id`='".$serverRef."'"
                ." UNION ALL "
                ."SELECT * FROM `mysql_server` where `display_name`='".$serverRef."'";
            $res = $db->sql_query($sql);

            $renamed = 0;
            while ($server = $db->sql_fetch_object($res)) {
                $renamed = Renamer::rename(
                    Sgbd::sql($server->name),
                    (string) $request['old'],
                    (string) $request['new'],
                    !empty($request['adjust_privileges']),
                    !empty($request['force']),
                    $request['server_id'],
                    true
                );
            }

            return $renamed;
        }

        return (int) $controller->move($param);
    }

    private static function runDirectMode(array $request): int
    {
        $connectionName = 'database_rename_'.str_replace('.', '_', uniqid('', true));
        Sgbd::setConfig([
            $connectionName => [
                'driver' => 'mysql',
                'hostname' => $request['host'],
                'port' => (string) $request['port'],
                'user' => $request['user'],
                'password' => $request['password'],
                'crypted' => '0',
                'database' => '',
                'ssl' => '0',
                'timeout' => '5',
            ],
        ]);

        return Renamer::rename(
            Sgbd::sql($connectionName),
            (string) $request['old'],
            (string) $request['new'],
            !empty($request['adjust_privileges']),
            !empty($request['force']),
            null,
            !empty($request['dry_run'])
        );
    }

    private static function bootstrap(): void
    {
        if (!defined('IS_CLI')) {
            define('IS_CLI', PHP_SAPI === 'cli');
        }

        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        if (!defined('ROOT')) {
            define('ROOT', dirname(__DIR__));
        }

        if (!defined('APP_DIR')) {
            define('APP_DIR', ROOT.DS.'App');
        }

        if (!defined('TMP')) {
            define('TMP', ROOT.DS.'tmp'.DS);
        }

        if (!defined('DATA')) {
            define('DATA', ROOT.DS.'data'.DS);
        }

        if (!defined('CONFIG')) {
            define('CONFIG', ROOT.DS.'configuration'.DS);
        }

        if (!defined('LIBRARY')) {
            define('LIBRARY', ROOT.DS.'library'.DS);
        }

        if (!defined('CORE_PATH')) {
            define('CORE_PATH', ROOT.DS);
        }

        if (!defined('LIB')) {
            define('LIB', CORE_PATH.'lib'.DS);
        }

        if (!defined('WEBROOT_DIR')) {
            define('WEBROOT_DIR', 'Webroot'.DS);
        }

        if (!defined('GLIAL_INDEX')) {
            define('GLIAL_INDEX', ROOT.DS.'App'.DS.'Webroot'.DS.'index.php');
        }

        require_once ROOT.DS.'vendor'.DS.'autoload.php';
        require_once ROOT.DS.'App'.DS.'Webroot'.DS.'Basic.php';

        $config = new Config();
        $config->load(CONFIG);
        Sgbd::setConfig($config->get('db'));
    }
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    exit(DatabaseRenameCli::main($argv, getenv()));
}
