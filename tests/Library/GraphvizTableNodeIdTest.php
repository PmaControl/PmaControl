<?php

declare(strict_types=1);

if (!defined('TMP')) {
    define('TMP', '/tmp/');
}

use App\Library\Graphviz;
use PHPUnit\Framework\TestCase;

final class GraphvizTableNodeIdTest extends TestCase
{
    public function testTableNodeIdUsesStableDotSafeHash(): void
    {
        $id = Graphviz::tableNodeId('shop', 'order-items');

        $this->assertMatchesRegularExpression('/^table_[a-f0-9]{16}$/', $id);
        $this->assertSame($id, Graphviz::tableNodeId('shop', 'order-items'));
        $this->assertNotSame($id, Graphviz::tableNodeId('shop', 'order_items'));
    }

    public function testTableNodeRefKeepsUnsafeTableNameOutOfDotIdentifier(): void
    {
        $ref = Graphviz::tableNodeRef('shop', 'order-items', 'a1');

        $this->assertMatchesRegularExpression('/^"table_[a-f0-9]{16}":a1$/', $ref);
        $this->assertStringNotContainsString('order-items', $ref);
    }
}
