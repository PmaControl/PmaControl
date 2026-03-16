<?php

declare(strict_types=1);

use App\Controller\Api;
use PHPUnit\Framework\TestCase;

if (!defined('CRYPT_KEY')) {
    define('CRYPT_KEY', 'pmacontrol-test-key');
}

final class ApiTest extends TestCase
{
    public function testResourceMapContainsExpectedEntries(): void
    {
        $map = Api::getResourceMap();

        $this->assertArrayHasKey('tags', $map);
        $this->assertArrayHasKey('clients', $map);
        $this->assertArrayHasKey('servers', $map);
        $this->assertArrayHasKey('ssh-keys', $map);
    }

    public function testNormalizeTagPayloadAppliesDefaultsAndKeepsAllowedFields(): void
    {
        $payload = Api::normalizePayload('tags', [
            'name' => 'critical',
            'background' => '#000000',
            'ignored' => 'value',
        ]);

        $this->assertSame('critical', $payload['name']);
        $this->assertSame('#000000', $payload['background']);
        $this->assertSame('#ffffff', $payload['color']);
        $this->assertArrayNotHasKey('ignored', $payload);
    }

    public function testNormalizeClientPayloadCastsBooleans(): void
    {
        $payload = Api::normalizePayload('clients', [
            'libelle' => 'ACME',
            'is_monitored' => 'false',
        ]);

        $this->assertSame('ACME', $payload['libelle']);
        $this->assertSame(0, $payload['is_monitored']);
        $this->assertArrayHasKey('date', $payload);
    }

    public function testNormalizeServerPayloadEncryptsPasswordAndCastsFlags(): void
    {
        $payload = Api::normalizePayload('servers', [
            'id_client' => '1',
            'id_environment' => '2',
            'name' => 'srv-01',
            'display_name' => 'srv-01',
            'ip' => '10.0.0.1',
            'login' => 'root',
            'passwd' => 'secret',
            'database' => 'mysql',
            'port' => '3307',
            'is_monitored' => true,
        ]);

        $this->assertSame(1, $payload['id_client']);
        $this->assertSame(2, $payload['id_environment']);
        $this->assertSame(3307, $payload['port']);
        $this->assertSame(1, $payload['is_monitored']);
        $this->assertNotSame('secret', $payload['passwd']);
    }

    public function testNormalizeSshKeyPayloadEncryptsKeysAndCastsBit(): void
    {
        $payload = Api::normalizePayload('ssh-keys', [
            'name' => 'VPG key',
            'added_on' => '2026-03-13 12:00:00',
            'fingerprint' => 'ABCD',
            'user' => 'vpg',
            'public_key' => 'ssh-ed25519 AAAA',
            'private_key' => '-----BEGIN OPENSSH PRIVATE KEY-----',
            'type' => 'ED25519',
            'bit' => '256',
            'comment' => 'PmaControl',
        ]);

        $this->assertSame('VPG key', $payload['name']);
        $this->assertSame(256, $payload['bit']);
        $this->assertNotSame('ssh-ed25519 AAAA', $payload['public_key']);
        $this->assertNotSame('-----BEGIN OPENSSH PRIVATE KEY-----', $payload['private_key']);
    }

    public function testNormalizePayloadRejectsMissingRequiredFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field');

        Api::normalizePayload('environments', ['libelle' => 'prod']);
    }

    public function testNormalizeBooleanSupportsCommonVariants(): void
    {
        $this->assertSame(1, Api::normalizeBoolean(true));
        $this->assertSame(1, Api::normalizeBoolean('yes'));
        $this->assertSame(0, Api::normalizeBoolean('false'));
        $this->assertSame(0, Api::normalizeBoolean(0));
    }

    public function testOpenApiDocumentExposesConfigurationPaths(): void
    {
        $document = Api::getOpenApiDocument();

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertArrayHasKey('/fr/api/config/tags', $document['paths']);
        $this->assertArrayHasKey('/fr/api/config/servers/{id}', $document['paths']);
        $this->assertArrayHasKey('/fr/api/config/ssh-keys', $document['paths']);
    }

    public function testReadRawRequestBodyFallsBackToStdinInCli(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, '{"name":"stdin"}');
        rewind($stream);

        $body = Api::readRawRequestBodyFromStreams('', $stream, true);

        fclose($stream);

        $this->assertSame('{"name":"stdin"}', $body);
    }
}
