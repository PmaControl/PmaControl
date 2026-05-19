<?php

namespace App\Library;

use \Glial\Sgbd\Sgbd;

/**
 * Reader for the `global_variable` table, the persistent
 * last-known-state mirror of MySQL `SHOW GLOBAL VARIABLES`.
 *
 * Rows are written by Listener::afterUpdateVariable() every time the
 * `mysql_global_variable` collector file is ingested. Unlike `ts_variable`
 * (time series) and Extraction2, this table keeps the most recent value
 * forever, so reads still succeed when the VPN to the monitored servers
 * has been down for days and no fresh sample has arrived.
 *
 * Returned shape matches Extraction2::display() for callers that merge
 * results from both sources:
 *   [id_mysql_server => [variable_name => value, ...]]
 *
 * @since 5.1
 */
class GlobalVariable
{
    /**
     * Fetch the latest known value of $variables for $serverIds from
     * the `global_variable` table.
     *
     * Accepts the same `from::name` notation as Extraction2 (e.g.
     * `variables::hostname`) for call-site symmetry; only the `name`
     * part is used here since `global_variable` is already the mirror
     * of the `variables` source.
     *
     * @param array<int,string> $variables   Variable names to read.
     * @param array<int,int>    $serverIds   mysql_server.id list.
     * @return array<int,array<string,string>>
     */
    public static function display(array $variables, array $serverIds): array
    {
        $out = [];

        if ($variables === [] || $serverIds === []) {
            return $out;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $names = [];
        foreach ($variables as $var) {
            $split = explode('::', (string) $var, 2);
            $name = strtolower(trim(end($split)));
            if ($name !== '') {
                $names[$name] = true;
            }
        }
        if ($names === []) {
            return $out;
        }

        $ids = [];
        foreach ($serverIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
        if ($ids === []) {
            return $out;
        }

        $nameList = "'" . implode(
            "','",
            array_map([$db, 'sql_real_escape_string'], array_keys($names))
        ) . "'";
        $idList = implode(',', array_keys($ids));

        $sql = "SELECT `id_mysql_server`, `variable_name`, `value`
                FROM `global_variable` PARTITION(pn)
                WHERE `id_mysql_server` IN ($idList)
                  AND `variable_name` IN ($nameList);";

        $res = $db->sql_query($sql);
        if ($res === false || !($res instanceof \mysqli_result)) {
            return $out;
        }

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int) $row['id_mysql_server'];
            $out[$id][$row['variable_name']] = (string) $row['value'];
        }

        return $out;
    }
}
