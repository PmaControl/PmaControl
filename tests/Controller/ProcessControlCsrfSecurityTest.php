<?php

declare(strict_types=1);

use App\Controller\Agent;
use App\Controller\Daemon;
use App\Controller\Job;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('LOG_FILE')) {
    define('LOG_FILE', '/tmp/pmacontrol-agent-test.log');
}

final class ProcessControlCsrfSecurityTest extends TestCase
{
    public function testAgentControlRequiresPostCsrfAndPositiveId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'agent.control');

        $get = Agent::evaluateControlRequest(['7'], [], ['REQUEST_METHOD' => 'GET'], $session);
        $invalidId = Agent::evaluateControlRequest(['7 OR 1=1'], [], $this->sameSitePostServer(), $session);
        $missingToken = Agent::evaluateControlRequest(['7'], [], $this->sameSitePostServer(), $session);
        $external = Agent::evaluateControlRequest(
            ['7'],
            [Csrf::DEFAULT_FIELD => $token],
            $this->externalPostServer(),
            $session
        );
        $valid = Agent::evaluateControlRequest(
            ['7'],
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(405, $get['status']);
        $this->assertSame('POST', $get['headers']['Allow']);
        $this->assertSame(400, $invalidId['status']);
        $this->assertNull($invalidId['id_daemon']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertSame(200, $valid['status']);
        $this->assertSame(7, $valid['id_daemon']);
    }

    public function testDaemonBulkControlRequiresPostCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'daemon.control');

        $get = Daemon::evaluateControlRequest([], ['REQUEST_METHOD' => 'GET'], $session);
        $missingToken = Daemon::evaluateControlRequest([], $this->sameSitePostServer(), $session);
        $valid = Daemon::evaluateControlRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(405, $get['status']);
        $this->assertSame('POST', $get['headers']['Allow']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(200, $valid['status']);
    }

    public function testJobRestartRequiresPostCsrfAndPositiveId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'job.restart');

        $get = Job::evaluateRestartRequest(['42'], [], ['REQUEST_METHOD' => 'GET'], $session);
        $invalidId = Job::evaluateRestartRequest(['1;id'], [], $this->sameSitePostServer(), $session);
        $missingToken = Job::evaluateRestartRequest(['42'], [], $this->sameSitePostServer(), $session);
        $external = Job::evaluateRestartRequest(
            ['42'],
            [Csrf::DEFAULT_FIELD => $token],
            $this->externalPostServer(),
            $session
        );
        $valid = Job::evaluateRestartRequest(
            ['42', '--debug'],
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(405, $get['status']);
        $this->assertSame('POST', $get['headers']['Allow']);
        $this->assertSame(400, $invalidId['status']);
        $this->assertNull($invalidId['id_job']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertSame(200, $valid['status']);
        $this->assertSame(42, $valid['id_job']);
        $this->assertTrue($valid['debug']);
    }

    public function testInternalCliCallsBypassBrowserCsrfGateAfterIdValidation(): void
    {
        $agent = Agent::evaluateControlRequest(['7'], [], ['REQUEST_METHOD' => 'GET'], [], true);
        $daemon = Daemon::evaluateControlRequest([], ['REQUEST_METHOD' => 'GET'], [], true);
        $job = Job::evaluateRestartRequest(['42', '--debug'], [], ['REQUEST_METHOD' => 'GET'], [], true);

        $this->assertSame(200, $agent['status']);
        $this->assertSame(7, $agent['id_daemon']);
        $this->assertSame(200, $daemon['status']);
        $this->assertSame(200, $job['status']);
        $this->assertSame(42, $job['id_job']);
        $this->assertTrue($job['debug']);
    }

    public function testJobRestartAllowListAndQuotesCommandArguments(): void
    {
        $this->assertTrue(Job::isRelaunchableCommand('Database', 'addRefresh'));
        $this->assertTrue(Job::isRelaunchableCommand('App\\Controller\\Backup', 'runBackup'));
        $this->assertFalse(Job::isRelaunchableCommand('Database', 'databaseLoad'));
        $this->assertFalse(Job::isRelaunchableCommand('Cleaner', 'restart'));

        $command = Job::buildRestartCommand(
            '/usr/bin/php',
            '/srv/www/pmacontrol/App/Webroot/index.php',
            'App\\Controller\\Database',
            'addRefresh',
            ['220', '221', "db; rm -rf /", '/tmp/dump path'],
            true
        );

        $this->assertSame(
            implode(' ', array_map('escapeshellarg', [
                '/usr/bin/php',
                '/srv/www/pmacontrol/App/Webroot/index.php',
                'Database',
                'addRefresh',
                '220',
                '221',
                "db; rm -rf /",
                '/tmp/dump path',
                '--debug',
            ])),
            $command
        );
        $this->assertNull(Job::buildRestartCommand('/usr/bin/php', '/index.php', 'Cleaner', 'restart', ['1']));
    }

    public function testViewsRenderProcessControlsAsPostFormsWithCsrfInputs(): void
    {
        $daemonView = (string) file_get_contents(__DIR__ . '/../../App/view/Daemon/index.view.php');
        $jobView = (string) file_get_contents(__DIR__ . '/../../App/view/Job/index.view.php');

        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'agent_control')", $daemonView);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'daemon_control')", $daemonView);
        $this->assertStringContainsString('form method="post" action="\'.LINK.\'Agent/stop/', $daemonView);
        $this->assertStringContainsString('form method="post" action="\'.LINK.\'Agent/start/', $daemonView);
        $this->assertStringContainsString('form method="post" action="\'.LINK.\'Daemon/stopAll/', $daemonView);
        $this->assertStringContainsString('form method="post" action="\'.LINK.\'Daemon/startAll', $daemonView);
        $this->assertStringNotContainsString('<a href="\'.LINK.\'Agent/stop/', $daemonView);
        $this->assertStringNotContainsString('<a href="\'.LINK.\'Agent/start/', $daemonView);
        $this->assertStringNotContainsString('<a href="\'.LINK.\'Daemon/stopAll/', $daemonView);
        $this->assertStringNotContainsString('<a href="\'.LINK.\'Daemon/startAll', $daemonView);

        $this->assertStringContainsString("use App\\Library\\Security\\CsrfRender;", $jobView);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'job_restart')", $jobView);
        $this->assertStringContainsString('form method="post" action="\'.LINK.\'job/restart/', $jobView);
        $this->assertStringNotContainsString('<a href="\'.LINK.\'job/restart/', $jobView);
    }

    public function testProcessControlGuardsRunBeforeSideEffects(): void
    {
        $agent = (string) file_get_contents(__DIR__ . '/../../App/Controller/Agent.php');
        $daemon = (string) file_get_contents(__DIR__ . '/../../App/Controller/Daemon.php');
        $job = (string) file_get_contents(__DIR__ . '/../../App/Controller/Job.php');

        $this->assertLessThan(
            strpos($agent, 'Debug::parseDebug($param);', strpos($agent, 'public function start($param)')),
            strpos($agent, 'evaluateControlRequest($param, $_POST ?? [], $_SERVER ?? [], $_SESSION ?? [], IS_CLI)')
        );
        $this->assertLessThan(
            strpos($agent, 'Sgbd::sql(DB_DEFAULT)', strpos($agent, 'public function start($param)')),
            strpos($agent, 'evaluateControlRequest($param, $_POST ?? [], $_SERVER ?? [], $_SESSION ?? [], IS_CLI)')
        );
        $this->assertLessThan(
            strpos($daemon, 'Sgbd::sql(DB_DEFAULT)', strpos($daemon, 'public function startAll($param)')),
            strpos($daemon, 'evaluateControlRequest($_POST ?? [], $_SERVER ?? [], $_SESSION ?? [], IS_CLI)')
        );
        $this->assertLessThan(
            strpos($job, 'Sgbd::sql(DB_DEFAULT)', strpos($job, 'public function restart($param)')),
            strpos($job, 'evaluateRestartRequest($param, $_POST ?? [], $_SERVER ?? [], $_SESSION ?? [], IS_CLI)')
        );
    }

    /**
     * @return array<string,string>
     */
    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    /**
     * @return array<string,string>
     */
    private function externalPostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];
    }
}
