<?php

declare(strict_types=1);

namespace App\Library;

use Glial\Sgbd\Sgbd;

final class MysqlServer
{
    /**
     * Schema names that MySQL/MariaDB ships and that pmacontrol must not
     * treat as user data — for backups, cross-server refresh, schema diff,
     * etc.
     *
     * Casing matches MySQL's lowercase output of SHOW DATABASES; comparisons
     * must be case-insensitive (cf. self::isSystemSchema()).
     */
    public const SYSTEM_SCHEMAS = ['information_schema', 'performance_schema', 'mysql', 'sys'];

    public static function buildDbLinkSql($idDb, bool $excludeDeleted = true): string
    {
        $sql = 'SELECT id,name FROM mysql_server WHERE id = '.(int) $idDb;

        if ($excludeDeleted) {
            $sql .= ' AND is_deleted = 0';
        }

        return $sql.';';
    }

    public static function getDbLinkFromId($idDb, bool $excludeDeleted = true)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query(self::buildDbLinkSql($idDb, $excludeDeleted));
        $dbLink = null;

        while ($ob = $db->sql_fetch_object($res)) {
            $dbLink = Sgbd::sql($ob->name);
        }

        return $dbLink;
    }

    public static function isSystemSchema(?string $name): bool
    {
        if ($name === null || $name === '') {
            return false;
        }

        return in_array(strtolower($name), self::SYSTEM_SCHEMAS, true);
    }
}
