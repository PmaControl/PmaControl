<?php

declare(strict_types=1);

use App\Library\Archive\MysqlRestoreCommand;
use PHPUnit\Framework\TestCase;

final class MysqlRestoreCommandTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir().'/pmacontrol-mysql-restore-test-'.uniqid('', true);
        mkdir($this->baseDir, 0700, true);
    }

    protected function tearDown(): void
    {
        $this->removePath($this->baseDir);
    }

    public function testPrefixDumpWithSqlModePrependsStatementWithoutShell(): void
    {
        $dump = $this->baseDir.'/dump.sql';
        file_put_contents($dump, "CREATE TABLE t (id int);\nINSERT INTO t VALUES (1);\n");
        chmod($dump, 0640);

        MysqlRestoreCommand::prefixDumpWithSqlMode($dump);

        $contents = (string) file_get_contents($dump);
        $this->assertStringStartsWith(MysqlRestoreCommand::sqlModePrefix(), $contents);
        $this->assertStringContainsString("CREATE TABLE t (id int);\n", $contents);
        $this->assertSame(0640, fileperms($dump) & 0777);
    }

    public function testClientDefaultsFileIsPrivateAndQuotesOptionValues(): void
    {
        $defaults = MysqlRestoreCommand::createClientDefaultsFile(
            array(
                'hostname' => 'db.internal',
                'port' => '3307',
                'user' => 'loader-user',
                'password' => 'pa"ss\\word;$(id)',
            ),
            $this->baseDir
        );

        $contents = (string) file_get_contents($defaults);

        $this->assertSame(0600, fileperms($defaults) & 0777);
        $this->assertStringContainsString('host="db.internal"', $contents);
        $this->assertStringContainsString('port=3307', $contents);
        $this->assertStringContainsString('user="loader-user"', $contents);
        $this->assertStringContainsString('password="pa\\"ss\\\\word;$(id)"', $contents);
        $this->assertStringContainsString('protocol=TCP', $contents);
    }

    public function testClientDefaultsFileRejectsControlCharacters(): void
    {
        $invalidConfigs = array(
            array('hostname' => "db\ninternal", 'port' => 3306, 'user' => 'loader', 'password' => 'secret'),
            array('hostname' => 'db.internal', 'port' => 3306, 'user' => "loader\ruser", 'password' => 'secret'),
            array('hostname' => 'db.internal', 'port' => 3306, 'user' => 'loader', 'password' => "secret\0value"),
            array('hostname' => 'db.internal', 'port' => 0, 'user' => 'loader', 'password' => 'secret'),
            array('hostname' => 'db.internal', 'port' => 65536, 'user' => 'loader', 'password' => 'secret'),
        );

        foreach ($invalidConfigs as $conf) {
            try {
                MysqlRestoreCommand::createClientDefaultsFile($conf, $this->baseDir);
                $this->fail('Invalid MySQL restore configuration was accepted');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringStartsWith('Invalid MySQL', $exception->getMessage());
            }
        }
    }

    public function testClientDefaultsFileAcceptsEmptyPasswordForCompatibility(): void
    {
        $defaults = MysqlRestoreCommand::createClientDefaultsFile(
            array(
                'hostname' => 'db.internal',
                'port' => 3306,
                'user' => 'loader',
                'password' => '',
            ),
            $this->baseDir
        );

        $contents = (string) file_get_contents($defaults);

        $this->assertStringContainsString('password=""', $contents);
    }

    public function testErrorLogFileIsPrivate(): void
    {
        $log = MysqlRestoreCommand::createErrorLogFile($this->baseDir);

        $this->assertFileExists($log);
        $this->assertSame(0600, fileperms($log) & 0777);
    }

    public function testBuildLoadCommandEscapesArgumentsAndKeepsPasswordOut(): void
    {
        $dump = $this->baseDir.'/dump file;id.sql';
        $defaults = $this->baseDir.'/mysql defaults.cnf';
        $log = $this->baseDir.'/mysql load;id.log';
        file_put_contents($dump, 'SELECT 1;');
        file_put_contents($defaults, 'password=secret;id');
        file_put_contents($log, '');

        $cmd = MysqlRestoreCommand::buildLoadCommand($defaults, $dump, 'archive_db', $log);

        $this->assertSame(
            'pv -- '.escapeshellarg($dump)
            .' | mysql --defaults-extra-file='.escapeshellarg($defaults)
            .' --database='.escapeshellarg('archive_db')
            .' 2> '.escapeshellarg($log),
            $cmd
        );
        $this->assertStringNotContainsString('secret;id', $cmd);
    }

    public function testBuildLoadCommandRejectsInvalidDatabaseName(): void
    {
        $dump = $this->baseDir.'/dump.sql';
        $defaults = $this->baseDir.'/mysql.cnf';
        $log = $this->baseDir.'/mysql.log';
        file_put_contents($dump, 'SELECT 1;');
        file_put_contents($defaults, '[client]');

        $this->expectException(InvalidArgumentException::class);

        MysqlRestoreCommand::buildLoadCommand($defaults, $dump, 'archive_db;id', $log);
    }

    private function removePath(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            @unlink($path);
            return;
        }

        foreach (array_diff(scandir($path) ?: array(), array('.', '..')) as $entry) {
            $this->removePath($path.'/'.$entry);
        }

        @rmdir($path);
    }
}
