<?php

declare(strict_types=1);

use App\Controller\Client;
use PHPUnit\Framework\TestCase;

final class ClientDeleteRequestTest extends TestCase
{
    public function testClientDeleteRequiresPostMethod(): void
    {
        $this->assertTrue(Client::isDeleteRequestAllowed(['REQUEST_METHOD' => 'POST']));
        $this->assertTrue(Client::isDeleteRequestAllowed(['REQUEST_METHOD' => 'post']));
        $this->assertFalse(Client::isDeleteRequestAllowed(['REQUEST_METHOD' => 'GET']));
        $this->assertFalse(Client::isDeleteRequestAllowed([]));
    }

    public function testClientIndexUsesPostFormForDeleteAction(): void
    {
        $view = (string) file_get_contents(__DIR__.'/../../App/view/Client/index.view.php');

        $this->assertStringContainsString('<form method="post"', $view);
        $this->assertStringContainsString('client/delete/', $view);
        $this->assertStringNotContainsString('<a href="<?= LINK ?>client/delete/', $view);
    }
}
