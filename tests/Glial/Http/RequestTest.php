<?php

declare(strict_types=1);

use Glial\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testMethodComparisonIsCaseInsensitive(): void
    {
        $this->assertTrue(Request::isMethod(['REQUEST_METHOD' => 'post'], 'POST'));
        $this->assertFalse(Request::isMethod(['REQUEST_METHOD' => 'GET'], 'POST'));
    }

    public function testSameSiteRequestAcceptsSameOriginHeader(): void
    {
        $this->assertTrue(Request::isSameSite([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test:8443',
            'HTTP_ORIGIN' => 'https://pmacontrol.test:8443',
        ]));
    }

    public function testSameSiteRequestFallsBackToRefererWhenOriginIsMissing(): void
    {
        $this->assertTrue(Request::isSameSite([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Worker/index',
        ]));
    }

    public function testSameSiteRequestRejectsExternalOriginEvenWithSameSiteReferer(): void
    {
        $this->assertFalse(Request::isSameSite([
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

        $this->assertFalse(Request::isSameSite($server));
        $this->assertFalse(Request::isSameSite($server + ['HTTP_ORIGIN' => 'null']));
        $this->assertFalse(Request::isSameSite($server + ['HTTP_REFERER' => 'https://attacker.test/post']));
        $this->assertFalse(Request::isSameSite($server + ['HTTP_REFERER' => '//pmacontrol.test/post']));
        $this->assertFalse(Request::isSameSite($server + ['HTTP_REFERER' => '/pmacontrol/fr/Worker/index']));
    }

    public function testSameSiteRequestRejectsWrongScheme(): void
    {
        $this->assertFalse(Request::isSameSite([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'http://pmacontrol.test',
        ]));
    }

    public function testSameSiteUrlCanUseExplicitCurrentOriginForProxyAwareControllers(): void
    {
        $server = [
            'HTTPS' => 'off',
            'HTTP_HOST' => 'internal-pma',
        ];

        $this->assertTrue(Request::isSameSite(
            $server + ['HTTP_ORIGIN' => 'https://pmacontrol.example'],
            'https://pmacontrol.example'
        ));
    }
}
