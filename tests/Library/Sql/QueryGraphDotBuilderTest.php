<?php

declare(strict_types=1);

use App\Library\Sql\QueryGraphDotBuilder;
use App\Library\Sql\QueryGraphExtractor;
use PHPUnit\Framework\TestCase;

final class QueryGraphDotBuilderTest extends TestCase
{
    public function testBuildsDotGraphWithTablesAndJoinEdges(): void
    {
        $graph = QueryGraphExtractor::extract('SELECT u.id FROM users u JOIN orders o ON u.id = o.user_id');

        $dot = QueryGraphDotBuilder::build($graph);

        $this->assertStringContainsString('digraph query_graph', $dot);
        $this->assertStringContainsString('users', $dot);
        $this->assertStringContainsString('orders', $dot);
        $this->assertStringContainsString('id = user_id', $dot);
    }

    public function testEscapesDotHtmlLabels(): void
    {
        $dot = QueryGraphDotBuilder::build([
            'tables' => [
                [
                    'database' => null,
                    'table' => '<script>alert(1)</script>',
                    'alias' => 'x',
                    'source' => 'FROM',
                ],
            ],
            'joins' => [],
            'where_fields' => [],
        ]);

        $this->assertStringNotContainsString('<script>', $dot);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $dot);
    }
}
