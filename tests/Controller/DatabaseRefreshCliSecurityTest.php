<?php

declare(strict_types=1);

use App\Library\Database\RefreshShellCommand;
use PHPUnit\Framework\TestCase;

final class DatabaseRefreshCliSecurityTest extends TestCase
{
    public function testDatabaseRefreshUsesSafeDirectoryAndNoRmShell(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $databaseRefreshStart = strpos($controller, 'public function databaseRefresh($param)');
        $databaseDumpStart = strpos($controller, 'public function databaseDump($param)', (int) $databaseRefreshStart);

        $this->assertNotFalse($databaseRefreshStart);
        $this->assertNotFalse($databaseDumpStart);
        $databaseRefreshBody = substr(
            $controller,
            (int) $databaseRefreshStart,
            (int) $databaseDumpStart - (int) $databaseRefreshStart
        );

        $this->assertStringContainsString('use App\\Library\\Filesystem\\SafeDirectory;', $controller);
        $this->assertStringContainsString("SafeDirectory::buildTemporaryChildPath(", $databaseRefreshBody);
        $this->assertStringContainsString("'pmacontrol-refresh-'", $databaseRefreshBody);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeSingle($param[0] ?? null)', $databaseRefreshBody);
        $this->assertStringContainsString('Identifier::normalizeDatabaseNameList((string) $rawDatabases)', $databaseRefreshBody);
        $this->assertStringContainsString('Uuid::isValid($uuid)', $databaseRefreshBody);
        $this->assertStringContainsString('finally', $databaseRefreshBody);
        $this->assertStringContainsString('SafeDirectory::removeTree($directory)', $databaseRefreshBody);
        $this->assertStringNotContainsString('$directory = $path."/".uniqid();', $databaseRefreshBody);
        $this->assertStringNotContainsString('shell_exec("rm -rvf ".$directory)', $databaseRefreshBody);
    }

    public function testDatabaseDumpAndLoadCommandsEscapeShellArguments(): void
    {
        $dump = RefreshShellCommand::buildDumpCommand(
            '10.0.0.1;id',
            "root';id",
            "pa ss;id",
            3306,
            "app_db;id",
            "/tmp/pma refresh;id"
        );

        $this->assertSame(
            'mydumper -h '.escapeshellarg('10.0.0.1;id')
            .' -u '.escapeshellarg("root';id")
            .' -p '.escapeshellarg('pa ss;id')
            .' -P 3306 -B '.escapeshellarg('app_db;id')
            .' -G -E -R -o '.escapeshellarg('/tmp/pma refresh;id')
            .' 2>&1 ',
            $dump
        );

        $load = RefreshShellCommand::buildLoadCommand(
            '10.0.0.2;id',
            "loader';id",
            "secret;id",
            3307,
            "target_db;id",
            "/tmp/pma load;id"
        );

        $this->assertSame(
            'myloader -h '.escapeshellarg('10.0.0.2;id')
            .' -u '.escapeshellarg("loader';id")
            .' -p '.escapeshellarg('secret;id')
            .' -P 3307 -o -s '.escapeshellarg('target_db;id')
            .' -d '.escapeshellarg('/tmp/pma load;id')
            .' 2>&1',
            $load
        );
    }

    public function testDatabaseRefreshWorkerCommandEscapesEveryArgument(): void
    {
        $cmd = RefreshShellCommand::buildWorkerCommand(
            '/usr/bin/php',
            '/srv/www/pma control/index.php',
            'Database',
            7,
            8,
            array('app_db', 'log-db'),
            "/tmp/pma refresh;id",
            "uuid';id",
            '/tmp/pma control/refresh.log',
            '/tmp/pma control/refresh.error.log',
            true
        );

        $expected = implode(' ', array_map('escapeshellarg', array(
            '/usr/bin/php',
            '/srv/www/pma control/index.php',
            'Database',
            'databaseRefresh',
            '7',
            '8',
            'app_db,log-db',
            "/tmp/pma refresh;id",
            "uuid';id",
        )))
            .' --debug > '.escapeshellarg('/tmp/pma control/refresh.log')
            .' 2> '.escapeshellarg('/tmp/pma control/refresh.error.log')
            .' & echo $!';

        $this->assertSame($expected, $cmd);
    }

    public function testMysqlMetadataCleanupUsesGlobWithoutShell(): void
    {
        $dir = sys_get_temp_dir().'/pmacontrol-db-refresh-cli-test-'.uniqid('', true);
        mkdir($dir, 0700, true);
        file_put_contents($dir.'/mysql.schema.sql', 'drop me');
        file_put_contents($dir.'/mysql.keep.txt', 'keep me');
        file_put_contents($dir.'/app.sql', 'keep me too');

        try {
            RefreshShellCommand::removeMysqlMetadataFiles($dir);

            $this->assertFileDoesNotExist($dir.'/mysql.schema.sql');
            $this->assertFileExists($dir.'/mysql.keep.txt');
            $this->assertFileExists($dir.'/app.sql');
        } finally {
            @unlink($dir.'/mysql.schema.sql');
            @unlink($dir.'/mysql.keep.txt');
            @unlink($dir.'/app.sql');
            @rmdir($dir);
        }
    }
}
