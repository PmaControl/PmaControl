<?php

declare(strict_types=1);

use App\Library\Security\AuthToken;
use PHPUnit\Framework\TestCase;

final class AuthTokenTest extends TestCase
{
    public function testGenerateTokenReturnsDistinctSixtyFourHexTokens(): void
    {
        $first = AuthToken::generateToken();
        $second = AuthToken::generateToken();

        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $first);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $second);
        $this->assertNotSame($first, $second);
    }

    public function testHashAndVerifyToken(): void
    {
        $token = str_repeat('a', 64);
        $hash = AuthToken::hashToken($token);

        $this->assertSame(64, strlen($hash));
        $this->assertNotSame($token, $hash);
        $this->assertTrue(AuthToken::verifyToken($token, $hash, AuthToken::expiresAt(3600, 1000), 1001));
        $this->assertFalse(AuthToken::verifyToken(str_repeat('b', 64), $hash, AuthToken::expiresAt(3600, 1000), 1001));
    }

    public function testVerifyTokenRejectsExpiredOrMalformedInput(): void
    {
        $token = str_repeat('a', 64);
        $hash = AuthToken::hashToken($token);

        $this->assertFalse(AuthToken::verifyToken($token, $hash, AuthToken::expiresAt(3600, 1000), 5000));
        $this->assertFalse(AuthToken::verifyToken('not-a-token', $hash, AuthToken::expiresAt(3600, 1000), 1001));
        $this->assertFalse(AuthToken::verifyToken($token, '', AuthToken::expiresAt(3600, 1000), 1001));
        $this->assertFalse(AuthToken::verifyToken($token, $hash, null, 1001));
    }

    public function testBuildHttpsUrlUsesTrustedOrigin(): void
    {
        $url = AuthToken::buildHttpsUrl(
            ['HTTP_HOST' => 'attacker.test'],
            '/en/user/confirmation/user%40example.test/' . str_repeat('a', 64),
            'https://pmacontrol.example'
        );

        $this->assertSame(
            'https://pmacontrol.example/en/user/confirmation/user%40example.test/' . str_repeat('a', 64),
            $url
        );
    }

    public function testBuildHttpsUrlForcesHttpsWhenNoTrustedOriginIsConfigured(): void
    {
        $url = AuthToken::buildHttpsUrl(
            ['HTTP_HOST' => 'pmacontrol.test:8443'],
            'user/password_recover/user%40example.test/' . str_repeat('a', 64),
            ''
        );

        $this->assertSame(
            'https://pmacontrol.test:8443/user/password_recover/user%40example.test/' . str_repeat('a', 64),
            $url
        );
    }

    public function testBuildHttpsUrlPrefersServerNameOverHostHeaderFallback(): void
    {
        $url = AuthToken::buildHttpsUrl(
            ['SERVER_NAME' => 'pmacontrol.internal', 'HTTP_HOST' => 'attacker.test'],
            'user/password_recover/user%40example.test/' . str_repeat('a', 64),
            ''
        );

        $this->assertSame(
            'https://pmacontrol.internal/user/password_recover/user%40example.test/' . str_repeat('a', 64),
            $url
        );
    }
}
