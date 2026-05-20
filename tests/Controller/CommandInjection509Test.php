<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CommandInjection509Test extends TestCase
{
    public function testTicket509LegacyShellPatternsAreRemovedFromTransferHelpers(): void
    {
        $scp = (string) file_get_contents(__DIR__ . '/../../App/Library/Scp.php');
        $transfer = (string) file_get_contents(__DIR__ . '/../../App/Library/Transfer.php');
        $ssh = (string) file_get_contents(__DIR__ . '/../../App/Library/Ssh.php');

        foreach ([$scp, $transfer, $ssh] as $source) {
            $this->assertStringNotContainsString('$ssh->exec("mkdir -p ', $source);
            $this->assertStringNotContainsString('$ssh->exec("md5sum ', $source);
            $this->assertStringNotContainsString('2>1 >> /dev/null', $source);
            $this->assertStringContainsString('ShellCommand::remoteMd5sum(', $source);
            $this->assertStringContainsString('$sftp->mkdir($dst_dir, -1, true)', $source);
        }
    }

    public function testTicket509DeployRsaKeyUsesValidatedEscapedRemoteCommands(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/DeployRsaKey.php');

        $this->assertStringContainsString('ShellCommand::isSafeUnixUsername($pubkey[\'user\'])', $controller);
        $this->assertStringContainsString('bin2hex(random_bytes(16))', $controller);
        $this->assertStringContainsString('ShellCommand::remoteTempPathForUser($pubkey[\'user\'], $tmp_file)', $controller);
        $this->assertStringContainsString('ShellCommand::remoteMkdirAndAppendFile(', $controller);
        $this->assertStringContainsString('ShellCommand::sudoShell($appendCommand)', $controller);

        $this->assertStringNotContainsString('uniqid()', $controller);
        $this->assertStringNotContainsString('Ssh::$ssh->write("sudo su -', $controller);
        $this->assertStringNotContainsString('$password . "\\n"', $controller);
        $this->assertStringNotContainsString('cat /home/" . $pubkey[\'user\']', $controller);
        $this->assertStringNotContainsString('"mkdir -p /root/.ssh && cat " . $dest_path', $controller);
    }
}
