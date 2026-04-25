<?php

declare(strict_types=1);

use App\Controller\Client;
use PHPUnit\Framework\TestCase;

final class ClientBreadcrumbTest extends TestCase
{
    public function testSettingsBreadcrumbUsesAsciiHrefAttribute(): void
    {
        $breadcrumb = Client::buildSettingsBreadcrumb('/fr/', 'Settings');

        $this->assertStringContainsString('<a href="/fr/">', $breadcrumb);
        $this->assertStringNotContainsString('href⁼', $breadcrumb);
    }
}
