<?php

declare(strict_types=1);

use App\Library\Security\SafeRedirect;
use PHPUnit\Framework\TestCase;

final class SafeRedirectTest extends TestCase
{
    public function testAcceptsSameSiteReferer(): void
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_REFERER' => 'https://pmacontrol.test/serverdashboard/main/1',
        ];

        self::assertSame(
            'https://pmacontrol.test/serverdashboard/main/1',
            SafeRedirect::refererOrFallback($server, '/serverdashboard/main/1')
        );
    }

    public function testRejectsExternalReferer(): void
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_REFERER' => 'https://attacker.test/phish',
        ];

        self::assertSame('/serverdashboard/main/1', SafeRedirect::refererOrFallback($server, '/serverdashboard/main/1'));
    }

    public function testRejectsAmbiguousReferers(): void
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
        ];

        self::assertSame('/fallback', SafeRedirect::refererOrFallback($server, '/fallback'));
        self::assertSame('/fallback', SafeRedirect::refererOrFallback($server + ['HTTP_REFERER' => ''], '/fallback'));
        self::assertSame('/fallback', SafeRedirect::refererOrFallback($server + ['HTTP_REFERER' => 'null'], '/fallback'));
        self::assertSame('/fallback', SafeRedirect::refererOrFallback($server + ['HTTP_REFERER' => '//pmacontrol.test/path'], '/fallback'));
        self::assertSame('/fallback', SafeRedirect::refererOrFallback($server + ['HTTP_REFERER' => '/serverdashboard/main/1'], '/fallback'));
    }

    public function testRejectsSchemeAndPortMismatch(): void
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test:8443',
        ];

        self::assertSame(
            '/fallback',
            SafeRedirect::refererOrFallback($server + ['HTTP_REFERER' => 'http://pmacontrol.test:8443/path'], '/fallback')
        );
        self::assertSame(
            '/fallback',
            SafeRedirect::refererOrFallback($server + ['HTTP_REFERER' => 'https://pmacontrol.test/path'], '/fallback')
        );
    }

    public function testUsesTrustedOriginForReverseProxyDeployments(): void
    {
        $server = [
            'HTTPS' => 'off',
            'HTTP_HOST' => 'internal-pmacontrol',
            'HTTP_REFERER' => 'https://pmacontrol.example/MysqlServer/main/1/pmacontrol',
        ];

        self::assertSame(
            'https://pmacontrol.example/MysqlServer/main/1/pmacontrol',
            SafeRedirect::refererOrFallback($server, '/fallback', 'https://pmacontrol.example')
        );
        self::assertSame('/fallback', SafeRedirect::refererOrFallback($server, '/fallback'));
    }

    public function testRejectsEmptyFallback(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SafeRedirect::refererOrFallback([], '');
    }
}
