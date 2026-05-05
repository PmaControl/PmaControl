<?php

declare(strict_types=1);

use App\Controller\Tunnel;
use PHPUnit\Framework\TestCase;

final class TunnelReachabilityTest extends TestCase
{
    public function testIsEndpointReachableReturnsTrueWhenConnectorSucceeds(): void
    {
        $connector = static fn (string $host, int $port, float $timeout): bool => $host === '127.0.0.1' && $port === 3306 && $timeout === 0.2;

        $this->assertTrue(Tunnel::isEndpointReachable(['127.0.0.1', 3306, 0.2, $connector]));
    }

    public function testIsEndpointReachableReturnsFalseWhenConnectorFails(): void
    {
        $connector = static fn (): bool => false;

        $this->assertFalse(Tunnel::isEndpointReachable(['127.0.0.1', 3306, 0.1, $connector]));
    }

    public function testIsEndpointReachableReturnsFalseForInvalidInput(): void
    {
        $this->assertFalse(Tunnel::isEndpointReachable(['', 3306]));
        $this->assertFalse(Tunnel::isEndpointReachable(['127.0.0.1', 0]));
    }
}
