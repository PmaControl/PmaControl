<?php

declare(strict_types=1);

use App\Controller\Tag;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TagAddSecurityTest extends TestCase
{
    public function testTagAddRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tag.add');

        $outcome = Tag::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'tag' => [
                    'name' => 'Production',
                    'color' => '#ffffff',
                    'background' => 'green',
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            ['name' => 'Production', 'color' => '#ffffff', 'background' => 'green'],
            $outcome['tag']
        );
    }

    public function testTagAddRequestRejectsNonPost(): void
    {
        $outcome = Tag::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['tag']);
    }

    public function testTagAddRequestRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tag.add');

        $outcome = Tag::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'tag' => ['name' => 'Owned']],
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
        $this->assertNull($outcome['tag']);
    }

    public function testTagAddRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tag.update');
        $server = $this->sameSitePostServer();

        $missingToken = Tag::evaluateAddRequest(
            ['tag' => ['name' => 'Production']],
            $server,
            $session
        );
        $foreignScope = Tag::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'tag' => ['name' => 'Production']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testTagAddPayloadRejectsMalformedOrUnexpectedFields(): void
    {
        $this->assertNull(Tag::normalizeAddPayload([]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => 'Production']));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => []]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => ['id' => '7', 'name' => 'Production']]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => ['name' => '']]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => ['name' => str_repeat('a', 51)]]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => ['color' => str_repeat('a', 21), 'name' => 'Production']]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => ['background' => str_repeat('a', 21), 'name' => 'Production']]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => ['name' => ['Production']]]));
        $this->assertNull(Tag::normalizeAddPayload(['tag' => [0 => 'Production']]));

        $this->assertSame(
            ['name' => 'Production', 'color' => '', 'background' => ''],
            Tag::normalizeAddPayload(['tag' => ['name' => ' Production ']])
        );
    }

    public function testExternalPostWouldHaveReachedLegacyMutationButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tag.add');
        $post = [Csrf::DEFAULT_FIELD => $token, 'tag' => ['name' => 'Owned']];

        $this->assertSame(
            ['name' => 'Owned', 'color' => '', 'background' => ''],
            Tag::normalizeAddPayload($post)
        );

        $outcome = Tag::evaluateAddRequest(
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
        $this->assertNull($outcome['tag']);
    }

    public function testTagAddUsesSharedCsrfLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Tag.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Tag/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString("private const TAG_ADD_CSRF_SCOPE = 'tag.add'", $controller);
        $this->assertStringContainsString('private const TAG_ADD_FIELDS', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::TAG_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::TAG_ADD_CSRF_SCOPE)', $controller);
        $this->assertMatchesRegularExpression(
            '/if \\(CsrfGuard::isPost\\(\\$_SERVER\\)\\) \\{\\s+\\$this->view\\s*=\\s*false;\\s+\\$this->layout_name\\s*=\\s*false;/s',
            $controller
        );
        $this->assertStringContainsString("\$save['tag'] = \$outcome['tag'];", $controller);

        $this->assertStringContainsString('$tagAddCsrfField', $view);
        $this->assertStringContainsString('$tagAddCsrfToken', $view);
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
