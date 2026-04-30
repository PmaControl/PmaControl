<?php

declare(strict_types=1);

use App\Controller\Query;
use PHPUnit\Framework\TestCase;

final class QueryGraphFeatureTest extends TestCase
{
    public function testGraphRequestAllowsGetRouteAndNormalizesNullSchema(): void
    {
        $outcome = Query::evaluateGraphRequest(
            [],
            ['REQUEST_METHOD' => 'GET'],
            ['12', 'NULL', '0123456789abcdef0123456789abcdef']
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(12, $outcome['id_mysql_server']);
        $this->assertSame('', $outcome['schema_name']);
        $this->assertSame('0123456789ABCDEF0123456789ABCDEF', $outcome['digest']);
    }

    public function testGraphRequestRejectsPostAndRawSqlGet(): void
    {
        $post = Query::evaluateGraphRequest(
            [],
            ['REQUEST_METHOD' => 'POST'],
            ['12', 'app', '0123456789abcdef0123456789abcdef']
        );
        $this->assertFalse($post['allowed']);
        $this->assertSame(405, $post['status']);
        $this->assertSame('GET', $post['headers']['Allow']);

        $put = Query::evaluateGraphRequest(
            [],
            ['REQUEST_METHOD' => 'PUT'],
            ['12', 'app', '0123456789abcdef0123456789abcdef']
        );
        $this->assertFalse($put['allowed']);
        $this->assertSame(405, $put['status']);

        $rawSql = Query::evaluateGraphRequest(
            ['sql' => 'SELECT * FROM users'],
            ['REQUEST_METHOD' => 'GET'],
            ['12', 'app', '0123456789abcdef0123456789abcdef']
        );
        $this->assertFalse($rawSql['allowed']);
        $this->assertSame(400, $rawSql['status']);
    }

    public function testGraphRequestRejectsUnsafeRouteValues(): void
    {
        $this->assertFalse(Query::evaluateGraphRequest([], ['REQUEST_METHOD' => 'GET'], ['0', 'app', '0123456789abcdef'])['allowed']);
        $this->assertFalse(Query::evaluateGraphRequest([], ['REQUEST_METHOD' => 'GET'], ['1', 'app', 'not-a-digest'])['allowed']);
        $this->assertFalse(Query::evaluateGraphRequest([], ['REQUEST_METHOD' => 'GET'], [['1'], 'app', '0123456789abcdef'])['allowed']);
    }

    public function testGraphPathUsesRouteSegmentsOnly(): void
    {
        $this->assertSame(
            'Query/graph/7/app/0123456789abcdef/',
            Query::buildGraphPath(7, 'app', '0123456789abcdef')
        );
        $this->assertSame(
            'Query/digest/7/NULL/0123456789abcdef/',
            Query::buildDigestPath(7, '', '0123456789abcdef')
        );
    }

    public function testGraphLinksAreWiredFromDigestAndMonitoringViews(): void
    {
        $digestView = file_get_contents(__DIR__ . '/../../App/view/Query/digest.view.php');
        $monitoringView = file_get_contents(__DIR__ . '/../../App/view/Monitoring/query.view.php');
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Query.php');

        $this->assertIsString($digestView);
        $this->assertIsString($monitoringView);
        $this->assertIsString($controller);

        $this->assertStringContainsString('Query::buildGraphPath', $digestView);
        $this->assertStringContainsString('Query::buildGraphPath', $monitoringView);
        $this->assertStringContainsString('evaluateGraphRequest($_GET, $_SERVER, $param)', $controller);
        $this->assertStringContainsString("array_key_exists('sql', \$get)", $controller);
    }
}
