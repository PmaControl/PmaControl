<?php

declare(strict_types=1);

use App\Controller\Tag;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TagUpdateSecurityTest extends TestCase
{
    public function testTagUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, Tag::TAG_UPDATE_CSRF_SCOPE);

        $outcome = Tag::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'color', 'value' => '#ff00aa', 'pk' => '7'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['field' => 'color', 'value' => '#ff00aa', 'id' => 7], $outcome['update']);
    }

    public function testTagUpdateRequestRejectsNonPost(): void
    {
        $outcome = Tag::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testTagUpdateRequestRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, Tag::TAG_UPDATE_CSRF_SCOPE);

        $outcome = Tag::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'color', 'value' => '#ff00aa', 'pk' => '7'],
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
        $this->assertNull($outcome['update']);
    }

    public function testTagUpdateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tree.update');
        $server = $this->sameSitePostServer();

        $missingToken = Tag::evaluateUpdateRequest(
            ['name' => 'color', 'value' => '#ff00aa', 'pk' => '7'],
            $server,
            $session
        );
        $foreignScope = Tag::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'name' => 'color', 'value' => '#ff00aa', 'pk' => '7'],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testTagUpdatePayloadRejectsUnknownColumnsInvalidIdsAndMalformedValues(): void
    {
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => 'query', 'value' => 'SELECT 1', 'pk' => '7']));
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => 'name` = 1 --', 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => 'name', 'value' => 'x', 'pk' => '0']));
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => 'name', 'value' => 'x', 'pk' => '7 OR 1=1']));
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => 'name', 'pk' => '7']));
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => ['name'], 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Tag::normalizeUpdatePayload(['name' => 'name', 'value' => ['x'], 'pk' => '7']));
    }

    public function testTagUpdateSqlUsesNormalizedFieldIdAndEscapedValue(): void
    {
        $sql = Tag::buildTagUpdateSql(
            ['field' => 'name', 'value' => "Bob's tag", 'id' => 7],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("UPDATE tag SET `name` = 'Bob\\'s tag' WHERE id = 7", $sql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, Tag::TAG_UPDATE_CSRF_SCOPE);
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'name', 'value' => 'Owned', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame(
            "UPDATE tag SET `name` = 'Owned' WHERE id = 7",
            Tag::buildTagUpdateSql(Tag::normalizeUpdatePayload($post), static fn (string $value): string => $value)
        );

        $outcome = Tag::evaluateUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['update']);
    }

    public function testTagUpdateUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $tagController = file_get_contents(__DIR__ . '/../../App/Controller/Tag.php');
        $tagView = file_get_contents(__DIR__ . '/../../App/view/Tag/index.view.php');
        $javascript = file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertIsString($tagController);
        $this->assertIsString($tagView);
        $this->assertIsString($javascript);

        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $tagController);
        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $tagController);
        $this->assertStringContainsString("public const TAG_UPDATE_CSRF_SCOPE = 'tag.update'", $tagController);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::TAG_UPDATE_CSRF_SCOPE)', $tagController);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::TAG_UPDATE_CSRF_SCOPE)', $tagController);
        $this->assertStringContainsString('private const TAG_UPDATE_FIELDS', $tagController);

        $this->assertStringContainsString('$tagUpdateCsrfAttributes', $tagView);
        $this->assertStringContainsString("CsrfRender::attributes(\$data, 'tag_update')", $tagView);
        $this->assertStringContainsString('tag/update', $tagView);
        $this->assertStringContainsString('params[csrfField] = csrfToken;', $javascript);
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
