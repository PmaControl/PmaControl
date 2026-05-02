<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ForeignKeyRoute
{
    public static function normalize(array $param): ?array
    {
        $idMysqlServer = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        if ($idMysqlServer === null) {
            return null;
        }

        $database = self::normalizeDatabaseName($param[1] ?? null);
        if ($database === null) {
            return null;
        }

        return [
            'id_mysql_server' => $idMysqlServer,
            'database' => $database,
            'param' => [$idMysqlServer, $database],
        ];
    }

    public static function normalizeDatabaseName($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $database = trim((string) $value);
        if ($database === '' || !Identifier::isDatabaseName($database)) {
            return null;
        }

        return $database;
    }
}
