<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserLoginHelperInventoryTest extends TestCase
{
    public function testLoginHelperIsNotExposedAsRouteLikeMethod(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($controller);
        $this->assertStringNotContainsString('function login(', $controller);
        $this->assertStringNotContainsString('$this->login(', $controller);
        $this->assertStringContainsString('private function establishSession($login, $password)', $controller);
        $this->assertStringContainsString('$this->establishSession(', $controller);
    }

}
