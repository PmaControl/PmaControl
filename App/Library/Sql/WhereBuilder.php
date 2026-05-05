<?php

declare(strict_types=1);

namespace App\Library\Sql;

use App\Library\Security\Identifier;
use InvalidArgumentException;

final class WhereBuilder
{
    public static function where($db, array $conditions): string
    {
        $sql = self::composeEquals($db, $conditions);

        return $sql === '' ? '' : 'WHERE ' . $sql;
    }

    public static function andWhere($db, array $conditions): string
    {
        $sql = self::composeEquals($db, $conditions);

        return $sql === '' ? '' : ' AND ' . $sql;
    }

    public static function equals($db, string $column, $value): string
    {
        if ($value === null) {
            return self::quoteColumn($column) . ' IS NULL';
        }

        return self::quoteColumn($column) . ' = ' . self::literal($db, $value);
    }

    public static function in($db, string $column, array $values): string
    {
        if ($values === []) {
            return '0=1';
        }

        return self::quoteColumn($column)
            . ' IN (' . implode(', ', array_map(static function ($value) use ($db): string {
                return self::literal($db, $value);
            }, array_values($values))) . ')';
    }

    public static function likeContains(
        $db,
        string $column,
        string $value,
        bool $escapeWildcards = true
    ): string {
        return self::like($db, $column, '%' . self::likeValue($value, $escapeWildcards) . '%');
    }

    public static function likePrefix(
        $db,
        string $column,
        string $value,
        bool $escapeWildcards = true
    ): string {
        return self::like($db, $column, self::likeValue($value, $escapeWildcards) . '%');
    }

    private static function composeEquals($db, array $conditions): string
    {
        if ($conditions === []) {
            return '';
        }

        $parts = [];
        foreach ($conditions as $column => $value) {
            if (!is_string($column) || $column === '') {
                throw new InvalidArgumentException('SQL condition column must be a non-empty string.');
            }

            $parts[] = self::equals($db, $column, $value);
        }

        return implode(' AND ', $parts);
    }

    private static function like($db, string $column, string $pattern): string
    {
        return self::quoteColumn($column) . " LIKE '" . self::escape($db, $pattern) . "'";
    }

    private static function likeValue(string $value, bool $escapeWildcards): string
    {
        if (!$escapeWildcards) {
            return $value;
        }

        return strtr($value, [
            '\\' => '\\\\',
            '%' => '\\%',
            '_' => '\\_',
        ]);
    }

    private static function literal($db, $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new InvalidArgumentException('SQL float literal must be finite.');
            }

            return str_replace(',', '.', sprintf('%.17G', $value));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_string($value)) {
            return "'" . self::escape($db, $value) . "'";
        }

        throw new InvalidArgumentException('Unsupported SQL literal type.');
    }

    private static function escape($db, string $value): string
    {
        if (!is_object($db) || !method_exists($db, 'sql_real_escape_string')) {
            throw new InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return $db->sql_real_escape_string($value);
    }

    private static function quoteColumn(string $column): string
    {
        $parts = explode('.', $column);
        if ($parts === [] || count($parts) > 2) {
            throw new InvalidArgumentException('Invalid SQL column identifier.');
        }

        foreach ($parts as $part) {
            if ($part === '') {
                throw new InvalidArgumentException('Invalid SQL column identifier.');
            }
        }

        return implode('.', array_map(static function (string $part): string {
            return Identifier::quoteStrictSqlIdentifier($part);
        }, $parts));
    }
}
