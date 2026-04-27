<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserBlockNewsletterCsrfInventoryTest extends TestCase
{
    public function testBlockNewsletterLegacyPostSurfaceIsRemoved(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');

        $this->assertIsString($controller);
        $this->assertStringNotContainsString('function block_newsletter(', $controller);
        $this->assertStringNotContainsString('$_POST[\'newsletter\']', $controller);
        $this->assertStringNotContainsString('UserNewsLetter', $controller);
    }
}
