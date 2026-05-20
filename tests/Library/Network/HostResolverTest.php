<?php

declare(strict_types=1);

use App\Library\Network\HostResolver;
use PHPUnit\Framework\TestCase;

final class HostResolverTest extends TestCase
{
    public function testReturnsLiteralIpv4WithoutCallingResolver(): void
    {
        $called = false;

        $ips = HostResolver::resolveIpv4Addresses(' 10.0.0.10 ', static function () use (&$called) {
            $called = true;

            return array('10.0.0.11');
        });

        $this->assertSame(array('10.0.0.10'), $ips);
        $this->assertFalse($called);
    }

    public function testFiltersResolverOutputToUniqueIpv4Addresses(): void
    {
        $ips = HostResolver::resolveIpv4Addresses('db01.internal', static function () {
            return array(
                '10.0.0.10',
                'not-an-ip',
                '2001:db8::1',
                '10.0.0.10',
                ' 10.0.0.11 ',
                array('10.0.0.12'),
            );
        });

        $this->assertSame(array('10.0.0.10', '10.0.0.11'), $ips);
    }

    public function testReturnsEmptyListForInvalidInputsOrResolutionFailure(): void
    {
        foreach (array(null, array(), '', '   ') as $host) {
            $this->assertSame(
                array(),
                HostResolver::resolveIpv4Addresses($host, static function () {
                    return array('10.0.0.10');
                })
            );
        }

        $this->assertSame(
            array(),
            HostResolver::resolveIpv4Addresses('db01.internal', static function () {
                return false;
            })
        );
        $this->assertSame(
            array(),
            HostResolver::resolveIpv4Addresses('db01.internal', static function () {
                return '10.0.0.10';
            })
        );
    }

    public function testShellMetacharactersAreOnlyResolverInput(): void
    {
        $seenHost = null;

        $ips = HostResolver::resolveIpv4Addresses("example.com; id #", static function (string $host) use (&$seenHost) {
            $seenHost = $host;

            return false;
        });

        $this->assertSame("example.com; id #", $seenHost);
        $this->assertSame(array(), $ips);
    }
}
