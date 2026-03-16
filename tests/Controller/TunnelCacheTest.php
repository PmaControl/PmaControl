<?php

declare(strict_types=1);

use App\Controller\Tunnel;
use PHPUnit\Framework\TestCase;

final class TunnelCacheTest extends TestCase
{
    protected function tearDown(): void
    {
        Tunnel::clearTunnelCache();
    }

    public function testGetFinalRemoteByLocalReturnsNullWhenCacheIsEmpty(): void
    {
        $this->setTunnelCache([]);

        $this->assertNull(Tunnel::getFinalRemoteByLocal(['127.0.0.1', 3306]));
    }

    public function testGetFinalRemoteByLocalReturnsRemoteEndpointFromCache(): void
    {
        $this->setTunnelCache([
            '127.0.0.1:3306' => [
                'remote_host' => '10.0.0.8',
                'remote_port' => 3306,
                'jump_hosts' => [],
            ],
        ]);

        $this->assertSame('10.0.0.8:3306', Tunnel::getFinalRemoteByLocal(['127.0.0.1', 3306]));
    }

    public function testGetFinalRemoteByLocalPrefersLastJumpHostWhenPresent(): void
    {
        $this->setTunnelCache([
            '127.0.0.1:4000' => [
                'remote_host' => '10.0.0.8',
                'remote_port' => 3306,
                'jump_hosts' => [
                    ['remote_host' => '192.168.10.10', 'remote_port' => 22],
                    ['remote_host' => '192.168.10.11', 'remote_port' => 2222],
                ],
            ],
        ]);

        $this->assertSame('192.168.10.11:2222', Tunnel::getFinalRemoteByLocal(['127.0.0.1', 4000]));
    }

    public function testGetFinalRemoteByLocalSupportsJumpHostsStoredAsIpPort(): void
    {
        $this->setTunnelCache([
            '127.0.0.1:13306' => [
                'remote_host' => '172.22.14.8',
                'remote_port' => 3306,
                'jump_hosts' => [
                    ['ip' => '172.20.10.62', 'port' => 22],
                ],
            ],
        ]);

        $this->assertSame('172.20.10.62:22', Tunnel::getFinalRemoteByLocal(['127.0.0.1', 13306]));
    }

    /**
     * @param array<string,array<string,mixed>> $cache
     */
    private function setTunnelCache(array $cache): void
    {
        $reflection = new ReflectionClass(Tunnel::class);

        $cacheProperty = $reflection->getProperty('tunnel_cache');
        $cacheProperty->setAccessible(true);
        $cacheProperty->setValue($cache);

        $expireProperty = $reflection->getProperty('tunnel_cache_expire');
        $expireProperty->setAccessible(true);
        $expireProperty->setValue(time() + 60);
    }
}
