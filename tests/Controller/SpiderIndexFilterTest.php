<?php

declare(strict_types=1);

use App\Controller\Spider;
use PHPUnit\Framework\TestCase;

final class SpiderIndexFilterTest extends TestCase
{
    public function testIndexRequestHasNoServerSelectionByDefault(): void
    {
        $outcome = Spider::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertNull($outcome['id_mysql_server']);
    }

    public function testIndexRequestAcceptsValidGetServerSelection(): void
    {
        $outcome = Spider::evaluateIndexRequest(
            ['mysql_server' => ['id' => '7']],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id_mysql_server']);
    }

    public function testIndexRequestRejectsPost(): void
    {
        $outcome = Spider::evaluateIndexRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id_mysql_server']);
    }

    public function testServerSelectionIgnoresHostileShapes(): void
    {
        $this->assertNull(Spider::normalizeIndexServerSelection(['mysql_server' => ['id' => '7 OR 1=1']]));
        $this->assertNull(Spider::normalizeIndexServerSelection(['mysql_server' => ['id' => '7e0']]));
        $this->assertNull(Spider::normalizeIndexServerSelection(['mysql_server' => ['id' => ' 7']]));
        $this->assertNull(Spider::normalizeIndexServerSelection(['mysql_server' => ['id' => '0']]));
        $this->assertNull(Spider::normalizeIndexServerSelection(['mysql_server' => ['id' => ['7']]]));
        $this->assertNull(Spider::normalizeIndexServerSelection(['mysql_server' => '7']));
    }

    public function testViewUsesGetAndControllerRejectsPost(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Spider.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Spider/index.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('evaluateIndexRequest($_GET, $_SERVER)', $controller);
        $this->assertStringContainsString("['Allow' => 'GET']", $controller);
        $this->assertStringContainsString('$this->view = false;', $controller);
        $this->assertStringContainsString('$this->layout_name = false;', $controller);
        $this->assertStringNotContainsString('SELECT * FROM mysql_database', $controller);

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringNotContainsString('$_POST', $view);
        $this->assertStringContainsString('$data[\'id_mysql_server\']', $view);
    }
}
