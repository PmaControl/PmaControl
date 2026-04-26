<?php

declare(strict_types=1);

use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    public function testTokenIsIssuedPerScopeAndValidatedWithHashEquals(): void
    {
        $session = [];

        $workerToken = Csrf::issueToken($session, 'worker.update');
        $otherToken = Csrf::issueToken($session, 'another.post');

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $workerToken);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $otherToken);
        $this->assertNotSame($workerToken, $otherToken);
        $this->assertSame($workerToken, Csrf::issueToken($session, 'worker.update'));
        $this->assertTrue(Csrf::validateToken([Csrf::DEFAULT_FIELD => $workerToken], $session, 'worker.update'));
        $this->assertFalse(Csrf::validateToken([Csrf::DEFAULT_FIELD => $otherToken], $session, 'worker.update'));
        $this->assertFalse(Csrf::validateToken([], $session, 'worker.update'));
    }

    public function testDefaultTokenApiKeepsGlialCompatibility(): void
    {
        $token = Csrf::token();

        $this->assertSame($token, Csrf::token());
        $this->assertStringContainsString('name="_csrf_token"', Csrf::field());
        $this->assertStringContainsString($token, Csrf::field());

        $_POST[Csrf::DEFAULT_FIELD] = $token;
        $this->assertTrue(Csrf::verify());
        $this->assertNotSame($token, Csrf::token());
    }

    public function testScopedVerifyCanSkipRotationForAjaxInlineEdit(): void
    {
        $token = Csrf::token('worker.update');
        $_POST[Csrf::DEFAULT_FIELD] = $token;

        $this->assertTrue(Csrf::verify('worker.update', null, false));
        $this->assertSame($token, Csrf::token('worker.update'));
    }
}
