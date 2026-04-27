<?php

declare(strict_types=1);

use App\Controller\Tree;
use PHPUnit\Framework\TestCase;

final class TreeIndexNavigationTest extends TestCase
{
    public function testTreeIndexAcceptsMenuSelectionThroughGet(): void
    {
        $outcome = Tree::evaluateIndexRequest(
            ['menu' => ['id' => '7']],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(302, $outcome['status']);
        $this->assertSame(7, $outcome['menu_id']);
    }

    public function testTreeIndexIgnoresInvalidMenuSelection(): void
    {
        $this->assertNull(Tree::normalizeIndexMenuSelection([]));
        $this->assertNull(Tree::normalizeIndexMenuSelection(['menu' => '7']));
        $this->assertNull(Tree::normalizeIndexMenuSelection(['menu' => ['id' => '0']]));
        $this->assertNull(Tree::normalizeIndexMenuSelection(['menu' => ['id' => '-1']]));
        $this->assertNull(Tree::normalizeIndexMenuSelection(['menu' => ['id' => '7 OR 1=1']]));
        $this->assertNull(Tree::normalizeIndexMenuSelection(['menu' => ['id' => ['7']]]));
    }

    public function testTreeIndexRejectsResidualPostSurface(): void
    {
        $outcome = Tree::evaluateIndexRequest(
            ['menu' => ['id' => '7']],
            ['REQUEST_METHOD' => 'POST']
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertNull($outcome['menu_id']);
    }

    public function testExternalPostWouldHaveMatchedLegacyPostBranchButIsRejected(): void
    {
        $legacyPost = ['menu' => ['id' => '7']];

        $this->assertNotEmpty($legacyPost['menu']['id']);

        $outcome = Tree::evaluateIndexRequest(
            $legacyPost,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ]
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertNull($outcome['menu_id']);
    }

    public function testTreeIndexViewDoesNotEmitPostForms(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Tree/index.view.php');

        $this->assertIsString($view);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertSame(2, substr_count($view, 'method="get"'));
    }

    public function testTreeIndexDisablesRenderingWhenPostIsRejected(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Tree.php');

        $this->assertIsString($controller);
        $this->assertMatchesRegularExpression(
            "/if \\(\\\$indexRequest\\['status'\\] === 405\\) \\{\\s+\\\$this->view\\s*=\\s*false;\\s+\\\$this->layout_name\\s*=\\s*false;\\s+self::sendTreeIndexError/s",
            $controller
        );
    }
}
