<?php

declare(strict_types=1);

use App\Library\System;
use PHPUnit\Framework\TestCase;

final class HomeDaemonStatusTest extends TestCase
{
    public function testResolveDaemonStatusReturnsStoppedWithoutPid(): void
    {
        $this->assertSame('stopped', System::resolveDaemonStatus(['id' => 7, 'pid' => 0]));
    }

    public function testResolveDaemonStatusAcceptsExpectedAgentLaunchProcess(): void
    {
        $commandLine = '/usr/bin/php /srv/www/pmacontrol/App/Webroot/index.php Agent launch 7 --debug';

        $this->assertSame('running', System::resolveDaemonStatus(['id' => 7, 'pid' => 1234], $commandLine));
    }

    public function testResolveDaemonStatusRejectsPidReusedByOtherProcess(): void
    {
        $commandLine = '/usr/sbin/apache2 -k start';

        $this->assertSame('error', System::resolveDaemonStatus(['id' => 7, 'pid' => 1234], $commandLine));
    }

    public function testResolveDaemonStatusRejectsOtherDaemonLaunchProcess(): void
    {
        $commandLine = '/usr/bin/php /srv/www/pmacontrol/App/Webroot/index.php Agent launch 8 --debug';

        $this->assertSame('error', System::resolveDaemonStatus(['id' => 7, 'pid' => 1234], $commandLine));
    }

    public function testResolveDaemonStatusRejectsDaemonIdPrefixMatch(): void
    {
        $commandLine = '/usr/bin/php /srv/www/pmacontrol/App/Webroot/index.php Agent launch 70 --debug';

        $this->assertSame('error', System::resolveDaemonStatus(['id' => 7, 'pid' => 1234], $commandLine));
    }

    public function testExpectedDaemonCommandNormalizesProcCmdlineNullBytes(): void
    {
        $commandLine = implode("\0", [
            '/usr/bin/php',
            '/srv/www/pmacontrol/App/Webroot/index.php',
            'Agent',
            'launch',
            '7',
            '--debug',
        ]);

        $this->assertTrue(System::isExpectedDaemonCommand(['id' => 7], $commandLine));
    }
}
