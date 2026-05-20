<?php

declare(strict_types=1);

use App\Controller\Daemon;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DaemonUpdateSecurityTest extends TestCase
{
    public function testDaemonUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'daemon.update');

        $outcome = Daemon::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'refresh_time', 'value' => '300', 'pk' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('UPDATE daemon_main SET `refresh_time` = 300 WHERE id = 7', $outcome['sql']);
    }

    public function testDaemonUpdateRequestRejectsNonPost(): void
    {
        $outcome = Daemon::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['sql']);
    }

    public function testDaemonUpdateRequestRejectsExternalSourceAndMissingToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'daemon.update');

        $external = Daemon::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'refresh_time', 'value' => '300', 'pk' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $missingToken = Daemon::evaluateUpdateRequest(
            ['name' => 'refresh_time', 'value' => '300', 'pk' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertNull($external['sql']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertNull($missingToken['sql']);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'daemon.update');
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'refresh_time', 'value' => '900', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame('UPDATE daemon_main SET `refresh_time` = 900 WHERE id = 7', Daemon::buildDaemonUpdateSql($post));

        $outcome = Daemon::evaluateUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['sql']);
    }

    public function testDaemonUpdateSqlIsRestrictedToRefreshTimeInteger(): void
    {
        $this->assertSame(
            'UPDATE daemon_main SET `refresh_time` = 300 WHERE id = 7',
            Daemon::buildDaemonUpdateSql(['name' => 'refresh_time', 'value' => '300', 'pk' => '7'])
        );

        $this->assertSame(
            'UPDATE daemon_main SET `refresh_time` = 0 WHERE id = 7',
            Daemon::buildDaemonUpdateSql(['name' => 'refresh_time', 'value' => '0', 'pk' => '7'])
        );

        $this->assertNull(Daemon::buildDaemonUpdateSql(['name' => 'pid', 'value' => '0', 'pk' => '7']));
        $this->assertNull(Daemon::buildDaemonUpdateSql(['name' => 'refresh_time` = 0, pid', 'value' => '0', 'pk' => '7']));
        $this->assertNull(Daemon::buildDaemonUpdateSql(['name' => 'refresh_time', 'value' => "300'; DROP TABLE daemon_main", 'pk' => '7']));
        $this->assertNull(Daemon::buildDaemonUpdateSql(['name' => 'refresh_time', 'value' => '300', 'pk' => '0']));
        $this->assertNull(Daemon::buildDaemonUpdateSql(['name' => 'refresh_time', 'value' => '-1', 'pk' => '7']));
    }

    public function testDaemonUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Daemon.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Daemon/index.view.php');
        $javascript = (string) file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');
        $updateStart = strpos($controller, 'public function update()');
        $refreshStart = strpos($controller, 'public function refresh($param)');

        $this->assertNotFalse($updateStart);
        $this->assertNotFalse($refreshStart);
        $updateBody = substr($controller, $updateStart, $refreshStart - $updateStart);

        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString("private const DAEMON_UPDATE_CSRF_SCOPE = 'daemon.update'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DAEMON_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::DAEMON_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateUpdateRequest($_POST, $_SERVER, $_SESSION)', $updateBody);
        $this->assertStringNotContainsString('$_POST[\'name\']', $updateBody);
        $this->assertStringNotContainsString('$_POST[\'value\']', $updateBody);
        $this->assertStringNotContainsString('sql_real_escape_string($_POST[\'pk\'])', $updateBody);
        $this->assertStringContainsString("CsrfRender::attributes(\$data, 'daemon_update')", $view);
        $this->assertStringContainsString('daemon/update', $view);
        $this->assertStringContainsString('params[csrfField] = csrfToken;', $javascript);
    }
}
