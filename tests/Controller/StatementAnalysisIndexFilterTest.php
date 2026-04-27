<?php

declare(strict_types=1);

use App\Controller\StatementAnalysis;
use PHPUnit\Framework\TestCase;

final class StatementAnalysisIndexFilterTest extends TestCase
{
    public function testIndexRequestDefaultsToServerOneOnGetWithoutSelection(): void
    {
        $outcome = StatementAnalysis::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(1, $outcome['id_mysql_server']);
    }

    public function testIndexRequestAcceptsValidGetServerSelection(): void
    {
        $outcome = StatementAnalysis::evaluateIndexRequest(
            ['mysql_server' => ['id' => '7']],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id_mysql_server']);
    }

    public function testIndexRequestRejectsPost(): void
    {
        $outcome = StatementAnalysis::evaluateIndexRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id_mysql_server']);
    }

    public function testServerSelectionIgnoresHostileShapes(): void
    {
        $this->assertSame(1, StatementAnalysis::normalizeServerSelection(['mysql_server' => ['id' => '7 OR 1=1']]));
        $this->assertSame(1, StatementAnalysis::normalizeServerSelection(['mysql_server' => ['id' => '7e0']]));
        $this->assertSame(1, StatementAnalysis::normalizeServerSelection(['mysql_server' => ['id' => ' 7']]));
        $this->assertSame(1, StatementAnalysis::normalizeServerSelection(['mysql_server' => ['id' => '0']]));
        $this->assertSame(1, StatementAnalysis::normalizeServerSelection(['mysql_server' => ['id' => ['7']]]));
        $this->assertSame(1, StatementAnalysis::normalizeServerSelection(['mysql_server' => '7']));
    }

    public function testRecentQueriesSqlUsesNormalizedInteger(): void
    {
        $sql = StatementAnalysis::buildRecentQueriesSql(7);

        $this->assertStringContainsString('id_mysql_server=7', $sql);
        $this->assertStringNotContainsString('7 OR 1=1', $sql);
        $this->assertStringContainsString('date_sub(now(), INTERVAL 1 DAY)', $sql);
    }

    public function testViewUsesGetAndControllerRejectsPost(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/StatementAnalysis.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/StatementAnalysis/index.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('evaluateIndexRequest($_GET, $_SERVER)', $controller);
        $this->assertStringContainsString("['Allow' => 'GET']", $controller);
        $this->assertStringContainsString('$this->view = false;', $controller);
        $this->assertStringContainsString('$this->layout_name = false;', $controller);

        $this->assertStringContainsString('method="GET"', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }
}
