<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserProfileSecurityTest extends TestCase
{
    public function testProfileAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.profile');

        $outcome = User::evaluateProfileRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'shoutbox' => ['text' => 'Bonjour depuis le profil'],
            ],
            $this->sameSitePostServer(),
            $session,
            ['42'],
            ['IdUser' => '7']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('Bonjour depuis le profil', $outcome['message']);
        $this->assertSame(7, $outcome['id_user_main']);
        $this->assertSame(42, $outcome['id_user_main__box']);
    }

    public function testProfileRejectsNonPost(): void
    {
        $outcome = User::evaluateProfileRequest(
            [],
            ['REQUEST_METHOD' => 'GET'],
            [],
            ['42'],
            ['IdUser' => '7']
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testProfileRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.profile');

        $outcome = User::evaluateProfileRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'shoutbox' => ['text' => 'forged message'],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            ['42'],
            ['IdUser' => '7']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testProfileRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.register');
        $server = $this->sameSitePostServer();

        $missingToken = User::evaluateProfileRequest(
            ['shoutbox' => ['text' => 'message']],
            $server,
            $session,
            ['42'],
            ['IdUser' => '7']
        );
        $foreignScope = User::evaluateProfileRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'shoutbox' => ['text' => 'message'],
            ],
            $server,
            $session,
            ['42'],
            ['IdUser' => '7']
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testProfileRejectsInvalidPayloadAndIds(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.profile');
        $server = $this->sameSitePostServer();

        $invalidPayload = User::evaluateProfileRequest(
            [Csrf::DEFAULT_FIELD => $token, 'shoutbox' => ['text' => '']],
            $server,
            $session,
            ['42'],
            ['IdUser' => '7']
        );
        $invalidProfile = User::evaluateProfileRequest(
            [Csrf::DEFAULT_FIELD => $token, 'shoutbox' => ['text' => 'message']],
            $server,
            $session,
            ['not-an-id'],
            ['IdUser' => '7']
        );
        $invalidUser = User::evaluateProfileRequest(
            [Csrf::DEFAULT_FIELD => $token, 'shoutbox' => ['text' => 'message']],
            $server,
            $session,
            ['42'],
            ['IdUser' => '-1']
        );

        $this->assertSame(400, $invalidPayload['status']);
        $this->assertSame('Invalid profile message payload', $invalidPayload['body']);
        $this->assertSame(400, $invalidProfile['status']);
        $this->assertSame('Invalid profile message payload', $invalidProfile['body']);
        $this->assertSame(400, $invalidUser['status']);
        $this->assertSame('Invalid profile message payload', $invalidUser['body']);
    }

    public function testProfileViewSendsCsrfToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/profil.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('private const USER_PROFILE_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::USER_PROFILE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::USER_PROFILE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$userProfileCsrfField', $view);
        $this->assertStringContainsString('$userProfileCsrfToken', $view);
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
