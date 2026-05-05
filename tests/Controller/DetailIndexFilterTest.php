<?php

declare(strict_types=1);

use App\Controller\Detail;
use PHPUnit\Framework\TestCase;

final class DetailIndexFilterTest extends TestCase
{
    public function testIndexRequestAllowsGet(): void
    {
        $outcome = Detail::evaluateIndexRequest(['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testIndexRequestRejectsPost(): void
    {
        $outcome = Detail::evaluateIndexRequest(['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
    }

    public function testIndexControllerAndViewDropPostFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Detail.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Detail/index.view.php');
        $indexStart = strpos($controller, 'public function index($param)');
        $indexEnd = strpos($controller, 'public function graph($param)');
        $this->assertNotFalse($indexStart);
        $this->assertNotFalse($indexEnd);
        $indexBody = substr($controller, $indexStart, $indexEnd - $indexStart);

        $this->assertStringContainsString('evaluateIndexRequest($_SERVER)', $controller);
        $this->assertStringNotContainsString('$_POST', $indexBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }
}
