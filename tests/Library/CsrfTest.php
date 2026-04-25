<?php

declare(strict_types=1);

use App\Library\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    public function testTokenIsIssuedPerScopeAndValidatedWithHashEquals(): void
    {
        $session = [];

        $workerToken = Csrf::issueToken($session, 'worker.update');
        $otherToken = Csrf::issueToken($session, 'another.post');

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $workerToken);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $otherToken);
        $this->assertNotSame($workerToken, $otherToken);
        $this->assertSame($workerToken, Csrf::issueToken($session, 'worker.update'));
        $this->assertTrue(Csrf::validateToken(['csrf_token' => $workerToken], $session, 'worker.update'));
        $this->assertFalse(Csrf::validateToken(['csrf_token' => $otherToken], $session, 'worker.update'));
        $this->assertFalse(Csrf::validateToken([], $session, 'worker.update'));
    }

    public function testSameSiteRequestAcceptsSameOriginHeader(): void
    {
        $this->assertTrue(Csrf::isSameSiteRequest([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test:8443',
            'HTTP_ORIGIN' => 'https://pmacontrol.test:8443',
        ]));
    }

    public function testSameSiteRequestFallsBackToRefererWhenOriginIsMissing(): void
    {
        $this->assertTrue(Csrf::isSameSiteRequest([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Worker/index',
        ]));
    }

    public function testSameSiteRequestRejectsExternalOriginEvenWithSameSiteReferer(): void
    {
        $this->assertFalse(Csrf::isSameSiteRequest([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
            'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Worker/index',
        ]));
    }

    public function testSameSiteRequestRejectsExternalOrAmbiguousReferer(): void
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
        ];

        $this->assertFalse(Csrf::isSameSiteRequest($server));
        $this->assertFalse(Csrf::isSameSiteRequest($server + ['HTTP_ORIGIN' => 'null']));
        $this->assertFalse(Csrf::isSameSiteRequest($server + ['HTTP_REFERER' => 'https://attacker.test/post']));
        $this->assertFalse(Csrf::isSameSiteRequest($server + ['HTTP_REFERER' => '//pmacontrol.test/post']));
        $this->assertFalse(Csrf::isSameSiteRequest($server + ['HTTP_REFERER' => '/pmacontrol/fr/Worker/index']));
    }

    public function testSameSiteUrlCanUseExplicitCurrentOriginForProxyAwareControllers(): void
    {
        $server = [
            'HTTPS' => 'off',
            'HTTP_HOST' => 'internal-pma',
        ];

        $this->assertTrue(Csrf::isSameSiteRequest(
            $server + ['HTTP_ORIGIN' => 'https://pmacontrol.example'],
            'https://pmacontrol.example'
        ));
    }
}
