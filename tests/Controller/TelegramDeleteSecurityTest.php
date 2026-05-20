<?php

declare(strict_types=1);

use App\Controller\Telegram;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TelegramDeleteSecurityTest extends TestCase
{
    public function testDeleteRequestAcceptsValidPostTokenAndMatchingId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'telegram.delete');

        $outcome = Telegram::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id_telegram_bot' => '7'],
            $this->sameSitePostServer(),
            $session,
            ['7']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id_telegram_bot']);
    }

    public function testDeleteRequestRejectsNonPost(): void
    {
        $outcome = Telegram::evaluateDeleteRequest([], ['REQUEST_METHOD' => 'GET'], [], ['7']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id_telegram_bot']);
    }

    public function testDeleteRequestRejectsExternalOriginBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'telegram.delete');

        $outcome = Telegram::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id_telegram_bot' => '7'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            ['7']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['id_telegram_bot']);
    }

    public function testDeleteRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'telegram.add');
        $server = $this->sameSitePostServer();

        $missingToken = Telegram::evaluateDeleteRequest(['id_telegram_bot' => '7'], $server, $session, ['7']);
        $foreignScope = Telegram::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'id_telegram_bot' => '7'],
            $server,
            $session,
            ['7']
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testDeleteRequestRejectsInvalidAndMismatchedIdsAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'telegram.delete');
        $server = $this->sameSitePostServer();

        foreach ([[], ['0'], ['bad'], [['7']]] as $param) {
            $outcome = Telegram::evaluateDeleteRequest(
                [Csrf::DEFAULT_FIELD => $token],
                $server,
                $session,
                $param
            );

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid telegram bot id', $outcome['body']);
            $this->assertNull($outcome['id_telegram_bot']);
        }

        $mismatch = Telegram::evaluateDeleteRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id_telegram_bot' => '8'],
            $server,
            $session,
            ['7']
        );

        $this->assertSame(400, $mismatch['status']);
        $this->assertSame('Invalid telegram bot id', $mismatch['body']);
        $this->assertNull($mismatch['id_telegram_bot']);
    }

    public function testCliDeleteBypassesBrowserCsrfAfterIdValidation(): void
    {
        $valid = Telegram::evaluateDeleteRequest([], ['REQUEST_METHOD' => 'GET'], [], ['7'], true);
        $invalid = Telegram::evaluateDeleteRequest([], ['REQUEST_METHOD' => 'GET'], [], ['bad'], true);

        $this->assertSame(200, $valid['status']);
        $this->assertSame(7, $valid['id_telegram_bot']);
        $this->assertSame(400, $invalid['status']);
        $this->assertNull($invalid['id_telegram_bot']);
    }

    public function testDeleteRedirectTargetIgnoresExternalRequestInputs(): void
    {
        $link = defined('LINK') ? LINK : '/';

        $this->assertSame($link . 'telegram/index', Telegram::deleteRedirectTarget());
    }

    public function testTelegramDeleteUsesPostCsrfAndSafeInternalRedirect(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Telegram.php');
        $indexView = (string) file_get_contents(__DIR__ . '/../../App/view/Telegram/index.view.php');
        $detailView = (string) file_get_contents(__DIR__ . '/../../App/view/Telegram/view.view.php');

        $deleteStart = strpos($controller, 'public function delete');
        $guardPosition = strpos($controller, 'evaluateDeleteRequest', $deleteStart);
        $fetchPosition = strpos($controller, '$bot = $this->getBotById', $deleteStart);
        $deleteSqlPosition = strpos($controller, 'DELETE FROM telegram_bot', $deleteStart);

        $this->assertNotFalse($deleteStart);
        $this->assertNotFalse($guardPosition);
        $this->assertNotFalse($fetchPosition);
        $this->assertNotFalse($deleteSqlPosition);
        $this->assertLessThan($fetchPosition, $guardPosition);
        $this->assertLessThan($deleteSqlPosition, $guardPosition);
        $this->assertStringContainsString("private const TELEGRAM_DELETE_CSRF_SCOPE = 'telegram.delete'", $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::TELEGRAM_DELETE_CSRF_SCOPE)', $controller);
        $this->assertSame(2, substr_count($controller, 'Csrf::issueToken($_SESSION, self::TELEGRAM_DELETE_CSRF_SCOPE)'));
        $this->assertStringContainsString('header("location: " . self::deleteRedirectTarget());', $controller);
        $this->assertStringContainsString("self::sendTelegramDeleteError(404, 'Telegram bot not found');", $controller);
        $this->assertStringNotContainsString('HTTP_REFERER', $controller);
        $this->assertStringNotContainsString('$_GET[\'redirect\']', $controller);

        foreach ([$indexView, $detailView] as $view) {
            $this->assertStringContainsString('use App\\Library\\Security\\CsrfRender;', $view);
            $this->assertStringContainsString('$telegramDeleteCsrfField', $view);
            $this->assertStringContainsString('$telegramDeleteCsrfToken', $view);
            $this->assertStringContainsString('<form method="post" action="', $view);
            $this->assertStringContainsString('telegram/delete/', $view);
            $this->assertStringContainsString('name="id_telegram_bot"', $view);
            $this->assertStringNotContainsString('href="\'.LINK.\'telegram/delete/', $view);
        }
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
