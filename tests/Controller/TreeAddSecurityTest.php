<?php

declare(strict_types=1);

use App\Controller\Tree;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class TreeAddSecurityTest extends TestCase
{
    public function testTreeAddRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tree.add');

        $outcome = Tree::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'menu' => [
                    'title' => 'Dashboard',
                    'url' => '{LINK}dashboard/index',
                    'icon' => 'fa fa-home',
                    'class' => 'Dashboard',
                    'method' => 'index',
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('Dashboard', $outcome['menu']['title']);
        $this->assertSame('{LINK}dashboard/index', $outcome['menu']['url']);
    }

    public function testTreeAddRequestRejectsNonPost(): void
    {
        $outcome = Tree::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['menu']);
    }

    public function testTreeAddRequestRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tree.add');

        $outcome = Tree::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'menu' => ['title' => 'Dashboard']],
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
        $this->assertNull($outcome['menu']);
    }

    public function testTreeAddRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'tree.update');
        $server = $this->sameSitePostServer();

        $missingToken = Tree::evaluateAddRequest(
            ['menu' => ['title' => 'Dashboard']],
            $server,
            $session
        );
        $foreignScope = Tree::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'menu' => ['title' => 'Dashboard']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testTreeAddPayloadRejectsMalformedOrUnexpectedFields(): void
    {
        $this->assertNull(Tree::normalizeAddPayload([]));
        $this->assertNull(Tree::normalizeAddPayload(['menu' => 'Dashboard']));
        $this->assertNull(Tree::normalizeAddPayload(['menu' => []]));
        $this->assertNull(Tree::normalizeAddPayload(['menu' => ['active' => '1']]));
        $this->assertNull(Tree::normalizeAddPayload(['menu' => ['title' => ['Dashboard']]]));
        $this->assertNull(Tree::normalizeAddPayload(['menu' => [0 => 'Dashboard']]));

        $this->assertSame(
            ['title' => 'Dashboard', 'method' => 'index'],
            Tree::normalizeAddPayload(['menu' => ['title' => 'Dashboard', 'method' => 'index']])
        );
    }

    public function testExternalPostWouldHaveReachedLegacyMutationButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'tree.add');
        $post = [Csrf::DEFAULT_FIELD => $token, 'menu' => ['title' => 'Owned']];

        $this->assertSame(['title' => 'Owned'], Tree::normalizeAddPayload($post));

        $outcome = Tree::evaluateAddRequest(
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
        $this->assertNull($outcome['menu']);
    }

    public function testTreeAddUsesSharedCsrfLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Tree.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Tree/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString("private const TREE_ADD_CSRF_SCOPE = 'tree.add'", $controller);
        $this->assertStringContainsString('private const TREE_ADD_FIELDS', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::TREE_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::TREE_ADD_CSRF_SCOPE)', $controller);
        $this->assertMatchesRegularExpression(
            '/if \\(CsrfGuard::isPost\\(\\$_SERVER\\)\\) \\{\\s+\\$this->view\\s*=\\s*false;\\s+\\$this->layout_name\\s*=\\s*false;/s',
            $controller
        );
        $this->assertStringContainsString("\$tree->add(\$outcome['menu'], \$id_parent);", $controller);

        $this->assertStringContainsString('$treeAddCsrfField', $view);
        $this->assertStringContainsString('$treeAddCsrfToken', $view);
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
