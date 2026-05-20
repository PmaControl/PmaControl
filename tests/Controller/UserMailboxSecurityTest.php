<?php

declare(strict_types=1);

use App\Controller\User;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class UserMailboxSecurityTest extends TestCase
{
    public function testMailboxAcceptsValidComposePost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.mailbox');

        $outcome = User::evaluateMailboxRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'mailbox_main' => ['id_user_main__to' => '42'],
            ],
            $this->sameSitePostServer(),
            $session,
            ['compose']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(42, $outcome['recipient_id']);
    }

    public function testMailboxRejectsNonPost(): void
    {
        $outcome = User::evaluateMailboxRequest([], ['REQUEST_METHOD' => 'GET'], [], ['compose']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testMailboxRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.mailbox');

        $outcome = User::evaluateMailboxRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'mailbox_main' => ['id_user_main__to' => '42'],
            ],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            ['compose']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testMailboxRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.profile');
        $server = $this->sameSitePostServer();

        $missingToken = User::evaluateMailboxRequest(
            ['mailbox_main' => ['id_user_main__to' => '42']],
            $server,
            $session,
            ['compose']
        );
        $foreignScope = User::evaluateMailboxRequest(
            [
                Csrf::DEFAULT_FIELD => $foreignToken,
                'mailbox_main' => ['id_user_main__to' => '42'],
            ],
            $server,
            $session,
            ['compose']
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testMailboxRejectsPostOutsideCompose(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.mailbox');

        $outcome = User::evaluateMailboxRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'mailbox_main' => ['id_user_main__to' => '42'],
            ],
            $this->sameSitePostServer(),
            $session,
            ['inbox']
        );

        $this->assertSame(400, $outcome['status']);
        $this->assertSame('Invalid mailbox action', $outcome['body']);
    }

    public function testMailboxRejectsInvalidRecipientPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'user.mailbox');
        $server = $this->sameSitePostServer();

        $missingPayload = User::evaluateMailboxRequest(
            [Csrf::DEFAULT_FIELD => $token],
            $server,
            $session,
            ['compose']
        );
        $invalidRecipient = User::evaluateMailboxRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'mailbox_main' => ['id_user_main__to' => '42 OR 1=1'],
            ],
            $server,
            $session,
            ['compose']
        );

        $this->assertSame(400, $missingPayload['status']);
        $this->assertSame('Invalid mailbox payload', $missingPayload['body']);
        $this->assertSame(400, $invalidRecipient['status']);
        $this->assertSame('Invalid mailbox payload', $invalidRecipient['body']);
    }

    public function testMailboxViewSendsCsrfTokenAndControllerWrapsTrait(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/User/mailbox.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('mailbox as private legacyMailbox', $controller);
        $this->assertStringContainsString('private const USER_MAILBOX_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::USER_MAILBOX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::USER_MAILBOX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('$this->legacyMailbox($param)', $controller);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$userMailboxCsrfField', $view);
        $this->assertStringContainsString('$userMailboxCsrfToken', $view);
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
