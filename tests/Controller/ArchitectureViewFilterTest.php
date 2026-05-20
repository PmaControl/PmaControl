<?php

declare(strict_types=1);

use App\Controller\Architecture;
use PHPUnit\Framework\TestCase;

final class ArchitectureViewFilterTest extends TestCase
{
    public function testViewAcceptsGetAndHead(): void
    {
        foreach (['GET', 'HEAD'] as $method) {
            $outcome = Architecture::evaluateViewRequest(['REQUEST_METHOD' => $method]);

            $this->assertSame(200, $outcome['status']);
            $this->assertSame('', $outcome['body']);
            $this->assertSame([], $outcome['headers']);
        }
    }

    public function testViewRejectsResidualPostAndUnsafeMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = Architecture::evaluateViewRequest(['REQUEST_METHOD' => $method]);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
        }
    }

    public function testViewControllerAndTemplateDoNotExposePostImportSurface(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Architecture.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Architecture/view.view.php');

        $viewStart = strpos($controller, 'public function view($param)');
        $controllerEnd = strrpos($controller, '}');
        $viewBody = substr($controller, $viewStart, $controllerEnd - $viewStart);

        $this->assertStringContainsString('self::evaluateViewRequest($_SERVER)', $viewBody);
        $this->assertStringNotContainsString('$_POST', $viewBody);
        $this->assertStringNotContainsString('$_FILES', $viewBody);
        $this->assertStringNotContainsString('Json::isJson', $viewBody);
        $this->assertStringNotContainsString('use \\App\\Library\\Json;', $controller);

        $this->assertStringNotContainsString('<form', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
        $this->assertStringNotContainsString('multipart/form-data', $view);
        $this->assertStringContainsString('/dot3/download/', $view);
    }
}
