<?php

namespace App\Library;

use \Glial\Sgbd\Sgbd;

/**
 * Public reader for the last known value of MySQL global variables.
 *
 * `Variable::last(['have_ssl', 'version', ...], [1, 2, ...])` returns
 * the most recent value persisted in the `global_variable` table for
 * each (id_mysql_server, variable_name) pair. That table is the
 * "last known state" mirror maintained by
 * `Listener::afterUpdateVariable()` whenever a `mysql_global_variable`
 * collector file is ingested, so reads still succeed when the VPN to
 * the monitored servers has been down for days — unlike Extraction2,
 * which queries `ts_variable` (cleared once no fresh sample arrives).
 *
 * Output shape matches Extraction2::display() so this is a drop-in
 * replacement at the call site:
 *
 *   [
 *     1 => ['id_mysql_server' => 1, 'date' => '2026-05-19 02:50:35',
 *           'version' => '10.11.17-MariaDB-deb12-log', ...],
 *     2 => ['id_mysql_server' => 2, ...],
 *   ]
 *
 * The 'date' meta-key is the MAX(row_start) across the requested
 * variables for that server, i.e. the freshness of the freshest
 * variable returned. `global_variable` uses MariaDB SYSTEM VERSIONING,
 * so row_start is the per-row last-update timestamp.
 *
 * @since 5.1
 */
class Variable
{
    /**
     * Return the last known value of each $variables for each $serverIds.
     *
     * Accepts the `from::name` notation Extraction2 uses (e.g.
     * `variables::hostname`) for call-site symmetry; only the `name`
     * part is used since `global_variable` is already the mirror of the
     * `variables` source.
     *
     * @param array<int,string> $variables Variable names (lower-case).
     * @param array<int,int>    $serverIds mysql_server.id list.
     * @return array<int,array<string,mixed>>
     */
    public static function last(array $variables, array $serverIds): array
    {
        $out = [];

        if ($variables === [] || $serverIds === []) {
            return $out;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $names = [];
        foreach ($variables as $var) {
            $parts = explode('::', (string) $var, 2);
            $name = strtolower(trim(end($parts)));
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

        $sql = "SELECT `id_mysql_server`, `variable_name`, `value`, `row_start`
                FROM `global_variable` PARTITION(pn)
                WHERE `id_mysql_server` IN ($idList)
                  AND `variable_name` IN ($nameList);";

        $res = $db->sql_query($sql);
        if ($res === false || !($res instanceof \mysqli_result)) {
            return $out;
        }

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int) $row['id_mysql_server'];
            if (!isset($out[$id])) {
                $out[$id] = ['id_mysql_server' => $id, 'date' => $row['row_start']];
            } elseif ($row['row_start'] > $out[$id]['date']) {
                $out[$id]['date'] = $row['row_start'];
            }
            $out[$id][$row['variable_name']] = (string) $row['value'];
        }

        return $out;
    }
}
