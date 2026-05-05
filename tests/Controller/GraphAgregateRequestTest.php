<?php

declare(strict_types=1);

use App\Controller\Graph;
use PHPUnit\Framework\TestCase;

final class GraphAgregateRequestTest extends TestCase
{
    public function testAgregateRequestRejectsPost(): void
    {
        $outcome = Graph::evaluateAgregateRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('', $outcome['redirect_route']);
    }

    public function testAgregateRequestAllowsEmptyGetWithoutRedirect(): void
    {
        $outcome = Graph::evaluateAgregateRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['redirect_route']);
        $this->assertSame('', $outcome['filter']['mysql_cluster_id']);
        $this->assertSame('', $outcome['filter']['mysql_server_ids']);
    }

    public function testAgregateRequestBuildsClusterRedirectFromCsv(): void
    {
        $outcome = Graph::evaluateAgregateRequest([
            'mysql_cluster' => ['id' => '1,2,3'],
        ], [
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'mysql_cluster%5Bid%5D=1%2C2%2C3',
        ]);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('1,2,3', $outcome['filter']['mysql_cluster_id']);
        $this->assertSame('mysql_cluster:id:1,2,3', $outcome['redirect_route']);
    }

    public function testAgregateRequestBuildsServerRedirectFromGetArray(): void
    {
        $outcome = Graph::evaluateAgregateRequest([
            'mysql_server' => ['id' => ['4', '5']],
        ], [
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'mysql_server%5Bid%5D%5B%5D=4&mysql_server%5Bid%5D%5B%5D=5',
        ]);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('4,5', $outcome['filter']['mysql_server_ids']);
        $this->assertSame('mysql_server:id:4,5', $outcome['redirect_route']);
    }

    public function testAgregateIdsDropInvalidValuesAndDuplicates(): void
    {
        $this->assertSame('1,4', Graph::normalizeAgregateIds(['1', 'abc', '-3', '004', '4']));
    }

    public function testAgregateServerSelectionWinsWhenBothFiltersArePresent(): void
    {
        $outcome = Graph::evaluateAgregateRequest([
            'mysql_cluster' => ['id' => '1,2'],
            'mysql_server' => ['id' => ['9', '10']],
        ], [
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'mysql_cluster%5Bid%5D=1%2C2&mysql_server%5Bid%5D%5B%5D=9&mysql_server%5Bid%5D%5B%5D=10',
        ]);

        $this->assertSame('mysql_server:id:9,10', $outcome['redirect_route']);
    }

    public function testAgregateRouteParamsDoNotRedirectAgainWithoutQueryString(): void
    {
        $outcome = Graph::evaluateAgregateRequest([
            'mysql_server' => ['id' => '4,5'],
        ], [
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
        ]);

        $this->assertSame('4,5', $outcome['filter']['mysql_server_ids']);
        $this->assertSame('', $outcome['redirect_route']);
    }

    public function testAgregateControllerAndViewDropPostFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Graph.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Graph/agregate.view.php');
        $agregateStart = strpos($controller, 'public function agregate($param)');
        $agregateEnd = strpos($controller, 'public static function evaluateAgregateRequest');
        $this->assertNotFalse($agregateStart);
        $this->assertNotFalse($agregateEnd);
        $agregateBody = substr($controller, $agregateStart, $agregateEnd - $agregateStart);

        $this->assertStringContainsString('evaluateAgregateRequest($_GET, $_SERVER)', $agregateBody);
        $this->assertStringNotContainsString('$_POST', $agregateBody);
        $this->assertStringContainsString('method="GET"', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
    }
}
