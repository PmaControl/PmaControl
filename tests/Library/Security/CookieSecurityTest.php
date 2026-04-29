<?php

declare(strict_types=1);

use App\Library\Security\CookieSecurity;
use PHPUnit\Framework\TestCase;

final class CookieSecurityTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv(CookieSecurity::ENV_FORCE_SECURE);
        putenv(CookieSecurity::ENV_SAMESITE);
        putenv(CookieSecurity::ENV_TRUSTED_PROXIES);
    }

    public function testHttpsRequestIsDetectedFromDirectServerState(): void
    {
        $this->assertTrue(CookieSecurity::isHttpsRequest(['HTTPS' => 'on']));
        $this->assertTrue(CookieSecurity::isHttpsRequest(['SERVER_PORT' => '443']));
        $this->assertFalse(CookieSecurity::isHttpsRequest(['HTTPS' => 'off', 'SERVER_PORT' => '80']));
    }

    public function testForwardedProtoRequiresTrustedProxy(): void
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.10',
            'HTTP_X_FORWARDED_PROTO' => 'https,http',
        ];

        $this->assertFalse(CookieSecurity::isHttpsRequest($server));
        $this->assertFalse(CookieSecurity::isHttpsRequest($server, ['10.0.0.11']));
        $this->assertTrue(CookieSecurity::isHttpsRequest($server, ['10.0.0.10']));
        $this->assertTrue(CookieSecurity::isHttpsRequest($server, ['10.0.0.0/24']));
    }

    public function testSecureModeCanBeForcedOrAutoDetected(): void
    {
        putenv(CookieSecurity::ENV_FORCE_SECURE.'=true');
        $this->assertTrue(CookieSecurity::shouldUseSecureCookies(['HTTPS' => 'off']));

        putenv(CookieSecurity::ENV_FORCE_SECURE.'=false');
        $this->assertFalse(CookieSecurity::shouldUseSecureCookies(['HTTPS' => 'on']));

        putenv(CookieSecurity::ENV_FORCE_SECURE.'=auto');
        $this->assertTrue(CookieSecurity::shouldUseSecureCookies(['HTTPS' => 'on']));
    }

    public function testSameSitePolicyDefaultsToLaxAndNormalizesValidValues(): void
    {
        $this->assertSame('Lax', CookieSecurity::sameSitePolicy());
        $this->assertSame('Strict', CookieSecurity::sameSitePolicy('strict'));
        $this->assertSame('None', CookieSecurity::sameSitePolicy('NONE'));
        $this->assertSame('Lax', CookieSecurity::sameSitePolicy('invalid'));
    }

    public function testSessionCookieParamsUseSecureWhenHttpsAndKeepExistingLifetimePathDomain(): void
    {
        $params = CookieSecurity::sessionCookieParams(
            ['HTTPS' => 'on'],
            null,
            ['lifetime' => 3600, 'path' => '/pmacontrol', 'domain' => 'pmacontrol.test']
        );

        $this->assertSame(3600, $params['lifetime']);
        $this->assertSame('/pmacontrol', $params['path']);
        $this->assertSame('pmacontrol.test', $params['domain']);
        $this->assertTrue($params['secure']);
        $this->assertTrue($params['httponly']);
        $this->assertSame('Lax', $params['samesite']);
    }

    public function testCookieOptionsIncludeDomainOnlyWhenConfigured(): void
    {
        $options = CookieSecurity::cookieOptions(['HTTPS' => 'off'], 1234, '/', '');

        $this->assertSame(1234, $options['expires']);
        $this->assertSame('/', $options['path']);
        $this->assertFalse($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('Lax', $options['samesite']);
        $this->assertArrayNotHasKey('domain', $options);

        $optionsWithDomain = CookieSecurity::cookieOptions(['HTTPS' => 'on'], 1234, '/', 'pmacontrol.test');
        $this->assertSame('pmacontrol.test', $optionsWithDomain['domain']);
        $this->assertTrue($optionsWithDomain['secure']);
    }

    public function testSameSiteNoneForcesSecureCookieOptions(): void
    {
        putenv(CookieSecurity::ENV_SAMESITE.'=None');

        $options = CookieSecurity::cookieOptions(['HTTPS' => 'off'], 1234);

        $this->assertTrue($options['secure']);
        $this->assertSame('None', $options['samesite']);
    }

    public function testSetCookieDelegatesWithModernOptionsArray(): void
    {
        $called = null;

        $result = CookieSecurity::setCookie(
            'language',
            'fr',
            1234,
            ['HTTPS' => 'on'],
            '/',
            'pmacontrol.test',
            true,
            null,
            function (string $name, string $value, array $options) use (&$called): bool {
                $called = [$name, $value, $options];

                return true;
            }
        );

        $this->assertTrue($result);
        $this->assertSame('language', $called[0]);
        $this->assertSame('fr', $called[1]);
        $this->assertSame('pmacontrol.test', $called[2]['domain']);
        $this->assertTrue($called[2]['secure']);
        $this->assertSame('Lax', $called[2]['samesite']);
    }

    public function testHardenSetCookieHeaderAddsSecurityAttributesAndPreservesCookieAttributes(): void
    {
        $header = 'Set-Cookie: auth=a=b=c; Path=/; Domain=pmacontrol.test; Expires=Wed, 29 Apr 2026 12:00:00 GMT; Max-Age=3600';

        $hardened = CookieSecurity::hardenSetCookieHeader($header, true);

        $this->assertSame(
            'Set-Cookie: auth=a=b=c; Path=/; Domain=pmacontrol.test; Expires=Wed, 29 Apr 2026 12:00:00 GMT; Max-Age=3600; Secure; HttpOnly; SameSite=Lax',
            $hardened
        );
    }

    public function testHardenSetCookieHeaderIsIdempotentAndPreservesStricterExistingPolicy(): void
    {
        $header = 'Set-Cookie: PHPSESSID=abc; path=/; Secure; HttpOnly; SameSite=Strict';

        $once = CookieSecurity::hardenSetCookieHeader($header, false);
        $twice = CookieSecurity::hardenSetCookieHeader($once, false);

        $this->assertSame('Set-Cookie: PHPSESSID=abc; path=/; Secure; HttpOnly; SameSite=Strict', $once);
        $this->assertSame($once, $twice);
    }

    public function testHardenSetCookieHeaderPreservesSameSiteNoneAndForcesSecure(): void
    {
        $header = 'Set-Cookie: external=abc; path=/; SameSite=None';

        $this->assertSame(
            'Set-Cookie: external=abc; path=/; Secure; HttpOnly; SameSite=None',
            CookieSecurity::hardenSetCookieHeader($header, false)
        );
    }

    public function testHardenSetCookieHeaderKeepsDeletionAttributes(): void
    {
        $header = 'Set-Cookie: auth=deleted; Path=/; Max-Age=-3600; Expires=Thu, 01 Jan 1970 00:00:00 GMT';

        $this->assertSame(
            'Set-Cookie: auth=deleted; Path=/; Max-Age=-3600; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Lax',
            CookieSecurity::hardenSetCookieHeader($header, false)
        );
    }

    public function testHardenPendingSetCookieHeadersReemitsOnlyHardenedCookieHeaders(): void
    {
        $removed = null;
        $emitted = [];

        CookieSecurity::hardenPendingSetCookieHeaders(
            true,
            'Lax',
            fn (): array => [
                'Content-Type: text/html',
                'Set-Cookie: PHPSESSID=abc; path=/',
                'Set-Cookie: auth=def; Path=/; Max-Age=3600',
            ],
            function (string $headerName) use (&$removed): void {
                $removed = $headerName;
            },
            function (string $headerLine, bool $replace) use (&$emitted): void {
                $emitted[] = [$headerLine, $replace];
            }
        );

        $this->assertSame('Set-Cookie', $removed);
        $this->assertSame([
            ['Set-Cookie: PHPSESSID=abc; path=/; Secure; HttpOnly; SameSite=Lax', false],
            ['Set-Cookie: auth=def; Path=/; Max-Age=3600; Secure; HttpOnly; SameSite=Lax', false],
        ], $emitted);
    }

    public function testOutgoingCookieHardenerRegistersCallableWithComputedPolicy(): void
    {
        $registered = null;

        $this->assertTrue(CookieSecurity::registerOutgoingCookieHardener(
            ['HTTPS' => 'on'],
            null,
            function (callable $callback) use (&$registered): void {
                $registered = $callback;
            }
        ));

        $this->assertIsCallable($registered);
    }
}
