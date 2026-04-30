<?php

declare(strict_types=1);

use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv(CsrfGuard::TRUSTED_ORIGIN);
    }

    public function testTrustedOriginCanComeFromEnvironment(): void
    {
        putenv(CsrfGuard::TRUSTED_ORIGIN . '=https://pmacontrol.example/');

        $this->assertSame('https://pmacontrol.example', CsrfGuard::trustedOrigin());
    }

    public function testSameSiteUsesConfiguredOriginInsteadOfForgedHost(): void
    {
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'off',
            'HTTP_HOST' => 'attacker.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.example',
        ];

        $this->assertTrue(CsrfGuard::isSameSite($server, 'https://pmacontrol.example'));
        $this->assertFalse(CsrfGuard::isSameSite($server));
    }

    public function testSameSiteRejectsMissingOriginAndReferer(): void
    {
        $this->assertFalse(CsrfGuard::isSameSite([
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
        ]));
    }

    public function testCheckStopsOnMethodBeforeOriginOrToken(): void
    {
        $outcome = CsrfGuard::check([], ['REQUEST_METHOD' => 'GET'], [], 'worker.update');

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testCheckStopsOnOriginBeforeToken(): void
    {
        $outcome = CsrfGuard::check(
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            [],
            'worker.update'
        );

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testCheckAcceptsValidPostOriginAndToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'worker.update');

        $outcome = CsrfGuard::check(
            [Csrf::DEFAULT_FIELD => $token],
            [
                'REQUEST_METHOD' => 'post',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Worker/index',
            ],
            $session,
            'worker.update'
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
    }

    public function testEnsureOrFailReturnsNullOnValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'worker.update');

        $failure = CsrfGuard::ensureOrFail(
            [Csrf::DEFAULT_FIELD => $token],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Worker/index',
            ],
            $session,
            'worker.update'
        );

        $this->assertNull($failure);
    }

    public function testEnsureOrFailReturnsFailurePayloadOnGet(): void
    {
        $failure = CsrfGuard::ensureOrFail([], ['REQUEST_METHOD' => 'GET'], [], 'worker.update');

        $this->assertSame([
            'status' => 405,
            'body' => 'Method Not Allowed',
            'headers' => ['Allow' => 'POST'],
        ], $failure);
        $this->assertArrayNotHasKey('allowed', $failure);
    }

    public function testEnsureOrFailReturnsFailurePayloadOnInvalidOrigin(): void
    {
        $failure = CsrfGuard::ensureOrFail(
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            [],
            'worker.update'
        );

        $this->assertSame([
            'status' => 403,
            'body' => 'Invalid request origin',
            'headers' => [],
        ], $failure);
    }

    public function testEnsureOrFailReturnsFailurePayloadOnInvalidToken(): void
    {
        $failure = CsrfGuard::ensureOrFail(
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Worker/index',
            ],
            [],
            'worker.update'
        );

        $this->assertSame([
            'status' => 403,
            'body' => 'Invalid CSRF token',
            'headers' => [],
        ], $failure);
    }

    public function testEnsureOrFailRespectsTrustedOriginOverride(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'worker.update');

        $failure = CsrfGuard::ensureOrFail(
            [Csrf::DEFAULT_FIELD => $token],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'off',
                'HTTP_HOST' => 'attacker.test',
                'HTTP_ORIGIN' => 'https://pmacontrol.example',
            ],
            $session,
            'worker.update',
            'https://pmacontrol.example/'
        );

        $this->assertNull($failure);
    }
}
