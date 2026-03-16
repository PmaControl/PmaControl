<?php

declare(strict_types=1);

use App\Controller\MaxScale;
use PHPUnit\Framework\TestCase;

final class MaxScaleMatchingTest extends TestCase
{
    public function testMatchMysqlServerIdsForEndpointSupportsDirectAliasAndTunnelCandidates(): void
    {
        $inventory = MaxScale::buildMysqlServerEndpointInventory(
            [
                ['id' => 223, 'ip' => '127.0.0.1', 'port' => 10445],
                ['id' => 224, 'ip' => '127.0.0.1', 'port' => 10446],
            ],
            [
                ['id_mysql_server' => 223, 'dns' => '192.168.100.101', 'port' => 4406],
                ['id_mysql_server' => 224, 'dns' => '192.168.100.102', 'port' => 4406],
            ],
            [
                ['id_mysql_server' => 223, 'remote_host' => '192.168.100.101', 'remote_port' => 4406],
            ]
        );

        $matches = MaxScale::matchMysqlServerIdsForEndpoint('192.168.100.101', 4406, $inventory);

        $this->assertCount(1, $matches);
        $this->assertSame(223, $matches[0]['id_mysql_server']);
        $this->assertSame(['alias_dns', 'ssh_tunnel.remote'], $matches[0]['sources']);
    }

    public function testMatchMysqlServerIdsForEndpointDoesNotConfuseFrontendAndBackendPorts(): void
    {
        $inventory = MaxScale::buildMysqlServerEndpointInventory(
            [
                ['id' => 223, 'ip' => '127.0.0.1', 'port' => 10445],
            ],
            [],
            []
        );

        $this->assertSame([], MaxScale::matchMysqlServerIdsForEndpoint('127.0.0.1', 4406, $inventory));

        $matches = MaxScale::matchMysqlServerIdsForEndpoint('127.0.0.1', 10445, $inventory);
        $this->assertCount(1, $matches);
        $this->assertSame(223, $matches[0]['id_mysql_server']);
        $this->assertSame(['mysql_server'], $matches[0]['sources']);
    }

    public function testNormalizeEndpointHostMapsLocalhostToLoopback(): void
    {
        $this->assertSame('127.0.0.1', MaxScale::normalizeEndpointHost('localhost'));
        $this->assertSame('127.0.0.1', MaxScale::normalizeEndpointHost('127.0.0.1'));
        $this->assertSame('::1', MaxScale::normalizeEndpointHost('[::1]'));
    }

    public function testResolveMysqlServerMatchesForWildcardListenerUsesScopedPortCandidates(): void
    {
        $inventory = MaxScale::buildMysqlServerEndpointInventory(
            [
                ['id' => 145, 'ip' => '10.100.31.59', 'port' => 4006],
                ['id' => 177, 'ip' => '10.68.68.134', 'port' => 6447],
                ['id' => 178, 'ip' => '10.68.68.135', 'port' => 6447],
                ['id' => 179, 'ip' => '10.68.68.134', 'port' => 6450],
            ],
            [],
            []
        );

        $resolution = MaxScale::resolveMysqlServerMatchesForListener('::', 6450, $inventory, [145, 177, 179]);

        $this->assertSame('listener endpoint is wildcard; matched scoped candidates on port 6450', $resolution['reason']);
        $this->assertCount(1, $resolution['matches']);
        $this->assertSame(179, $resolution['matches'][0]['id_mysql_server']);
        $this->assertContains('maxscale_server__mysql_server.scope', $resolution['matches'][0]['sources']);
    }

    public function testResolveMysqlServerMatchesForWildcardListenerDoesNotGuessWithoutScope(): void
    {
        $inventory = MaxScale::buildMysqlServerEndpointInventory(
            [
                ['id' => 177, 'ip' => '10.68.68.134', 'port' => 6447],
                ['id' => 178, 'ip' => '10.68.68.135', 'port' => 6447],
            ],
            [],
            []
        );

        $resolution = MaxScale::resolveMysqlServerMatchesForListener('0.0.0.0', 6447, $inventory, []);

        $this->assertSame([], $resolution['matches']);
        $this->assertSame(
            'listener endpoint is wildcard and no maxscale_server__mysql_server scope exists for port 6447',
            $resolution['reason']
        );
    }
}
