<?php

declare(strict_types=1);

if (!defined('TMP')) {
    define('TMP', '/tmp/');
}

use App\Library\EngineV4;
use PHPUnit\Framework\TestCase;

final class EngineV4Test extends TestCase
{
    public function testGetFileLockVariantsEmbedTsFileAndPid(): void
    {
        $lock = EngineV4::getFileLock('mysql_global_variable', 1234);
        $md5 = EngineV4::getFileMd5('mysql_global_variable', 1234);
        $pid = EngineV4::getFilePid('mysql_global_variable', 1234);

        $this->assertStringContainsString('mysql_global_variable::1234', $lock);
        $this->assertStringEndsWith('.lock', $lock);
        $this->assertStringEndsWith('.md5', $md5);
        $this->assertStringEndsWith('.pid', $pid);
    }

    public function testGetPidExtractsPidFromGeneratedFileName(): void
    {
        $file = EngineV4::getFilePid('mysql_global_variable', 5678);

        $this->assertSame('5678', EngineV4::getPid($file));
    }

    public function testMd5GlobPatternCanTargetOneServerOrAllServers(): void
    {
        $this->assertSame(
            '/tmp/md5/proxysql_runtime_mysql_servers::181.md5',
            EngineV4::getMd5GlobPattern('proxysql_runtime_mysql_servers', 181)
        );
        $this->assertSame(
            '/tmp/md5/proxysql_runtime_mysql_servers::*.md5',
            EngineV4::getMd5GlobPattern('proxysql_runtime_mysql_servers')
        );
    }

    public function testProxySqlStructuralFilesCoverTopologyInputs(): void
    {
        $files = EngineV4::getProxySqlStructuralFiles();

        $this->assertContains('proxysql_runtime_mysql_servers', $files);
        $this->assertContains('proxysql_runtime_mysql_group_replication_hostgroups', $files);
        $this->assertContains('proxysql_runtime_mysql_group_replication_hostgroup', $files);
        $this->assertContains('proxysql_runtime_mysql_replication_hostgroups', $files);
        $this->assertContains('proxysql_runtime_mysql_galera_hostgroups', $files);
        $this->assertContains('proxysql_runtime_proxysql_servers', $files);
    }

    public function testCleanMd5FilesCanTargetOnePid(): void
    {
        $prefix = 'pmacontrol_test_md5_' . bin2hex(random_bytes(4));
        $directory = '/tmp/md5';
        $firstFile = $directory . '/' . $prefix . '::181.md5';
        $secondFile = $directory . '/' . $prefix . '::182.md5';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($firstFile, 'first');
        file_put_contents($secondFile, 'second');

        try {
            $this->assertSame(1, EngineV4::cleanMd5Files([$prefix], [181]));
            $this->assertFileDoesNotExist($firstFile);
            $this->assertFileExists($secondFile);
        } finally {
            if (is_file($firstFile)) {
                unlink($firstFile);
            }
            if (is_file($secondFile)) {
                unlink($secondFile);
            }
        }
    }
}
