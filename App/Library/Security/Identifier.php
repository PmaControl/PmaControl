<?php

declare(strict_types=1);

namespace App\Library\Security;

final class Identifier
{
    public const DATABASE_NAME_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';
    public const STRICT_SQL_IDENTIFIER_PATTERN = '/\A[A-Za-z_][A-Za-z0-9_]{0,63}\z/';
    public const ACCOUNT_NAME_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';
    public const HOST_PATTERN = '/^[A-Za-z0-9_.:%-]{1,255}$/';
    public const PRIVILEGE_PATTERN = '/^[A-Z][A-Z _]{0,24}$/';
    private const ABSOLUTE_PATH_PATTERN = '#^/[A-Za-z0-9._/-]{1,255}$#';

    public static function isDatabaseName(string $name): bool
    {
        return preg_match(self::DATABASE_NAME_PATTERN, $name) === 1;
    }

    public static function isSqlIdentifier(string $identifier): bool
    {
        return self::isDatabaseName($identifier);
    }

    public static function quoteSqlIdentifier(string $identifier): string
    {
        if (!self::isSqlIdentifier($identifier)) {
            throw new \InvalidArgumentException('Invalid SQL identifier');
        }

        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public static function isStrictSqlIdentifier(string $identifier): bool
    {
        return preg_match(self::STRICT_SQL_IDENTIFIER_PATTERN, $identifier) === 1;
    }

    public static function quoteStrictSqlIdentifier(string $identifier): string
    {
        if (!self::isStrictSqlIdentifier($identifier)) {
            throw new \InvalidArgumentException('Unsafe SQL identifier: ' . $identifier);
        }

        return '`' . $identifier . '`';
    }

    public static function quoteStrictSqlIdentifierOrFallback(string $identifier, string $fallback): string
    {
        return self::isStrictSqlIdentifier($identifier)
            ? self::quoteStrictSqlIdentifier($identifier)
            : self::quoteStrictSqlIdentifier($fallback);
    }

    public static function normalizeDatabaseNameList(string $names, int $maxItems = 64): ?array
    {
        $items = [];
        foreach (explode(',', $names) as $name) {
            $database = trim($name);
            if ($database === '') {
                continue;
            }
            if (!self::isDatabaseName($database)) {
                return null;
            }

            $items[] = $database;
        }

        if ($items === [] || count($items) > $maxItems) {
            return null;
        }

        $uniqueItems = array_values(array_unique($items));
        if (count($uniqueItems) !== count($items)) {
            return null;
        }

        return $uniqueItems;
    }

    public static function isAccountName(string $account): bool
    {
        return $account === '' || preg_match(self::ACCOUNT_NAME_PATTERN, $account) === 1;
    }

    public static function isHostName(string $host): bool
    {
        return preg_match(self::HOST_PATTERN, $host) === 1
            && strpos($host, "\0") === false;
    }

    public static function isPrivilegeName(string $privilege): bool
    {
        return preg_match(self::PRIVILEGE_PATTERN, $privilege) === 1;
    }

    public static function isSafeAbsolutePath(string $path): bool
    {
        return preg_match(self::ABSOLUTE_PATH_PATTERN, $path) === 1
            && strpos($path, "\0") === false
            && !preg_match('#(^|/)\.\.(/|$)#', $path);
    }
}
