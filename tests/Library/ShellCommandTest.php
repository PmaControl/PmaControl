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
}
