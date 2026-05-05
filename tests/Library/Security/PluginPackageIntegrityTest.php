<?php

namespace Tests\Library\Security;

use App\Library\Security\PluginPackageIntegrity;
use PHPUnit\Framework\TestCase;

final class PluginPackageIntegrityTest extends TestCase
{
    private $zipPath;

    protected function setUp(): void
    {
        $this->zipPath = tempnam(sys_get_temp_dir(), 'pmacontrol-plugin-zip-');
        file_put_contents($this->zipPath, 'plugin package bytes');
    }

    protected function tearDown(): void
    {
        if (is_string($this->zipPath) && is_file($this->zipPath)) {
            unlink($this->zipPath);
        }
    }

    public function testAcceptsLegacyMd5OnlyDuringTransition(): void
    {
        $result = PluginPackageIntegrity::evaluate($this->zipPath, array(
            'md5' => md5_file($this->zipPath),
        ), array());

        self::assertTrue($result['allowed']);
        self::assertSame(PluginPackageIntegrity::MODE_LEGACY_MD5_ONLY, $result['mode']);
        self::assertSame(array('Plugin ZIP uses legacy MD5-only integrity metadata'), $result['warnings']);
    }

    public function testRejectsMissingSha256WhenLegacyMd5IsNotAvailable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plugin ZIP SHA-256 checksum is required');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(), array());
    }

    public function testRejectsMd5Mismatch(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plugin ZIP MD5 checksum mismatch');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'md5' => str_repeat('0', 32),
        ), array());
    }

    public function testRejectsSha256Mismatch(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plugin ZIP SHA-256 checksum mismatch');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'sha256' => str_repeat('0', 64),
        ), array());
    }

    public function testRejectsSha256WithoutSignature(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plugin ZIP signature is required with SHA-256 metadata');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'sha256' => hash_file('sha256', $this->zipPath),
        ), array());
    }

    public function testRejectsInvalidSignature(): void
    {
        $this->skipWithoutSodium();
        $keys = $this->signingKeys();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plugin ZIP signature verification failed');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'sha256' => hash_file('sha256', $this->zipPath),
            'signature' => base64_encode(str_repeat("\0", SODIUM_CRYPTO_SIGN_BYTES)),
        ), array('test' => base64_encode($keys['public'])));
    }

    public function testAcceptsValidSignedSha256Package(): void
    {
        $this->skipWithoutSodium();
        $keys = $this->signingKeys();
        $signature = sodium_crypto_sign_detached((string) file_get_contents($this->zipPath), $keys['secret']);

        $result = PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'sha256' => hash_file('sha256', $this->zipPath),
            'signature' => base64_encode($signature),
        ), array('test' => base64_encode($keys['public'])));

        self::assertSame(PluginPackageIntegrity::MODE_SIGNED_SHA256, $result['mode']);
        self::assertSame(array(), $result['warnings']);
    }

    public function testRejectsMd5MismatchEvenWhenSha256SignatureIsValid(): void
    {
        $this->skipWithoutSodium();
        $keys = $this->signingKeys();
        $signature = sodium_crypto_sign_detached((string) file_get_contents($this->zipPath), $keys['secret']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Plugin ZIP MD5 checksum mismatch');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'md5' => str_repeat('0', 32),
            'sha256' => hash_file('sha256', $this->zipPath),
            'signature' => base64_encode($signature),
        ), array('test' => base64_encode($keys['public'])));
    }

    public function testRejectsUnknownSignatureKeyId(): void
    {
        $this->skipWithoutSodium();
        $keys = $this->signingKeys();
        $signature = sodium_crypto_sign_detached((string) file_get_contents($this->zipPath), $keys['secret']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No trusted plugin signature public key matches the requested key id');

        PluginPackageIntegrity::ensureZipTrusted($this->zipPath, array(
            'sha256' => hash_file('sha256', $this->zipPath),
            'signature' => base64_encode($signature),
            'signature_key_id' => 'missing',
        ), array('test' => base64_encode($keys['public'])));
    }

    private function skipWithoutSodium(): void
    {
        if (!extension_loaded('sodium') || !function_exists('sodium_crypto_sign_detached')) {
            self::markTestSkipped('ext-sodium is required for Ed25519 signature tests');
        }
    }

    /**
     * @return array{public:string,secret:string}
     */
    private function signingKeys(): array
    {
        $keypair = sodium_crypto_sign_keypair();

        return array(
            'public' => sodium_crypto_sign_publickey($keypair),
            'secret' => sodium_crypto_sign_secretkey($keypair),
        );
    }
}
