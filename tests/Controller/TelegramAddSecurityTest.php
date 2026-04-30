<?php

declare(strict_types=1);

use App\Controller\Telegram;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TelegramAddSecurityTest extends TestCase
{
    public function testTelegramAddAcceptsValidPostShape(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'telegram.add');

        $outcome = Telegram::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'telegram_bot' => [
                    'token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab',
                    'chat_id' => '-1001234567890',
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab', $outcome['bot']['token']);
        $this->assertSame('-1001234567890', $outcome['bot']['chat_id']);
    }

    public function testTelegramAddRejectsNonPost(): void
    {
        $outcome = Telegram::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['bot']);
    }

    public function testTelegramAddRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'telegram.add');

        $outcome = Telegram::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'telegram_bot' => ['token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab', 'chat_id' => '-42']],
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
        $this->assertNull($outcome['bot']);
    }

    public function testTelegramAddRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tree.add');
        $server = $this->sameSitePostServer();

        $missingToken = Telegram::evaluateAddRequest(
            ['telegram_bot' => ['token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab', 'chat_id' => '-42']],
            $server,
            $session
        );
        $foreignScope = Telegram::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'telegram_bot' => ['token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab', 'chat_id' => '-42']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testTelegramAddPayloadRejectsUnexpectedOrNonScalarFields(): void
    {
        $this->assertSame(['token' => '', 'chat_id' => ''], Telegram::normalizeAddPayload([]));
        $this->assertNull(Telegram::normalizeAddPayload(['telegram_bot' => 'token']));
        $this->assertNull(Telegram::normalizeAddPayload(['telegram_bot' => ['enabled' => '1']]));
        $this->assertNull(Telegram::normalizeAddPayload(['telegram_bot' => ['token' => ['nested']]]));
        $this->assertNull(Telegram::normalizeAddPayload(['telegram_bot' => [0 => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab']]));
        $this->assertNull(Telegram::normalizeAddPayload(['telegram_bot' => ['token' => str_repeat('a', 256)]]));
    }

    public function testTelegramAddLocalFormatValidation(): void
    {
        $this->assertTrue(Telegram::isTelegramTokenFormat('123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab'));
        $this->assertFalse(Telegram::isTelegramTokenFormat('not-a-token'));
        $this->assertFalse(Telegram::isTelegramTokenFormat('123456:short'));

        $this->assertTrue(Telegram::isTelegramChatIdFormat('-1001234567890'));
        $this->assertTrue(Telegram::isTelegramChatIdFormat('@valid_channel_1'));
        $this->assertFalse(Telegram::isTelegramChatIdFormat('group name'));
        $this->assertFalse(Telegram::isTelegramChatIdFormat('@bad'));
    }

    public function testExternalPostWouldHaveReachedLegacyValidationButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'telegram.add');
        $post = [
            Csrf::DEFAULT_FIELD => $token,
            'telegram_bot' => [
                'token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_ab',
                'chat_id' => '-42',
            ],
        ];

        $this->assertSame('-42', Telegram::normalizeAddPayload($post)['chat_id']);

        $outcome = Telegram::evaluateAddRequest(
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertNull($outcome['bot']);
    }

    public function testTelegramAddUsesSharedCsrfLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Telegram.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Telegram/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString("private const TELEGRAM_ADD_CSRF_SCOPE = 'telegram.add'", $controller);
        $this->assertStringContainsString('private const TELEGRAM_ADD_FIELDS', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::TELEGRAM_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::TELEGRAM_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('$this->view        = false;', $controller);
        $this->assertStringContainsString('$db = Sgbd::sql(DB_DEFAULT);', $controller);

        $this->assertStringContainsString('$telegramAddCsrfField', $view);
        $this->assertStringContainsString('$telegramAddCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);
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
