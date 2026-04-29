<?php

declare(strict_types=1);

namespace App\Library;

use Glial\Sgbd\Sgbd;

final class MysqlServer
{
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
}
