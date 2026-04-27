<?php

declare(strict_types=1);

namespace App\Library\Security;

final class Identifier
{
    public const DATABASE_NAME_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';
    private const ABSOLUTE_PATH_PATTERN = '#^/[A-Za-z0-9._/-]{1,255}$#';

    public static function isDatabaseName(string $name): bool
    {
        return preg_match(self::DATABASE_NAME_PATTERN, $name) === 1;
    }

    public static function isSafeAbsolutePath(string $path): bool
    {
        return preg_match(self::ABSOLUTE_PATH_PATTERN, $path) === 1
            && strpos($path, "\0") === false
            && !preg_match('#(^|/)\.\.(/|$)#', $path);
    }
}
