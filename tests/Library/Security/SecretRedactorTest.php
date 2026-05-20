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
        $message = "password=secret token=abc mysql://user:pass@example/db Authorization: Bearer clear\n"
            . "mysql -h db -u root -pMySqlSecret --password=LongSecret "
            . "MASTER_PASSWORD='repl-secret' IDENTIFIED BY 'grant-secret'";

        $redacted = SecretRedactor::text($message);

        $this->assertStringNotContainsString('secret', $redacted);
        $this->assertStringNotContainsString('abc', $redacted);
        $this->assertStringNotContainsString('pass@example', $redacted);
        $this->assertStringNotContainsString('Bearer clear', $redacted);
        $this->assertStringNotContainsString('MySqlSecret', $redacted);
        $this->assertStringNotContainsString('LongSecret', $redacted);
        $this->assertStringNotContainsString('repl-secret', $redacted);
        $this->assertStringNotContainsString('grant-secret', $redacted);
        $this->assertStringContainsString('password=***', $redacted);
        $this->assertStringContainsString('token=***', $redacted);
        $this->assertStringContainsString('mysql://user:***@example/db', $redacted);
        $this->assertStringContainsString('Authorization: ***', $redacted);
        $this->assertStringContainsString('-p******', $redacted);
        $this->assertStringContainsString('--password=******', $redacted);
        $this->assertStringContainsString("MASTER_PASSWORD='***'", $redacted);
        $this->assertStringContainsString("IDENTIFIED BY '***'", $redacted);
    }

    public function testInvalidJsonPayloadFallsBackToTextRedaction(): void
    {
        $redacted = SecretRedactor::jsonPayload('{"password":"secret", bad');

        $this->assertStringNotContainsString('secret', $redacted);
        $this->assertStringContainsString('"password":"[redacted]"', $redacted);
    }

    public function testTextRedactsSshKeyMaterial(): void
    {
        $privateKey = "-----BEGIN OPENSSH PRIVATE KEY-----\nclear-private-key\n-----END OPENSSH PRIVATE KEY-----";
        $publicKey = 'ssh-rsa ' . str_repeat('A', 80) . ' user@example.test';
        $ecdsaPublicKey = 'ecdsa-sha2-nistp256 ' . str_repeat('B', 80) . ' ecdsa@example.test';

        $redacted = SecretRedactor::text($privateKey . "\n" . $publicKey . "\n" . $ecdsaPublicKey);

        $this->assertStringNotContainsString('clear-private-key', $redacted);
        $this->assertStringNotContainsString(str_repeat('A', 80), $redacted);
        $this->assertStringNotContainsString(str_repeat('B', 80), $redacted);
        $this->assertStringContainsString('[redacted private key]', $redacted);
        $this->assertStringContainsString('[redacted public key]', $redacted);
    }

    public function testDebugValueRedactsArraysAndObjects(): void
    {
        $object = new \stdClass();
        $object->user = 'root';
        $object->passwd = 'mysql-secret';
        $opaqueObject = new class {
            private string $privateKey = 'opaque-secret';
        };

        $redactedArray = SecretRedactor::debugValue([
            'private_key' => 'ssh-secret',
            'nested' => ['token' => 'api-secret', 'display_name' => 'db1'],
        ]);
        $redactedObject = SecretRedactor::debugValue($object);
        $redactedOpaqueObject = SecretRedactor::debugValue($opaqueObject);

        $this->assertSame('[redacted]', $redactedArray['private_key']);
        $this->assertSame('[redacted]', $redactedArray['nested']['token']);
        $this->assertSame('db1', $redactedArray['nested']['display_name']);
        $this->assertSame('root', $redactedObject->user);
        $this->assertSame('[redacted]', $redactedObject->passwd);
        $this->assertIsString($redactedOpaqueObject);
        $this->assertStringNotContainsString('opaque-secret', $redactedOpaqueObject);
    }
}
