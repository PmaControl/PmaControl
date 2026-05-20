<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationMetadataReader
{
    /**
     * @param object $db Glial/Mysqli adapter.
     * @param list<string> $tables Fully-qualified table names.
     * @return array<string,array{rows:list<array<string,mixed>>,tooltips:array<string,string>,available:bool,reason?:string}>
     */
    public static function read(object $db, array $tables): array
    {
        $payload = [];

        foreach ($tables as $table) {
            if (!self::tableExists($db, $table)) {
                $payload[$table] = [
                    'rows' => [],
                    'tooltips' => [],
                    'available' => false,
                    'reason' => 'table_not_available',
                ];
                continue;
            }

            $rows = self::fetchRows($db, $table);
            $rows = ReplicationMetadataDictionary::maskSensitiveRows($rows);

            $payload[$table] = [
                'rows' => $rows,
                'tooltips' => ReplicationMetadataDictionary::tooltipMapForRows($rows),
                'available' => true,
            ];
        }

        return $payload;
    }

    public static function tableExists(object $db, string $qualifiedTable): bool
    {
        [$schema, $table] = self::splitQualifiedTable($qualifiedTable);

        $res = $db->sql_query_silent(
            'SHOW TABLES FROM ' . self::quoteIdentifier($schema)
            . " LIKE '" . $db->sql_real_escape_string($table) . "'"
        );
        if (!$res) {
            return false;
        }

        return (bool) $db->sql_fetch_array($res, MYSQLI_ASSOC);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function fetchRows(object $db, string $qualifiedTable): array
    {
        [$schema, $table] = self::splitQualifiedTable($qualifiedTable);
        $res = $db->sql_query_silent(
            'SELECT * FROM ' . self::quoteIdentifier($schema) . '.' . self::quoteIdentifier($table) . ' LIMIT 200'
        );
        if (!$res) {
            return [];
        }

        $rows = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array{0:string,1:string}
     */
    private static function splitQualifiedTable(string $qualifiedTable): array
    {
        $parts = explode('.', $qualifiedTable, 2);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Replication metadata table must be schema-qualified');
        }

        foreach ($parts as $part) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $part)) {
                throw new \InvalidArgumentException('Invalid replication metadata identifier');
            }
        }

        return [$parts[0], $parts[1]];
    }

    private static function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new \InvalidArgumentException('Invalid replication metadata identifier');
        }

        return '`' . $identifier . '`';
    }
}
