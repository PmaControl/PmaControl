<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserRegisterSecurityTest extends TestCase
{
    public function testRegisterAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.register');

        $outcome = User::evaluateRegisterRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['email' => 'new@example.test'],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(200, $outcome['status']);
    }

    public function testRegisterRejectsNonPost(): void
    {
        $outcome = User::evaluateRegisterRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testRegisterRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.register');

        $outcome = User::evaluateRegisterRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['email' => 'new@example.test'],
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

    public function testRegisterRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.updateIdGroup');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];

        $missingToken = User::evaluateRegisterRequest(
            ['user_main' => ['email' => 'new@example.test']],
            $server,
            $session
        );
        $foreignScope = User::evaluateRegisterRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'user_main' => ['email' => 'new@example.test'],
            ],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testRegisterRejectsMissingPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.register');

        $outcome = User::evaluateRegisterRequest(
            [Csrf::DEFAULT_FIELD => $token],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(400, $outcome['status']);
        $this->assertSame('Invalid registration payload', $outcome['body']);
    }

    public function testRegisterViewSendsCsrfToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/register.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('private const USER_REGISTER_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::USER_REGISTER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::USER_REGISTER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$userRegisterCsrfField', $view);
        $this->assertStringContainsString('$userRegisterCsrfToken', $view);
    }
}
