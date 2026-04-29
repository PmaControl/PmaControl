<?php

declare(strict_types=1);

use App\Library\Filesystem\SafeDirectory;
use PHPUnit\Framework\TestCase;

final class SafeDirectoryTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir().'/pmacontrol-safe-directory-test-'.uniqid('', true);
        mkdir($this->baseDir, 0700, true);
    }

    protected function tearDown(): void
    {
        $this->removePath($this->baseDir);
    }

    public function testBuildTemporaryChildPathRequiresSafeWritableBase(): void
    {
        $child = SafeDirectory::buildTemporaryChildPath(
            $this->baseDir,
            'pmacontrol-refresh-',
            static function (): string {
                return 'abc123';
            }
        );
        $realBaseDir = realpath($this->baseDir) ?: $this->baseDir;

        $this->assertSame($realBaseDir.'/pmacontrol-refresh-abc123', $child);
        $this->assertSame($realBaseDir, SafeDirectory::normalizeBaseDirectory($this->baseDir));
        $this->assertNull(SafeDirectory::buildTemporaryChildPath($this->baseDir.';id', 'pmacontrol-refresh-'));
        $this->assertNull(SafeDirectory::buildTemporaryChildPath($this->baseDir.'/../backup', 'pmacontrol-refresh-'));
        $this->assertNull(SafeDirectory::buildTemporaryChildPath($this->baseDir.'-missing', 'pmacontrol-refresh-'));
        $this->assertNull(SafeDirectory::buildTemporaryChildPath($this->baseDir, '../bad-'));
        $this->assertNull(SafeDirectory::buildTemporaryChildPath(
            $this->baseDir,
            'pmacontrol-refresh-',
            static function (): string {
                return '../bad';
            }
        ));
    }

    public function testRemoveTreeDeletesNestedFilesWithoutFollowingSymlinks(): void
    {
        $tree = $this->baseDir.'/pmacontrol-refresh-delete';
        $target = $this->baseDir.'/outside-target.txt';
        mkdir($tree.'/nested', 0700, true);
        file_put_contents($tree.'/metadata', 'ok');
        file_put_contents($tree.'/nested/dump.sql', 'ok');
        file_put_contents($target, 'keep');

        if (function_exists('symlink')) {
            @symlink($target, $tree.'/nested/link-to-target');
        }

        SafeDirectory::removeTree($tree);

        $this->assertDirectoryDoesNotExist($tree);
        $this->assertFileExists($target);
        $this->assertSame('keep', file_get_contents($target));
    }

    public function testRemoveTreeDoesNotFollowDirectorySymlinks(): void
    {
        if (!function_exists('symlink')) {
            $this->markTestSkipped('symlink() is unavailable');
        }

        $tree = $this->baseDir.'/pmacontrol-refresh-dir-link';
        $targetDir = $this->baseDir.'/outside-dir';
        mkdir($tree, 0700, true);
        mkdir($targetDir, 0700, true);
        file_put_contents($targetDir.'/keep.txt', 'keep');

        if (@symlink($targetDir, $tree.'/link-to-dir') === false) {
            $this->markTestSkipped('Unable to create directory symlink');
        }

        SafeDirectory::removeTree($tree);

        $this->assertDirectoryDoesNotExist($tree);
        $this->assertDirectoryExists($targetDir);
        $this->assertFileExists($targetDir.'/keep.txt');
        $this->assertSame('keep', file_get_contents($targetDir.'/keep.txt'));
    }

    public function testRemoveTreeRejectsUnsafePath(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SafeDirectory::removeTree($this->baseDir.';id');
    }

    private function removePath(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            @unlink($path);
            return;
        }

        foreach (array_diff(scandir($path) ?: array(), array('.', '..')) as $entry) {
            $this->removePath($path.'/'.$entry);
        }

        @rmdir($path);
    }
}
