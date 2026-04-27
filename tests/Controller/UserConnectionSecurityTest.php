<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserConnectionSecurityTest extends TestCase
{
    public function testConnectionAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.connection');

        $outcome = User::evaluateConnectionRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => [
                    'login' => ' admin ',
                    'password' => '  secret  ',
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('admin', $outcome['login']);
        $this->assertSame('  secret  ', $outcome['password']);
    }

    public function testConnectionRejectsNonPost(): void
    {
        $outcome = User::evaluateConnectionRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testConnectionRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.connection');

        $outcome = User::evaluateConnectionRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['login' => 'admin', 'password' => 'secret'],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testConnectionRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.lostPassword');
        $server = $this->sameSitePostServer();

        $missingToken = User::evaluateConnectionRequest(
            ['user_main' => ['login' => 'admin', 'password' => 'secret']],
            $server,
            $session
        );
        $foreignScope = User::evaluateConnectionRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'user_main' => ['login' => 'admin', 'password' => 'secret'],
            ],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testConnectionRejectsInvalidPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.connection');
        $server = $this->sameSitePostServer();

        $missingPayload = User::evaluateConnectionRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $server,
            $session
        );
        $emptyLogin = User::evaluateConnectionRequest(
            [Csrf::DEFAULT_FIELD => $token, 'user_main' => ['login' => ' ', 'password' => 'secret']],
            $server,
            $session
        );
        $emptyPassword = User::evaluateConnectionRequest(
            [Csrf::DEFAULT_FIELD => $token, 'user_main' => ['login' => 'admin', 'password' => '']],
            $server,
            $session
        );

        $this->assertSame(400, $missingPayload['status']);
        $this->assertSame('Invalid connection payload', $missingPayload['body']);
        $this->assertSame(400, $emptyLogin['status']);
        $this->assertSame('Invalid connection payload', $emptyLogin['body']);
        $this->assertSame(400, $emptyPassword['status']);
        $this->assertSame('Invalid connection payload', $emptyPassword['body']);
    }

    public function testConnectionViewSendsCsrfToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/connection.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('private const USER_CONNECTION_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::USER_CONNECTION_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::USER_CONNECTION_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('name="<?=$userConnectionCsrfField?>"', $view);
        $this->assertStringContainsString('value="<?=$userConnectionCsrfToken?>"', $view);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }
}
