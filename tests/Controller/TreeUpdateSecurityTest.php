<?php

declare(strict_types=1);

use App\Controller\Tree;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TreeUpdateSecurityTest extends TestCase
{
    public function testTreeUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tree.update');

        $outcome = Tree::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'title', 'value' => 'Dashboard', 'pk' => '7'],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['field' => 'title', 'value' => 'Dashboard', 'id' => 7], $outcome['update']);
    }

    public function testTreeUpdateRequestRejectsNonPost(): void
    {
        $outcome = Tree::evaluateUpdateRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testTreeUpdateRequestRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tree.update');

        $outcome = Tree::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $token, 'name' => 'title', 'value' => 'Dashboard', 'pk' => '7'],
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

    public function testTreeUpdateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'worker.update');
        $server = $this->sameSitePostServer();

        $missingToken = Tree::evaluateUpdateRequest(
            ['name' => 'title', 'value' => 'Dashboard', 'pk' => '7'],
            $server,
            $session
        );
        $foreignScope = Tree::evaluateUpdateRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'name' => 'title', 'value' => 'Dashboard', 'pk' => '7'],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testTreeUpdatePayloadRejectsUnknownColumnsAndInvalidIds(): void
    {
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => 'query', 'value' => 'SELECT 1', 'pk' => '7']));
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => 'title` = 1 --', 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => 'title', 'value' => 'x', 'pk' => '0']));
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => 'title', 'value' => 'x', 'pk' => '7 OR 1=1']));
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => 'title', 'pk' => '7']));
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => ['title'], 'value' => 'x', 'pk' => '7']));
        $this->assertNull(Tree::normalizeUpdatePayload(['name' => 'title', 'value' => ['x'], 'pk' => '7']));
    }

    public function testTreeUpdateSqlUsesNormalizedFieldIdAndEscapedValue(): void
    {
        $sql = Tree::buildTreeUpdateSql(
            ['field' => 'title', 'value' => "Bob's menu", 'id' => 7],
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("UPDATE menu SET `title` = 'Bob\\'s menu' WHERE id = 7", $sql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tree.update');
        $post = [Csrf::DEFAULT_FIELD => $token, 'name' => 'title', 'value' => 'Owned', 'pk' => '7'];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame(
            "UPDATE menu SET `title` = 'Owned' WHERE id = 7",
            Tree::buildTreeUpdateSql(Tree::normalizeUpdatePayload($post), static fn (string $value): string => $value)
        );

        $outcome = Tree::evaluateUpdateRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['update']);
    }

    public function testTreeUpdateUsesSharedCsrfLibraryAndInlineEditSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Tree.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Tree/index.view.php');
        $javascript = file_get_contents(__DIR__ . '/../../App/Webroot/js/Tree/index.js');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($javascript);

        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString("private const TREE_UPDATE_CSRF_SCOPE = 'tree.update'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::TREE_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::TREE_UPDATE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('private const TREE_UPDATE_FIELDS', $controller);

        $this->assertStringContainsString('$treeUpdateCsrfAttributes', $view);
        $this->assertStringContainsString('data-csrf-field="', $view);
        $this->assertStringContainsString('data-csrf-token="', $view);
        $this->assertStringContainsString('tree/update', $view);
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
