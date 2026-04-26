<?php

declare(strict_types=1);

use App\Controller\Agent;
use PHPUnit\Framework\TestCase;

if (!defined('LOG_FILE')) {
    define('LOG_FILE', '/tmp/pmacontrol-agent-test.log');
}

final class AgentCommandTest extends TestCase
{
    private function buildBackgroundCommand(array $args, string $logFile): string
    {
        $reflectionMethod = new ReflectionMethod(Agent::class, 'buildBackgroundCommand');

        return $reflectionMethod->invokeArgs(null, [$args, $logFile]);
    }

    public function testBackgroundCommandQuotesArgumentsAndRedirectsStderrToLog(): void
    {
        $command = $this->buildBackgroundCommand(
            [
                '/usr/bin/php',
                '/srv/www/pmacontrol/App/Webroot/index.php',
                'Integrate',
                'integrateAll',
                "mysql_*'danger",
                'loop:42',
                '--debug',
            ],
            '/tmp/pma control/worker.log'
        );

        $this->assertSame(
            implode(' ', array_map('escapeshellarg', [
                '/usr/bin/php',
                '/srv/www/pmacontrol/App/Webroot/index.php',
                'Integrate',
                'integrateAll',
                "mysql_*'danger",
                'loop:42',
                '--debug',
            ])) . ' >> ' . escapeshellarg('/tmp/pma control/worker.log') . ' 2>&1 & echo $!',
            $command
        );
    }

    public function testBackgroundCommandPreservesEmptyDaemonParams(): void
    {
        $command = $this->buildBackgroundCommand(
            ['/usr/bin/php', '/srv/www/pmacontrol/App/Webroot/index.php', 'Agent', 'launch', ''],
            '/tmp/worker.log'
        );

        $this->assertStringContainsString("''", $command);
    }
}
