<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserUpdateIdGroupSecurityTest extends TestCase
{
    public function testUpdateIdGroupAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.updateIdGroup');

        $outcome = User::evaluateUpdateIdGroupRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => [
                    '7' => ['id_group' => '2'],
                    '8' => ['id_group' => '3'],
                ],
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
        $this->assertSame([7 => 2, 8 => 3], $outcome['updates']);
    }

    public function testUpdateIdGroupRejectsNonPost(): void
    {
        $outcome = User::evaluateUpdateIdGroupRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testUpdateIdGroupRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.updateIdGroup');

        $outcome = User::evaluateUpdateIdGroupRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'user_main' => ['7' => ['id_group' => '2']],
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

    public function testUpdateIdGroupRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.profile');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];

        $missingToken = User::evaluateUpdateIdGroupRequest(
            ['user_main' => ['7' => ['id_group' => '2']]],
            $server,
            $session
        );
        $foreignScope = User::evaluateUpdateIdGroupRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'user_main' => ['7' => ['id_group' => '2']],
            ],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testUpdateIdGroupRejectsInvalidPayload(): void
    {
        $this->assertNull(User::normalizeUpdateIdGroupPayload([]));
        $this->assertNull(User::normalizeUpdateIdGroupPayload(['user_main' => []]));
        $this->assertNull(User::normalizeUpdateIdGroupPayload(['user_main' => ['0' => ['id_group' => '2']]]));
        $this->assertNull(User::normalizeUpdateIdGroupPayload(['user_main' => ['7' => ['id_group' => '0']]]));
        $this->assertNull(User::normalizeUpdateIdGroupPayload(['user_main' => ['7' => ['id_group' => '2; DROP']]]));
    }

    public function testUserIndexViewSendsCsrfToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/index.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('Csrf::issueToken(', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::USER_UPDATE_IDGROUP_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$userUpdateIdGroupCsrfField', $view);
        $this->assertStringContainsString('$userUpdateIdGroupCsrfToken', $view);
        $this->assertStringContainsString('user/update_idgroup', $view);
    }
}
