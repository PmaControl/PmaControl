<?php

declare(strict_types=1);

use App\Library\ShellCommand;
use PHPUnit\Framework\TestCase;

final class ShellCommandTest extends TestCase
{
    public function testArgumentsAreEscaped(): void
    {
        $command = ShellCommand::of('/usr/bin/php')
            ->arg('/srv/www/pma control/index.php')
            ->arg("mysql_*'danger")
            ->toString();

        $this->assertSame(
            implode(' ', array_map('escapeshellarg', [
                '/usr/bin/php',
                '/srv/www/pma control/index.php',
                "mysql_*'danger",
            ])),
            $command
        );
    }

    public function testOptionsSupportSpaceAndEqualsSeparators(): void
    {
        $command = ShellCommand::of('mysql')
            ->option('-h', 'db host')
            ->option('--database', 'archive_db', '=')
            ->intOption('-P', 3306)
            ->flag('--debug')
            ->toString();

        $this->assertSame(
            'mysql -h '.escapeshellarg('db host')
            .' --database='.escapeshellarg('archive_db')
            .' -P 3306 --debug',
            $command
        );
    }

    public function testUnsafeBinaryNameIsEscaped(): void
    {
        $this->assertSame(escapeshellarg('mysql;id'), ShellCommand::of('mysql;id')->toString());
    }

    public function testInvalidOptionNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ShellCommand::of('mysql')->option('--database;id', 'archive_db');
    }

    public function testInvalidOptionSeparatorIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ShellCommand::of('mysql')->option('--database', 'archive_db', ':');
    }

    public function testInvalidRawTokenIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ShellCommand::of('mysql')->rawToken('foo;bar');
    }

    public function testRedirectsAndStderrMergeAreExplicit(): void
    {
        $command = ShellCommand::of('php')
            ->redirect(1, '>', '/tmp/pma control/run.log')
            ->redirect(2, '>>', '/tmp/pma control/error.log')
            ->mergeStderrIntoStdout()
            ->toString();

        $this->assertSame(
            'php > '.escapeshellarg('/tmp/pma control/run.log')
            .' 2>> '.escapeshellarg('/tmp/pma control/error.log')
            .' 2>&1',
            $command
        );
    }

    public function testInvalidRedirectionOperatorIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ShellCommand::of('php')->redirect(2, '2>&1', '/tmp/log');
    }

    public function testPipeDoesNotMutateSourceCommand(): void
    {
        $pv = ShellCommand::of('pv')
            ->flag('--')
            ->arg('/tmp/dump.sql');
        $mysql = ShellCommand::of('mysql')
            ->option('--database', 'archive_db', '=');

        $pipeline = $pv->pipeTo($mysql)->toString();

        $this->assertSame('pv -- '.escapeshellarg('/tmp/dump.sql'), $pv->toString());
        $this->assertSame(
            'pv -- '.escapeshellarg('/tmp/dump.sql')
            .' | mysql --database='.escapeshellarg('archive_db'),
            $pipeline
        );
    }

    public function testBackgroundPidFinalizesCommand(): void
    {
        $command = ShellCommand::of('php')
            ->arg('worker.php')
            ->inBackgroundCapturingPid();

        $this->assertSame('php '.escapeshellarg('worker.php').' & echo $!', $command->toString());

        $this->expectException(LogicException::class);

        $command->arg('late');
    }

    public function testImplicitStringCastIsNotAvailable(): void
    {
        $this->assertFalse(method_exists(ShellCommand::class, '__toString'));
    }

    public function testRemoteMd5sumEscapesPathAndSilencesStderr(): void
    {
        foreach (['/tmp/x;id', '/tmp/$(id)', "/tmp/'\$(id)", '-rf'] as $path) {
            $this->assertSame(
                'md5sum -- ' . escapeshellarg($path) . ' 2>/dev/null',
                ShellCommand::remoteMd5sum($path)
            );
        }

        $this->assertSame(
            'md5sum -- ' . escapeshellarg('/tmp/file'),
            ShellCommand::remoteMd5sum('/tmp/file', false)
        );
    }

    public function testRemoteMkdirAndAppendFileEscapePaths(): void
    {
        $this->assertSame(
            'mkdir -p -- ' . escapeshellarg('/root/.ssh')
            . ' && cat -- ' . escapeshellarg('/home/foo/abc;id')
            . ' >> ' . escapeshellarg('/root/.ssh/authorized_keys'),
            ShellCommand::remoteMkdirAndAppendFile('/root/.ssh', '/home/foo/abc;id', '/root/.ssh/authorized_keys')
        );

        $this->assertSame(
            'rm -f -- ' . escapeshellarg('/tmp/x;id'),
            ShellCommand::remoteRemoveFile('/tmp/x;id')
        );
    }

    public function testSudoShellEscapesWholeRemoteCommand(): void
    {
        $command = "mkdir -p -- /root/.ssh && cat -- '/tmp/x;id' >> '/root/.ssh/authorized_keys'";

        $this->assertSame('sudo -- sh -c ' . escapeshellarg($command), ShellCommand::sudoShell($command));
    }

    public function testUnixUsernameValidationAndTempPath(): void
    {
        $this->assertTrue(ShellCommand::isSafeUnixUsername('root'));
        $this->assertTrue(ShellCommand::isSafeUnixUsername('pmacontrol_1'));
        $this->assertTrue(ShellCommand::isSafeUnixUsername('PmaControl-1'));

        foreach (['', '1abc', 'bad;user', '../user', "bad\nuser", str_repeat('a', 33)] as $username) {
            $this->assertFalse(ShellCommand::isSafeUnixUsername($username));
        }

        $this->assertSame('/root/pma-123', ShellCommand::remoteTempPathForUser('root', 'pma-123'));
        $this->assertSame('/home/pmacontrol/pma-123', ShellCommand::remoteTempPathForUser('pmacontrol', 'pma-123'));
        $this->assertNull(ShellCommand::remoteTempPathForUser('bad;user', 'pma-123'));
        $this->assertNull(ShellCommand::remoteTempPathForUser('pmacontrol', '../pma-123'));
        $this->assertNull(ShellCommand::remoteTempPathForUser('pmacontrol', '.'));
        $this->assertNull(ShellCommand::remoteTempPathForUser('pmacontrol', '..'));
        $this->assertNull(ShellCommand::remoteTempPathForUser('pmacontrol', '.hidden'));
    }
}
