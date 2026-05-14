<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1212 — minimal pin tests for the Tools → BLACKHOLE entry
 * point: the controller class loads, exposes the public actions the
 * routing relies on, and the shared library class is wired up.
 */
final class BlackholeControllerExistsTest extends TestCase
{
    public function testControllerClassExists(): void
    {
        self::assertTrue(
            class_exists(\App\Controller\Blackhole::class),
            'App\\Controller\\Blackhole must exist to serve /Blackhole/index'
        );
    }

    public function testRelayLibraryClassExists(): void
    {
        self::assertTrue(
            class_exists(\App\Library\BlackholeRelay::class),
            'App\\Library\\BlackholeRelay is the shared core for UI + CLI'
        );
    }

    /**
     * Every action wired into the UI / CLI flow must be a public method
     * on the controller — Glial's router discovers them by name.
     */
    public function testExposedActionsArePublic(): void
    {
        $expected = [
            'index', 'status', 'runConvertCli',
            'startConvert',              // pipeline A — convert in place
            'startGreenfield', 'probeIdle', // pipeline B — provision from scratch
        ];
        foreach ($expected as $method) {
            $ref = new ReflectionMethod(\App\Controller\Blackhole::class, $method);
            self::assertTrue($ref->isPublic(), "{$method}() must be public");
        }
    }
}
