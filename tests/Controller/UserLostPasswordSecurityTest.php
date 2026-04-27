<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserLostPasswordSecurityTest extends TestCase
{
    public function testLostPasswordAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.lostPassword');

        $outcome = User::evaluateLostPasswordRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['email' => ' user@example.test '],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('user@example.test', $outcome['email']);
    }

    public function testLostPasswordRejectsNonPost(): void
    {
        $outcome = User::evaluateLostPasswordRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testLostPasswordRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.lostPassword');

        $outcome = User::evaluateLostPasswordRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['email' => 'user@example.test'],
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

    public function testLostPasswordRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.passwordRecover');
        $server = $this->sameSitePostServer();

        $missingToken = User::evaluateLostPasswordRequest(
            ['user_main' => ['email' => 'user@example.test']],
            $server,
            $session
        );
        $foreignScope = User::evaluateLostPasswordRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'user_main' => ['email' => 'user@example.test'],
            ],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testLostPasswordRejectsInvalidPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.lostPassword');
        $server = $this->sameSitePostServer();

        $missingPayload = User::evaluateLostPasswordRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $server,
            $session
        );
        $invalidEmail = User::evaluateLostPasswordRequest(
            [Csrf::DEFAULT_FIELD => $token, 'user_main' => ['email' => 'not-an-email']],
            $server,
            $session
        );

        $this->assertSame(400, $missingPayload['status']);
        $this->assertSame('Invalid lost password payload', $missingPayload['body']);
        $this->assertSame(400, $invalidEmail['status']);
        $this->assertSame('Invalid lost password payload', $invalidEmail['body']);
    }

    public function testLostPasswordViewSendsCsrfToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/lost_password.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('private const USER_LOST_PASSWORD_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::USER_LOST_PASSWORD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::USER_LOST_PASSWORD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('name="<?=$userLostPasswordCsrfField?>"', $view);
        $this->assertStringContainsString('value="<?=$userLostPasswordCsrfToken?>"', $view);
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
