<?php

declare(strict_types=1);

use App\Library\Security\SignedJsonCache;
use PHPUnit\Framework\TestCase;

final class SignedJsonCacheTest extends TestCase
{
    private const KEY = 'unit-test-cache-key';
    private const SCOPE = 'unit.scope';
    private const VERSION = 1;

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().'/pmacontrol-signed-json-cache-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testMissingFileReturnsNull(): void
    {
        $this->assertNull(SignedJsonCache::read($this->cachePath(), self::SCOPE, self::VERSION, self::KEY));
    }

    public function testValidCacheRoundTrip(): void
    {
        $payload = [
            'data' => [
                ['host' => 'db01', 'port' => 3306],
            ],
        ];

        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, $payload, self::KEY);

        $this->assertSame($payload, SignedJsonCache::read($this->cachePath(), self::SCOPE, self::VERSION, self::KEY));
    }

    public function testTamperedPayloadIsRejected(): void
    {
        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['value' => 'clean'], self::KEY);

        $envelope = $this->readEnvelope();
        $envelope['payload']['value'] = 'tampered';
        file_put_contents($this->cachePath(), json_encode($envelope, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->assertNull(SignedJsonCache::read($this->cachePath(), self::SCOPE, self::VERSION, self::KEY));
    }

    public function testDifferentScopeIsRejected(): void
    {
        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['value' => 'clean'], self::KEY);

        $this->assertNull(SignedJsonCache::read($this->cachePath(), 'other.scope', self::VERSION, self::KEY));
    }

    public function testDifferentVersionIsRejected(): void
    {
        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['value' => 'clean'], self::KEY);

        $this->assertNull(SignedJsonCache::read($this->cachePath(), self::SCOPE, 2, self::KEY));
    }

    public function testInvalidJsonIsRejected(): void
    {
        $this->ensureParentDirectory();
        file_put_contents($this->cachePath(), '{invalid');

        $this->assertNull(SignedJsonCache::read($this->cachePath(), self::SCOPE, self::VERSION, self::KEY));
    }

    public function testNonArrayPayloadIsRejected(): void
    {
        $this->ensureParentDirectory();
        $payloadJson = json_encode('not-array', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $envelope = [
            'v' => self::VERSION,
            'scope' => self::SCOPE,
            'payload' => 'not-array',
            'mac' => 'sha256:'.hash_hmac('sha256', self::VERSION.'|'.self::SCOPE.'|'.$payloadJson, self::KEY),
        ];
        file_put_contents($this->cachePath(), json_encode($envelope, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->assertNull(SignedJsonCache::read($this->cachePath(), self::SCOPE, self::VERSION, self::KEY));
    }

    public function testWriteCreatesPrivateFileAndDirectory(): void
    {
        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['value' => 'clean'], self::KEY);

        $this->assertFileExists($this->cachePath());
        $this->assertSame(0600, fileperms($this->cachePath()) & 0777);
        $this->assertSame(0700, fileperms(dirname($this->cachePath())) & 0777);
        $this->assertSame([], glob($this->cachePath().'.tmp.*'));
    }

    public function testMissingKeyFailsClosed(): void
    {
        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['value' => 'clean'], self::KEY);

        $this->assertNull(SignedJsonCache::read($this->cachePath(), self::SCOPE, self::VERSION, ''));

        $this->expectException(RuntimeException::class);
        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['value' => 'clean'], '');
    }

    public function testObjectPayloadIsRejectedBeforeWrite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SignedJsonCache::write($this->cachePath(), self::SCOPE, self::VERSION, ['object' => new stdClass()], self::KEY);
    }

    private function cachePath(): string
    {
        return $this->root.'/nested/cache.json';
    }

    private function ensureParentDirectory(): void
    {
        if (!is_dir(dirname($this->cachePath()))) {
            mkdir(dirname($this->cachePath()), 0700, true);
        }
    }

    private function readEnvelope(): array
    {
        return json_decode((string) file_get_contents($this->cachePath()), true, 512, JSON_THROW_ON_ERROR);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($path);
    }
}
