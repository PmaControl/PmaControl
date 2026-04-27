<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserPasswordRecoverSecurityTest extends TestCase
{
    public function testPasswordRecoverAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.passwordRecover');

        $outcome = User::evaluatePasswordRecoverRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['password' => 'new-secret'],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('new-secret', $outcome['password']);
    }

    public function testPasswordRecoverRejectsNonPost(): void
    {
        $outcome = User::evaluatePasswordRecoverRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testPasswordRecoverRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.passwordRecover');

        $outcome = User::evaluatePasswordRecoverRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['password' => 'new-secret'],
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

    public function testPasswordRecoverRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.profile');
        $server = $this->sameSitePostServer();

        $missingToken = User::evaluatePasswordRecoverRequest(
            ['user_main' => ['password' => 'new-secret']],
            $server,
            $session
        );
        $foreignScope = User::evaluatePasswordRecoverRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'user_main' => ['password' => 'new-secret'],
            ],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testPasswordRecoverRejectsInvalidPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.passwordRecover');
        $server = $this->sameSitePostServer();

        $missingPayload = User::evaluatePasswordRecoverRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $server,
            $session
        );
        $emptyPassword = User::evaluatePasswordRecoverRequest(
            [Csrf::DEFAULT_FIELD => $token, 'user_main' => ['password' => '']],
            $server,
            $session
        );

        $this->assertSame(400, $missingPayload['status']);
        $this->assertSame('Invalid password recovery payload', $missingPayload['body']);
        $this->assertSame(400, $emptyPassword['status']);
        $this->assertSame('Invalid password recovery payload', $emptyPassword['body']);
    }

    public function testPasswordRecoverViewSendsCsrfToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/password_recover.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('private const USER_PASSWORD_RECOVER_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::USER_PASSWORD_RECOVER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::USER_PASSWORD_RECOVER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$userPasswordRecoverCsrfField', $view);
        $this->assertStringContainsString('$userPasswordRecoverCsrfToken', $view);
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
