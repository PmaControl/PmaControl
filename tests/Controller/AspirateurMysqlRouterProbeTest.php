<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurMysqlRouterProbeTest extends TestCase
{
    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);

        return $instanceMethod->invokeArgs($object, $arguments);
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function testProxyTransactionProbeIsSkippedForKnownMysqlRouterEndpoint(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'shouldRunProxyTransactionProbe',
            [1, 0, true]
        );

        $this->assertFalse($result);
    }

    public function testProxyTransactionProbeStillRunsForRegularProxyEndpoint(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'shouldRunProxyTransactionProbe',
            [1, 0, false]
        );

        $this->assertTrue($result);
    }

    public function testProxyTransactionProbeIsSkippedForVipEndpoint(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'shouldRunProxyTransactionProbe',
            [1, 1, false]
        );

        $this->assertFalse($result);
    }

    public function testMysqlRouterSignatureIsDetectedFromServerBanner(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isMysqlRouterSignature',
            ['8.0.35-router']
        );

        $this->assertTrue($result);
    }

    public function testMysqlRouterSignatureIsDetectedFromVersionComment(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isMysqlRouterSignature',
            ['', '', 'MySQL Router']
        );

        $this->assertTrue($result);
    }
}
