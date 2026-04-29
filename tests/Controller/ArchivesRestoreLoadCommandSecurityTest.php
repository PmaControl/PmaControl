<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ArchivesRestoreLoadCommandSecurityTest extends TestCase
{
    public function testArchiveLoadUsesSharedMysqlRestoreCommandHelper(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Archives.php');
        $helper = (string) file_get_contents(__DIR__ . '/../../App/Library/Archive/MysqlRestoreCommand.php');

        $loadStart = strpos($controller, 'public function load($param)');
        $historyStart = strpos($controller, 'public function history($param)', (int) $loadStart);

        $this->assertNotFalse($loadStart);
        $this->assertNotFalse($historyStart);

        $loadBody = substr($controller, (int) $loadStart, (int) $historyStart - (int) $loadStart);

        $this->assertStringContainsString('use App\\Library\\Archive\\MysqlRestoreCommand;', $controller);
        $this->assertStringContainsString("MysqlRestoreCommand::prefixDumpWithSqlMode(\$stats['file_path'])", $loadBody);
        $this->assertStringContainsString('MysqlRestoreCommand::createClientDefaultsFile($conf)', $loadBody);
        $this->assertStringContainsString(
            "MysqlRestoreCommand::buildLoadCommand(\$defaults_file, \$stats['file_path'], \$database, \$log_mysql)",
            $loadBody
        );
        $this->assertStringContainsString('finally', $loadBody);
        $this->assertStringContainsString('catch (\\Throwable $exception)', $loadBody);
        $this->assertStringContainsString('$restore_exception = $exception;', $loadBody);
        $this->assertStringContainsString('$exit = 1;', $loadBody);
        $this->assertStringContainsString('MysqlRestoreCommand::deleteFile($defaults_file)', $loadBody);
        $this->assertStringContainsString('MysqlRestoreCommand::deleteFile($log_mysql)', $loadBody);

        $this->assertStringNotContainsString('sed -i', $loadBody);
        $this->assertStringNotContainsString("-p'{password}'", $loadBody);
        $this->assertStringNotContainsString('str_replace("{password}"', $loadBody);
        $this->assertStringNotContainsString('Debug::debug($cmd)', $loadBody);
        $this->assertStringNotContainsString('Debug::debug($conf', $loadBody);

        $this->assertStringContainsString('--defaults-extra-file=', $helper);
        $this->assertStringContainsString('escapeshellarg($dumpPath)', $helper);
        $this->assertStringContainsString('escapeshellarg($defaultsFile)', $helper);
        $this->assertStringContainsString('escapeshellarg($database)', $helper);
        $this->assertStringContainsString('escapeshellarg($logPath)', $helper);
        $this->assertStringContainsString('password=', $helper);
        $this->assertStringNotContainsString("-p'{password}'", $helper);
    }
}
