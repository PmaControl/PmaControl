<?php

declare(strict_types=1);

use App\Library\Security\SecretRedactor;
use PHPUnit\Framework\TestCase;

final class SecretRedactorTest extends TestCase
{
    public function testJsonPayloadRedactsNestedSecrets(): void
    {
        $payload = json_encode([
            'mysql' => [[
                'fqdn' => 'db1.example.test',
                'login' => 'pmacontrol',
                'password' => 'mysql-secret',
                'stats' => [
                    'login' => 'haproxy',
                    'password' => 'stats-secret',
                ],
                'tag' => ['prod', 'mysql'],
            ]],
            'api_token' => 'token-secret',
        ]);

        $this->assertIsString($payload);

        $redacted = SecretRedactor::jsonPayload($payload);

        $this->assertStringNotContainsString('mysql-secret', $redacted);
        $this->assertStringNotContainsString('stats-secret', $redacted);
        $this->assertStringNotContainsString('token-secret', $redacted);
        $this->assertStringContainsString('"password":"[redacted]"', $redacted);
        $this->assertStringContainsString('"api_token":"[redacted]"', $redacted);
        $this->assertStringContainsString('"fqdn":"db1.example.test"', $redacted);
        $this->assertStringContainsString('"login":"pmacontrol"', $redacted);
    }

    public function testTextRedactsCommonSecretPatterns(): void
    {
        $message = 'password=secret token=abc mysql://user:pass@example/db Authorization: Bearer clear';

        $redacted = SecretRedactor::text($message);

        $this->assertStringNotContainsString('secret', $redacted);
        $this->assertStringNotContainsString('abc', $redacted);
        $this->assertStringNotContainsString('pass@example', $redacted);
        $this->assertStringNotContainsString('Bearer clear', $redacted);
        $this->assertStringContainsString('password=***', $redacted);
        $this->assertStringContainsString('token=***', $redacted);
        $this->assertStringContainsString('mysql://user:***@example/db', $redacted);
        $this->assertStringContainsString('Authorization: ***', $redacted);
    }

    public function testInvalidJsonPayloadFallsBackToTextRedaction(): void
    {
        $redacted = SecretRedactor::jsonPayload('{"password":"secret", bad');

        $this->assertStringNotContainsString('secret', $redacted);
        $this->assertStringContainsString('"password":"[redacted]"', $redacted);
    }
}
