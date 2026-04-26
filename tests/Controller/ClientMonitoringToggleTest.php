<?php

declare(strict_types=1);

use App\Controller\Client;
use PHPUnit\Framework\TestCase;

final class ClientMonitoringToggleTest extends TestCase
{
    public function testMonitoringToggleRequiresPostMethod(): void
    {
        $this->assertTrue(Client::isPostRequest(['REQUEST_METHOD' => 'POST']));
        $this->assertTrue(Client::isPostRequest(['REQUEST_METHOD' => 'post']));
        $this->assertFalse(Client::isPostRequest(['REQUEST_METHOD' => 'GET']));
        $this->assertFalse(Client::isPostRequest([]));
    }

    public function testNormalizeMonitoringTogglePayloadUsesPostValues(): void
    {
        $payload = Client::normalizeMonitoringTogglePayload(
            ['99', 'false'],
            ['id' => '12', 'is_monitored' => 'true']
        );

        $this->assertSame(['id' => 12, 'is_monitored' => 1], $payload);
    }

    public function testNormalizeMonitoringTogglePayloadKeepsPostRouteCompatibility(): void
    {
        $payload = Client::normalizeMonitoringTogglePayload(['42', 'false'], []);

        $this->assertSame(['id' => 42, 'is_monitored' => 0], $payload);
    }

    public function testNormalizeMonitoringTogglePayloadRejectsInvalidClientId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid client id');

        Client::normalizeMonitoringTogglePayload([], ['id' => '0', 'is_monitored' => 'true']);
    }

    public function testNormalizeMonitoringTogglePayloadRejectsInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid monitoring status');

        Client::normalizeMonitoringTogglePayload([], ['id' => '12', 'is_monitored' => 'maybe']);
    }

    public function testNormalizeMonitoringTogglePayloadRejectsMissingStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid monitoring status');

        Client::normalizeMonitoringTogglePayload([], ['id' => '12']);
    }
}
