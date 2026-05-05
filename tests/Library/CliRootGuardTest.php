<?php

declare(strict_types=1);

use App\Library\CliRootGuard;
use PHPUnit\Framework\TestCase;

final class CliRootGuardTest extends TestCase
{
    public function testRootCliApplicationCommandShouldReexec(): void
    {
        $this->assertTrue(CliRootGuard::shouldReexec(true, 0, ['index.php', 'worker', 'run'], []));
    }

    public function testNonRootCliCommandDoesNotReexec(): void
    {
        $this->assertFalse(CliRootGuard::shouldReexec(true, 33, ['index.php', 'worker', 'run'], []));
    }

    public function testInstallCommandKeepsRoot(): void
    {
        $this->assertFalse(CliRootGuard::shouldReexec(true, 0, ['index.php', 'install', 'index'], []));
    }

    public function testRootOnlyWebserviceImportKeepsRoot(): void
    {
        $this->assertFalse(CliRootGuard::shouldReexec(true, 0, ['index.php', 'webservice', 'importMysqlServerPlain'], []));
    }

    public function testReexecEnvironmentPreventsLoop(): void
    {
        $this->assertFalse(CliRootGuard::shouldReexec(
            true,
            0,
            ['index.php', 'worker', 'run'],
            ['PMACONTROL_CLI_REEXEC' => '1']
        ));
    }

    public function testBuildPhpCommandPreservesArguments(): void
    {
        $command = CliRootGuard::buildPhpCommand(
            ['App/Webroot/index.php', 'worker', 'run', '10', '--debug'],
            '/srv/www/pmacontrol/App/Webroot/index.php',
            '/usr/bin/php'
        );

        $this->assertSame(
            ['/usr/bin/php', '/srv/www/pmacontrol/App/Webroot/index.php', 'worker', 'run', '10', '--debug'],
            $command
        );
    }
}
