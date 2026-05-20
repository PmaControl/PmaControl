<?php

declare(strict_types=1);

namespace App\Library;

use App\Library\Security\Identifier;
use App\Library\Sql\WhereBuilder;

final class SelectorOptions
{
    private const MYSQL_DATABASE_COLUMNS = ['id', 'name', 'schema_name'];

    public static function databaseNamesByServerId($idMysqlServer, bool $excludeDeleted = true): array
    {
        $serverId = (int) $idMysqlServer;
        if ($serverId <= 0) {
            return [];
        }

        $dbLink = MysqlServer::getDbLinkFromId($serverId, $excludeDeleted);
        if (empty($dbLink)) {
            return [];
        }

        return self::databaseNamesFromConnection($dbLink);
    }

    public static function databaseNamesFromConnection($dbLink): array
    {
        $res = $dbLink->sql_query('SHOW DATABASES');
        $databases = [];

        while ($ob = $dbLink->sql_fetch_object($res)) {
            $name = (string) $ob->Database;
            $databases[] = [
                'id' => $name,
                'libelle' => $name,
            ];
        }

        return $databases;
    }

    public static function recordedDatabasesByServerId(
        $db,
        $idMysqlServer,
        string $idField = 'id',
        string $labelField = 'schema_name',
        ?string $orderField = null
    ): array {
        $serverId = (int) $idMysqlServer;
        if ($serverId <= 0) {
            return [];
        }

        $idColumn = self::mysqlDatabaseColumn($idField);
        $labelColumn = self::mysqlDatabaseColumn($labelField);

        $sql = 'SELECT `'.$idColumn.'` AS id, `'.$labelColumn.'` AS libelle'
            .' FROM mysql_database WHERE id_mysql_server = '.$serverId;

        if ($orderField !== null) {
            $sql .= ' ORDER BY `'.self::mysqlDatabaseColumn($orderField).'`';
        }

        $res = $db->sql_query($sql.';');
        $databases = [];

        while ($ob = $db->sql_fetch_object($res)) {
            $databases[] = [
                'id' => $ob->id,
                'libelle' => (string) $ob->libelle,
            ];
        }

        return $databases;
    }

    public static function sharedDatabaseNamesByServerIds(array $idMysqlServers, callable $connectionResolver): array
    {
        if ($idMysqlServers === []) {
            return [];
        }

        $max = count($idMysqlServers);
        $names = [];

        foreach ($idMysqlServers as $idMysqlServer) {
            $dbLink = $connectionResolver($idMysqlServer);
            foreach (self::databaseNamesFromConnection($dbLink) as $database) {
                $names[] = (string) $database['id'];
            }
        }

        $counts = array_count_values($names);
        $databases = [];

        foreach ($counts as $name => $count) {
            $databases[] = [
                'id' => $name,
                'libelle' => '('.$count.'/'.$max.') '.$name,
            ];
        }

        return $databases;
    }

    public static function tableNamesByServerIdAndDatabase($idMysqlServer, string $database, callable $connectionResolver): array
    {
        if (!Identifier::isDatabaseName($database)) {
            throw new \InvalidArgumentException('Invalid database name for selector.');
        }

        $dbLink = $connectionResolver((int) $idMysqlServer);
        $dbLink->sql_query('USE `'.$database.'`;');
        $tables = $dbLink->getListTable();

        $options = [];
        foreach ($tables['table'] ?? [] as $table) {
            $name = (string) $table;
            $options[] = [
                'id' => $name,
                'libelle' => $name,
            ];
        }

        return $options;
    }

    public static function timeSeriesVariables($db, ?string $type = null, string $idMode = 'qualified'): array
    {
        if (!in_array($idMode, ['qualified', 'id'], true)) {
            throw new \InvalidArgumentException('Invalid time-series selector id mode.');
        }

        if ($type === null) {
            $sql = 'SELECT * FROM ts_variable order by `from`, `name`;';
        } else {
            $sql = 'SELECT * from ts_variable ' . WhereBuilder::where($db, ['type' => $type]) . ';';
        }

        $res = $db->sql_query($sql);
        $variables = [];

        while ($ob = $db->sql_fetch_object($res)) {
            $from = (string) $ob->from;
            $name = (string) $ob->name;
            $label = $from.'::'.$name;

            $variables[] = [
                'id' => $idMode === 'id' ? $ob->id : $label,
                'libelle' => $label,
                'extra' => [
                    'data-content' => "<small class='text-muted'>".$from.'</small> '.$name,
                ],
            ];
        }

        return $variables;
    }

    private static function mysqlDatabaseColumn(string $column): string
    {
        if (!in_array($column, self::MYSQL_DATABASE_COLUMNS, true)) {
            throw new \InvalidArgumentException('Invalid mysql_database selector column.');
        }

        return $column;
    }
}
