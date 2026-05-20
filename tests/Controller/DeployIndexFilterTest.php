<?php

declare(strict_types=1);

use App\Controller\Deploy;
use PHPUnit\Framework\TestCase;

final class DeployIndexFilterTest extends TestCase
{
    public function testIndexRequestAllowsGet(): void
    {
        $outcome = Deploy::evaluateIndexRequest(['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testIndexRequestAllowsHead(): void
    {
        $outcome = Deploy::evaluateIndexRequest(['REQUEST_METHOD' => 'HEAD']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testIndexRequestRejectsUnsafeMethods(): void
    {
        $outcome = Deploy::evaluateIndexRequest(['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);

        $putOutcome = Deploy::evaluateIndexRequest(['REQUEST_METHOD' => 'PUT']);
        $this->assertSame(405, $putOutcome['status']);
        $this->assertSame('GET, HEAD', $putOutcome['headers']['Allow']);
    }

    public function testIndexControllerAndViewDropPostFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Deploy.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Deploy/index.view.php');
        $indexStart = strpos($controller, 'public function index()');
        $indexEnd = strpos($controller, 'public function execute($param)');

        $this->assertNotFalse($indexStart);
        $this->assertNotFalse($indexEnd);
        $indexBody = substr($controller, $indexStart, $indexEnd - $indexStart);

        $this->assertStringContainsString('evaluateIndexRequest($_SERVER)', $controller);
        $this->assertStringNotContainsString('$_POST', $indexBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringContainsString('unset($_GET[\'galera\'], $_GET[\'general\']);', $view);
        $this->assertStringContainsString('preg_match(\'/^\\d{1,5}$/\'', $view);
        $this->assertStringContainsString('"disabled" => "disabled"', $view);
        $this->assertStringContainsString('<button type="button" class="btn btn-primary">Deploy</button>', $view);
        $this->assertStringContainsString('$_GET[\'ssh\'] = array(\'port\' => $deploySshPort);', $view);
        $this->assertStringNotContainsString('$_GET[\'ssh\'][\'password\']', $view);
    }
}
