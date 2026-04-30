<?php

declare(strict_types=1);

use App\Controller\Mysqlsys;
use App\Library\Security\SafeRedirect;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}

final class MysqlsysMutationSecurityTest extends TestCase
{
    public function testResetRejectsGetBeforePayload(): void
    {
        $outcome = Mysqlsys::evaluateResetRequest(
            [],
            ['id_mysql_server' => '7'],
            ['REQUEST_METHOD' => 'GET'],
            []
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id_mysql_server']);
    }

    public function testResetAcceptsValidPostAndNormalizesId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.reset');

        $outcome = Mysqlsys::evaluateResetRequest(
            [],
            $this->postWithToken(['id_mysql_server' => '00042'], $token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertSame(42, $outcome['id_mysql_server']);
        $this->assertSame([42], $outcome['param']);
    }

    public function testResetRejectsMissingTokenAndExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.reset');

        $missingToken = Mysqlsys::evaluateResetRequest(
            [],
            ['id_mysql_server' => '7'],
            $this->sameSitePostServer(),
            $session
        );
        $externalOrigin = Mysqlsys::evaluateResetRequest(
            [],
            $this->postWithToken(['id_mysql_server' => '7'], $token),
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $externalOrigin['status']);
        $this->assertSame('Invalid request origin', $externalOrigin['body']);
    }

    public function testResetRejectsInjectedRoutePostAndMismatchIds(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.reset');
        $server = $this->sameSitePostServer();

        $cases = [
            Mysqlsys::evaluateResetRequest(['1 OR 1=1'], $this->postWithToken(['id_mysql_server' => '1'], $token), $server, $session),
            Mysqlsys::evaluateResetRequest([], $this->postWithToken(['id_mysql_server' => '1 OR 1=1'], $token), $server, $session),
            Mysqlsys::evaluateResetRequest(['2'], $this->postWithToken(['id_mysql_server' => '1'], $token), $server, $session),
            Mysqlsys::evaluateResetRequest([], $this->postWithToken(['id_mysql_server' => '0'], $token), $server, $session),
        ];

        foreach ($cases as $outcome) {
            $this->assertFalse($outcome['allowed']);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid MySQL server id', $outcome['body']);
            $this->assertNull($outcome['id_mysql_server']);
        }
    }

    public function testDropUsesSeparateScopeAndRequiresConfirmationPayload(): void
    {
        $session = [];
        $resetToken = Csrf::issueToken($session, 'mysqlsys.reset');
        $dropToken = Csrf::issueToken($session, 'mysqlsys.drop');
        $server = $this->sameSitePostServer();

        $wrongScope = Mysqlsys::evaluateDropRequest(
            [],
            $this->dropPost(['id_mysql_server' => '7'], $resetToken),
            $server,
            $session
        );
        $missingConfirm = Mysqlsys::evaluateDropRequest(
            [],
            $this->postWithToken(['id_mysql_server' => '7'], $dropToken),
            $server,
            $session
        );
        $missingServerName = Mysqlsys::evaluateDropRequest(
            [],
            $this->postWithToken(['id_mysql_server' => '7', 'confirm' => 'DROP_SYS'], $dropToken),
            $server,
            $session
        );

        $this->assertSame(403, $wrongScope['status']);
        $this->assertSame('Invalid CSRF token', $wrongScope['body']);
        $this->assertSame(400, $missingConfirm['status']);
        $this->assertSame('Invalid MySQL-sys drop confirmation', $missingConfirm['body']);
        $this->assertSame(400, $missingServerName['status']);
        $this->assertSame('Invalid MySQL-sys drop confirmation', $missingServerName['body']);
    }

    public function testDropAcceptsValidConfirmationAndNormalizesId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.drop');

        $outcome = Mysqlsys::evaluateDropRequest(
            ['7'],
            $this->dropPost(['id_mysql_server' => '007', 'confirm_server_name' => 'prod-mysql-01'], $token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(7, $outcome['id_mysql_server']);
        $this->assertSame([7], $outcome['param']);
        $this->assertSame('prod-mysql-01', $outcome['confirm_server_name']);
        $this->assertTrue(Mysqlsys::isDropConfirmationServerNameValid('prod-mysql-01', 'prod-mysql-01'));
        $this->assertFalse(Mysqlsys::isDropConfirmationServerNameValid('PROD-MYSQL-01', 'prod-mysql-01'));
        $this->assertFalse(Mysqlsys::isDropConfirmationServerNameValid(null, 'prod-mysql-01'));
    }

    public function testResetAllowsCliRouteIdWithoutCsrf(): void
    {
        $outcome = Mysqlsys::evaluateResetRequest(['42'], [], ['REQUEST_METHOD' => 'GET'], [], true);

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(42, $outcome['id_mysql_server']);
        $this->assertSame([42], $outcome['param']);
    }

    public function testExternalRefererFallsBackToMysqlsysIndex(): void
    {
        $server = [
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_REFERER' => 'https://attacker.test/after',
        ];

        $this->assertSame(
            '/mysqlsys/index/mysql_server:id:7',
            SafeRedirect::refererOrFallback($server, '/mysqlsys/index/mysql_server:id:7')
        );
    }

    public function testControllerAndViewUsePostCsrfHelpersAndNoMutatingLinks(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Mysqlsys.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Mysqlsys/index.view.php');

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\SafeRedirect;', $controller);
        $this->assertStringContainsString("private const MYSQLSYS_RESET_CSRF_SCOPE = 'mysqlsys.reset'", $controller);
        $this->assertStringContainsString("private const MYSQLSYS_DROP_CSRF_SCOPE = 'mysqlsys.drop'", $controller);
        $this->assertStringContainsString('evaluateResetRequest($param, $_POST, $_SERVER, $_SESSION, IS_CLI)', $controller);
        $this->assertStringContainsString('evaluateDropRequest($param, $_POST, $_SERVER, $_SESSION, IS_CLI)', $controller);
        $this->assertStringContainsString('SafeRedirect::refererOrFallback($_SERVER, self::mysqlsysIndexUrl($id_mysql_server))', $controller);
        $this->assertStringNotContainsString('header("location: " . $_SERVER[\'HTTP_REFERER\'])', $controller);

        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'mysqlsys_reset')", $view);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'mysqlsys_drop')", $view);
        $this->assertStringContainsString('action="\' . LINK . \'Mysqlsys/reset"', $view);
        $this->assertStringContainsString('action="\' . LINK . \'Mysqlsys/drop"', $view);
        $this->assertStringContainsString('method="post"', $view);
        $this->assertStringContainsString('name="confirm" value="DROP_SYS"', $view);
        $this->assertStringContainsString('name="confirm_server_name"', $view);
        $this->assertStringNotContainsString('<a href="\' . LINK . \'Mysqlsys/reset/', $view);
        $this->assertStringNotContainsString('<a href="\' . LINK . \'Mysqlsys/drop/', $view);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    private function postWithToken(array $payload, string $token): array
    {
        $payload[Csrf::DEFAULT_FIELD] = $token;

        return $payload;
    }

    private function dropPost(array $payload, string $token): array
    {
        $payload += [
            'confirm' => 'DROP_SYS',
            'confirm_server_name' => 'prod-mysql-01',
        ];

        return $this->postWithToken($payload, $token);
    }
}
