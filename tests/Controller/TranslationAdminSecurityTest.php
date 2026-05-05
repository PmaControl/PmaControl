<?php

declare(strict_types=1);

use App\Controller\Translation;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TranslationAdminSecurityTest extends TestCase
{
    public function testAdminTranslationAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'translation.admin_translation');

        $outcome = Translation::evaluateAdminTranslationRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'field-to-update' => 'id-12;id-13;',
                'none' => ['id_to' => 'fr'],
                'id-12' => 'Bonjour',
                'id-13' => '',
            ],
            $this->sameSitePostServer(),
            $session,
            ['en', 'fr']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('fr', $outcome['target']);
        $this->assertSame(
            [
                ['id' => 12, 'text' => 'Bonjour'],
                ['id' => 13, 'text' => ''],
            ],
            $outcome['updates']
        );
    }

    public function testAdminTranslationRejectsNonPost(): void
    {
        $outcome = Translation::evaluateAdminTranslationRequest([], ['REQUEST_METHOD' => 'GET'], [], ['fr']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertSame([], $outcome['updates']);
    }

    public function testAdminTranslationRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'translation.admin_translation');

        $outcome = Translation::evaluateAdminTranslationRequest(
            [Csrf::DEFAULT_FIELD => $token, 'field-to-update' => 'id-12;', 'none' => ['id_to' => 'fr'], 'id-12' => 'Bonjour'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            ['fr']
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testAdminTranslationRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tree.add');
        $server = $this->sameSitePostServer();

        $missingToken = Translation::evaluateAdminTranslationRequest(
            ['field-to-update' => 'id-12;', 'none' => ['id_to' => 'fr'], 'id-12' => 'Bonjour'],
            $server,
            $session,
            ['fr']
        );
        $foreignScope = Translation::evaluateAdminTranslationRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'field-to-update' => 'id-12;', 'none' => ['id_to' => 'fr'], 'id-12' => 'Bonjour'],
            $server,
            $session,
            ['fr']
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testAdminTranslationPayloadRejectsInvalidTargetsAndKeys(): void
    {
        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => 'id-12;', 'none' => ['id_to' => 'fr;DROP'], 'id-12' => 'Bonjour'],
            ['fr']
        ));
        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => 'id-0;', 'none' => ['id_to' => 'fr'], 'id-0' => 'Bonjour'],
            ['fr']
        ));
        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => 'id-12 OR 1=1;', 'none' => ['id_to' => 'fr'], 'id-12 OR 1=1' => 'Bonjour'],
            ['fr']
        ));
        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => 'id-12;id-12;', 'none' => ['id_to' => 'fr'], 'id-12' => 'Bonjour'],
            ['fr']
        ));
        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => ['id-12;'], 'none' => ['id_to' => 'fr'], 'id-12' => 'Bonjour'],
            ['fr']
        ));
        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => 'id-12;', 'none' => ['id_to' => 'fr'], 'id-12' => ['Bonjour']],
            ['fr']
        ));
    }

    public function testAdminTranslationPayloadRejectsTooManyUpdatesOrTooLongText(): void
    {
        $keys = [];
        for ($i = 1; $i <= 501; $i++) {
            $keys[] = 'id-' . $i;
        }

        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => implode(';', $keys) . ';', 'none' => ['id_to' => 'fr']],
            ['fr']
        ));

        $this->assertNull(Translation::normalizeAdminTranslationPayload(
            ['field-to-update' => 'id-12;', 'none' => ['id_to' => 'fr'], 'id-12' => str_repeat('a', 65536)],
            ['fr']
        ));
    }

    public function testEmptyFieldListIsAcceptedAsNoopAfterCsrf(): void
    {
        $this->assertSame(
            ['target' => null, 'updates' => []],
            Translation::normalizeAdminTranslationPayload(['field-to-update' => ''], ['fr'])
        );
    }

    public function testAdminTranslationUsesSharedCsrfLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Translation.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Translation/admin_translation.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString("private const TRANSLATION_ADMIN_CSRF_SCOPE = 'translation.admin_translation'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::TRANSLATION_ADMIN_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::TRANSLATION_ADMIN_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Sgbd::sql(DB_DEFAULT)', $controller);
        $this->assertStringContainsString("'translation_'.\$postOutcome['target']", $controller);

        $this->assertStringContainsString('$translationAdminCsrfField', $view);
        $this->assertStringContainsString('$translationAdminCsrfToken', $view);
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
