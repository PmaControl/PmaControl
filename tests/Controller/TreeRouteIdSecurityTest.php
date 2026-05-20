<?php

declare(strict_types=1);

use App\Controller\Tree;
use PHPUnit\Framework\TestCase;

final class TreeRouteIdSecurityTest extends TestCase
{
    public function testTreeRoutePositiveIntegerNormalizerAcceptsOnlyPositiveIntegerScalars(): void
    {
        $this->assertSame(1, Tree::normalizeRoutePositiveInteger([], 0, 1));
        $this->assertSame(1, Tree::normalizeRoutePositiveInteger([''], 0, 1));
        $this->assertSame(1, Tree::normalizeRoutePositiveInteger('', 0, 1));
        $this->assertSame(7, Tree::normalizeRoutePositiveInteger(['7'], 0));
        $this->assertSame(7, Tree::normalizeRoutePositiveInteger([' 7 '], 0));
        $this->assertSame(7, Tree::normalizeRoutePositiveInteger('7', 0));

        $this->assertNull(Tree::normalizeRoutePositiveInteger([], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger('', 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger(['0'], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger(['-1'], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger(['1 OR 1=1'], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger(['1; DROP TABLE menu'], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger(["1\r\nLocation: https://evil.test"], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger([['1']], 0));
        $this->assertNull(Tree::normalizeRoutePositiveInteger('7', 1));
    }

    public function testTreeAddParentRouteKeepsLegacyRootNullButRejectsPayloads(): void
    {
        $this->assertSame('NULL', Tree::normalizeRouteTreeParentId(['1', 'NULL'], 1));
        $this->assertSame('NULL', Tree::normalizeRouteTreeParentId(['1', 'null'], 1));
        $this->assertSame(42, Tree::normalizeRouteTreeParentId(['1', '42'], 1));

        $this->assertNull(Tree::normalizeRouteTreeParentId('', 1));
        $this->assertNull(Tree::normalizeRouteTreeParentId(['1'], 1));
        $this->assertNull(Tree::normalizeRouteTreeParentId(['1', ''], 1));
        $this->assertNull(Tree::normalizeRouteTreeParentId(['1', 'NULL OR 1=1'], 1));
        $this->assertNull(Tree::normalizeRouteTreeParentId(['1', '42 OR 1=1'], 1));
        $this->assertNull(Tree::normalizeRouteTreeParentId(['1', "42\r\nLocation: https://evil.test"], 1));
    }

    public function testTreeControllerUsesSharedRouteIntegerNormalizerBeforeSqlSinks(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Tree.php');

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeSingle($routeParams[$offset])', $controller);
        $this->assertStringContainsString('$routeParams = self::normalizeRouteParameters($param);', $controller);
        $this->assertStringContainsString('$data[\'id_menu\'] = $idMenu;', $controller);
        $this->assertStringContainsString('$id_menu = self::normalizeRoutePositiveInteger($param, 0);', $controller);
        $this->assertStringContainsString('$id      = self::normalizeRoutePositiveInteger($param, 1);', $controller);
        $this->assertStringContainsString('$id_parent = self::normalizeRouteTreeParentId($param, 1);', $controller);
        $this->assertStringContainsString("self::sendTreeRouteError(400, 'Invalid tree route');", $controller);

        $this->assertStringNotContainsString('$data[\'id_menu\'] = $param[0];', $controller);
        $this->assertStringNotContainsString('$id_menu = $param[0];', $controller);
        $this->assertStringNotContainsString('$id      = $param[1];', $controller);
    }
}
