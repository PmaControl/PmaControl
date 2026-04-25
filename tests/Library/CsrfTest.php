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
}
