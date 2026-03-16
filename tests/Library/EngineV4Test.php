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
}
