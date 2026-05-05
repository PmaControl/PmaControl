<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CommonClientEnvironmentFilterTest extends TestCase
{
    public function testClientEnvironmentFilterFormUsesGet(): void
    {
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Common/displayClientEnvironment.view.php');

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringContainsString('name="client_environment" value="1"', $view);
        $this->assertStringContainsString('Form::select("client", "libelle"', $view);
        $this->assertStringContainsString('Form::select("environment", "libelle"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    public function testClientEnvironmentControllerDoesNotConsumePost(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Common.php');
        $start = strpos($controller, 'public function displayClientEnvironment($param)');
        $next = strpos($controller, 'public function remove', $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($next);
        $methodBody = substr($controller, $start, $next - $start);

        $this->assertStringContainsString('$_SERVER[\'REQUEST_METHOD\'] == "GET"', $methodBody);
        $this->assertStringContainsString('$_GET[\'client_environment\']', $methodBody);
        $this->assertStringContainsString('$_SESSION[\'client\'][\'libelle\']', $methodBody);
        $this->assertStringContainsString('$_SESSION[\'environment\'][\'libelle\']', $methodBody);
        $this->assertStringNotContainsString('$_POST', $methodBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $methodBody);
    }
}
