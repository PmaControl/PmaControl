<?php

declare(strict_types=1);

namespace App\Library\Filesystem;

use App\Library\Security\Identifier;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SafeDirectory
{
    private const SAFE_NAME_PATTERN = '/^[A-Za-z0-9._-]{1,128}$/';

    public static function normalizeBaseDirectory($path): ?string
    {
        if (!is_scalar($path)) {
            return null;
        }

        $path = rtrim(trim((string) $path), '/');
        if ($path === '' || !Identifier::isSafeAbsolutePath($path)) {
            return null;
        }

        $realPath = realpath($path);
        if ($realPath === false || !is_dir($realPath) || !is_writable($realPath)) {
            return null;
        }

        $realPath = rtrim($realPath, '/');
        if ($realPath === '' || !Identifier::isSafeAbsolutePath($realPath)) {
            return null;
        }

        return $realPath;
    }

    public static function buildTemporaryChildPath(
        $basePath,
        string $prefix = 'pmacontrol-',
        ?callable $idFactory = null
    ): ?string {
        $baseDirectory = self::normalizeBaseDirectory($basePath);
        if ($baseDirectory === null || !preg_match(self::SAFE_NAME_PATTERN, $prefix)) {
            return null;
        }

        $idFactory = $idFactory ?? static function (): string {
            return uniqid('', true);
        };

        $identifier = (string) $idFactory();
        if (!preg_match(self::SAFE_NAME_PATTERN, $identifier)) {
            return null;
        }

        return $baseDirectory.'/'.$prefix.$identifier;
    }

    public static function removeTree(string $path): void
    {
        $path = rtrim($path, '/');
        if ($path === '' || !Identifier::isSafeAbsolutePath($path)) {
            throw new \InvalidArgumentException('Invalid directory path');
        }

        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            unlink($path);
            return;
        }

        $realPath = realpath($path);
        if ($realPath === false) {
            return;
        }

        $realPath = rtrim($realPath, '/');
        if ($realPath === '' || !Identifier::isSafeAbsolutePath($realPath)) {
            throw new \InvalidArgumentException('Invalid directory path');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $entry) {
            $entryPath = $entry->getPathname();
            if ($entry->isLink() || $entry->isFile()) {
                unlink($entryPath);
                continue;
            }

            if ($entry->isDir()) {
                rmdir($entryPath);
            }
        }

        rmdir($realPath);
    }
}
