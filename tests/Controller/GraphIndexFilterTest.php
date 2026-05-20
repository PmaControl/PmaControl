<?php

declare(strict_types=1);

use App\Controller\Graph;
use PHPUnit\Framework\TestCase;

final class GraphIndexFilterTest extends TestCase
{
    public function testIndexRequestAllowsGet(): void
    {
        $outcome = Graph::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('6 hour', $outcome['filter']['interval']);
        $this->assertNull($outcome['filter']['id_mysql_server']);
    }

    public function testIndexRequestRejectsPost(): void
    {
        $outcome = Graph::evaluateIndexRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
    }

    public function testIndexFilterNormalizesServerAndInterval(): void
    {
        $filter = Graph::normalizeIndexFilter([
            'mysql_server' => ['id' => '42'],
            'status_value_int' => ['date' => '1 day'],
        ]);

        $this->assertSame(42, $filter['id_mysql_server']);
        $this->assertSame('1 day', $filter['interval']);
    }

    public function testIndexFilterRejectsInvalidServerAndUnknownInterval(): void
    {
        $filter = Graph::normalizeIndexFilter([
            'mysql_server' => ['id' => '1 OR 1=1'],
            'status_value_int' => ['date' => '1 year'],
        ]);

        $this->assertNull($filter['id_mysql_server']);
        $this->assertSame('6 hour', $filter['interval']);
    }

    public function testIndexControllerAndViewDropPostFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Graph.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Graph/index.view.php');
        $indexStart = strpos($controller, 'public function index()');
        $indexEnd = strpos($controller, 'public function cache()');
        $this->assertNotFalse($indexStart);
        $this->assertNotFalse($indexEnd);
        $indexBody = substr($controller, $indexStart, $indexEnd - $indexStart);

        $this->assertStringContainsString('evaluateIndexRequest($_GET, $_SERVER)', $controller);
        $this->assertStringNotContainsString('$_POST', $indexBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }
}
