<?php

declare(strict_types=1);

use App\Controller\Datamodel;
use PHPUnit\Framework\TestCase;

final class DatamodelAddFilterTest extends TestCase
{
    public function testAddRequestAllowsGetAndHead(): void
    {
        $get = Datamodel::evaluateAddRequest(['REQUEST_METHOD' => 'GET']);
        $head = Datamodel::evaluateAddRequest(['REQUEST_METHOD' => 'HEAD']);

        $this->assertSame(200, $get['status']);
        $this->assertSame('', $get['body']);
        $this->assertSame([], $get['headers']);
        $this->assertSame(200, $head['status']);
        $this->assertSame('', $head['body']);
        $this->assertSame([], $head['headers']);
    }

    public function testAddRequestRejectsUnsafeMethods(): void
    {
        $post = Datamodel::evaluateAddRequest(['REQUEST_METHOD' => 'POST']);
        $put = Datamodel::evaluateAddRequest(['REQUEST_METHOD' => 'PUT']);

        $this->assertSame(405, $post['status']);
        $this->assertSame('Method Not Allowed', $post['body']);
        $this->assertSame('GET, HEAD', $post['headers']['Allow']);
        $this->assertSame(405, $put['status']);
        $this->assertSame('GET, HEAD', $put['headers']['Allow']);
    }

    public function testAddControllerAndViewDropDeadPostFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Datamodel.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Datamodel/add.view.php');
        $addStart = strpos($controller, 'public function add()');
        $addEnd = strpos($controller, 'public static function evaluateAddRequest');

        $this->assertNotFalse($addStart);
        $this->assertNotFalse($addEnd);
        $addBody = substr($controller, $addStart, $addEnd - $addStart);

        $this->assertStringContainsString('evaluateAddRequest($_SERVER)', $controller);
        $this->assertStringNotContainsString('$_POST', $addBody);
        $this->assertStringNotContainsString('sql_save', $addBody);
        $this->assertStringNotContainsString('$id_cleaner_main', $addBody);
        $this->assertStringNotContainsString('$db', $addBody);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
    }
}
