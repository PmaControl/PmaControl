<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
  https://github.com/fanthos/chartjs-chart-timeline/wiki  => changement de variable
 * https://jsfiddle.net/fanthos/8vrme4bt/ => demo
 *
 * https://nagix.github.io/chartjs-plugin-datasource/
 * https://www.npmjs.com/package/chartjs-plugin-datasource-prometheus

 * vertical line

 *  */

namespace App\Library;

use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;

/**
 * Class responsible for extraction2 workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Extraction2
{
    use \App\Library\Filter;
/**
 * Stores `$variable` for variable.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $variable   = array();
/**
 * Stores `$server` for server.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $server     = array();
/**
 * Stores `$groupbyday` for groupbyday.
 *
 * @var bool
 * @phpstan-var bool
 * @psalm-var bool
 */
    static $groupbyday = false;
/**
 * Stores `$ts_file` for ts file.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $ts_file = array();


/**
 * Stores `$partition` for partition.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $partition = array();
    static float $lastRuntimeCacheTouch = 0.0;

    public static function resetRuntimeState(): void
    {
        self::$variable = [];
        self::$server = [];
        self::$ts_file = [];
        self::$partition = [];
    }

    public static function resetRuntimeStateIfStale(?float $now = null, float $ttlSeconds = 1.0): bool
    {
        $now ??= microtime(true);

        if (self::$lastRuntimeCacheTouch > 0.0 && ($now - self::$lastRuntimeCacheTouch) > $ttlSeconds) {
            self::resetRuntimeState();
            self::$lastRuntimeCacheTouch = $now;
            return true;
        }

        self::$lastRuntimeCacheTouch = $now;
        return false;
    }

    public static function shouldUseDirectPointLookup(array $server, $date, bool $range): bool
    {
        return !empty($date)
            && $range === false
            && count($server) === 1
            && is_array($date)
            && count($date) === 1;
    }

    public static function shouldUseTsMaxDate($date): bool
    {
        return empty($date) || $date === 'MAX_DATE';
    }

    public static function buildExplicitDateFilter(array $dates): ?string
    {
        if (count($dates) === 0) {
            return null;
        }

        return " AND a.`date` IN ('".implode("','", $dates)."') ";
    }

    public static function getExtraIdentifierFieldByRadical(string $radical): ?string
    {
        return match ($radical) {
            'slave' => 'connection_name',
            'digest' => 'id_ts_mysql_query',
            default => null,
        };
    }

    public static function buildValueTableName(string $radical, string $type): string
    {
        return 'ts_value_'.$radical.'_'.$type;
    }

    public static function buildSelectFields(string $radical, string $type, bool $graph = false): string
    {
        if ($radical === 'slave') {
            return "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, a.`connection_name`,a.`date`,a.`value` ";
        }

        if ($radical === 'digest') {
            return "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, a.`id_ts_mysql_query` as `id_ts_mysql_query`, a.`date`, a.`value` ";
        }

        $fields = "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, 'N/A' as `connection_name`,a.`date` ";

        if ($graph) {
            return $fields.", ((a.`value` - LAG(a.`value`) OVER W))/(TIME_TO_SEC(TIMEDIFF(a.date, lag(a.date) OVER W))) as value  ";
        }

        return $fields.", a.`value` as value ";
    }

    public static function normalizeDisplayValue(string $type, $value)
    {
        if ($type === 'json') {
            return json_decode(trim((string) $value), true);
        }

        return trim((string) $value);
    }

    public static function appendDisplayRow(array $table, object $row, bool $range): array
    {
        $radical = self::$variable[$row->id_ts_variable]['radical'];
        $metricName = self::$variable[$row->id_ts_variable]['name'];
        $specialField = self::getExtraIdentifierFieldByRadical($radical);

        if (!in_array($radical, ['digest', 'slave'], true)) {
            $value = self::normalizeDisplayValue((string) $row->type, $row->value);

            if ($range) {
                $table[$row->id_mysql_server][$row->date]['id_mysql_server'] = $row->id_mysql_server;
                $table[$row->id_mysql_server][$row->date]['date'] = $row->date;
                $table[$row->id_mysql_server][$row->date][$metricName] = $value;
            } else {
                $table[$row->id_mysql_server]['id_mysql_server'] = $row->id_mysql_server;
                $table[$row->id_mysql_server]['date'] = $row->date;
                $table[$row->id_mysql_server][$metricName] = $value;
            }

            return $table;
        }

        $groupKey = '@'.$radical;
        $identifier = '';
        if ($specialField !== null && isset($row->{$specialField})) {
            $identifier = (string) $row->{$specialField};
        }

        if (!isset($row->value)) {
            $row->value = '';
        }

        $value = trim((string) $row->value);

        if ($range) {
            $table[$row->id_mysql_server][$groupKey][$identifier][$row->date]['id_mysql_server'] = $row->id_mysql_server;
            $table[$row->id_mysql_server][$groupKey][$identifier][$row->date]['date'] = $row->date;
            $table[$row->id_mysql_server][$groupKey][$identifier][$row->date][$metricName] = $value;
        } else {
            $table[$row->id_mysql_server][$groupKey][$identifier]['id_mysql_server'] = $row->id_mysql_server;
            $table[$row->id_mysql_server][$groupKey][$identifier]['date'] = $row->date;
            $table[$row->id_mysql_server][$groupKey][$identifier][$metricName] = $value;
        }

        return $table;
    }

    public static function buildDirectQuerySegments(array $idTsVariables, int $idMysqlServer, string $date, string $partition): array
    {
        $sql = [];

        foreach ($idTsVariables as $radical => $dataType) {
            foreach ($dataType as $type => $tabIds) {
                $fields = self::buildSelectFields((string) $radical, (string) $type, false);
                $tableName = self::buildValueTableName((string) $radical, (string) $type);
                $idTsVariable = implode(',', $tabIds);

                $sql[] = "(SELECT ".$fields." FROM `".$tableName."` PARTITION(`".$partition."`) a "
                    ." WHERE id_ts_variable IN (".$idTsVariable.")
                    AND a.id_mysql_server = ".$idMysqlServer." AND a.`date` = '".$date."' ) ";
            }
        }

        return $sql;
    }

/**
 * Handle extraction2 state through `extract`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @param array $server Input value for `server`.
 * @phpstan-param array $server
 * @psalm-param array $server
 * @param mixed $date Input value for `date`.
 * @phpstan-param mixed $date
 * @psalm-param mixed $date
 * @param mixed $range Input value for `range`.
 * @phpstan-param mixed $range
 * @psalm-param mixed $range
 * @param mixed $graph Input value for `graph`.
 * @phpstan-param mixed $graph
 * @psalm-param mixed $graph
 * @return mixed Returned value for extract.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::extract()
 * @example /fr/extraction2/extract
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function extract($var = array(),array $server = array(), $date = "", $range = false, $graph = false)
    {
        /*
          debug($var);
          debug($server);
          debug($date);
          /**** */

        //  Debug::debug($var);
        //  Debug::debug($server);
        //  Debug::debug($date);

        //Debug::debug($date);

        self::resetRuntimeStateIfStale();

        $db = Sgbd::sql(DB_DEFAULT);
        $sql3 = "";

        //il faudrait retirer la liste qui peut ralentir la requette quand il y a beaucoup de serveurs
        if (empty($server)) {
            $server = self::getServerList();
        } else {
            $server = self::filterServerList($server);
            sort($server);
            self::$server = $server;
        }


        //Debug::debug(self::$server, "SERVER");

        if (self::shouldUseDirectPointLookup(self::$server, $date, (bool) $range)) {
            
            $date  = implode(',', $date);

            $sql3 = self::getQuery([$var, self::$server, $date]);
        } else {

            $extra_where = "";
            $INNER       = "";
            $useMaxDate = self::shouldUseTsMaxDate($date);

            if ($useMaxDate) {

                $INNER = "\n INNER JOIN ts_max_date b ON a.id_mysql_server = b.id_mysql_server AND a.date = b.date ";
                $INNER .= "\n INNER JOIN `ts_variable` c ON a.`id_ts_variable` = c.id AND b.`id_ts_file` = c.`id_ts_file` ";

                //$extra_where = " AND a.`date` > date_sub(now(), INTERVAL 1 DAY) ";
            } else {

                if (is_array($date)) {
                    if ($range) {
                        $date_min = $date[0];
                        $date_max = $date[1];

                        $extra_where = " AND a.`date` BETWEEN '".$date_min."' AND '".$date_max."' ";
                    } else {

                        // il y aurait moyen de faire mieux pour récupérer uniquement les bonnes dates 
                        // certainement avec un windows function

                        $sql_get_mindate = "select id_mysql_server, id_ts_file, max(date) as date from ts_date_by_server 
                        where date = '".$date[0]."' 
                        AND id_mysql_server IN (".implode(",", self::$server ).")
                        AND id_ts_file in (".implode(",", self::$ts_file ).")
                        group by 1,2;";

                        Debug::sql($sql_get_mindate, "GET MINDATE");

                        $res2 = $db->sql_query($sql_get_mindate);

                        $dates = array();
                        while ($arr = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                            $dates[] = $arr['date'];                        
                        }        

                        $explicitDateFilter = self::buildExplicitDateFilter($dates);
                        if ($explicitDateFilter === null) {
                            return [];
                        }

                        $extra_where = $explicitDateFilter;

                    }
                } else {
                    $extra_where = " AND a.`date` > date_sub(now(), INTERVAL $date) "; // JIRA-MARIADB : https://jira.mariadb.org/browse/MDEV-17355?filter=-2
                    $extra_where .= " AND a.`date` <= now() ";
                }

                if ($date !== "MAX_DATE") {
                    $extra_where .= " AND a.`date` <= now() ";
                }

                //$extra_where .= " GROUP BY id_mysql_server, id_ts_variable, date(a.`date`), hour(a.`date`)";
                //$extra_where .= ", minute(a.`date`)";

                $INNER = "\n INNER JOIN `ts_date_by_server` b on a.`date` = b.`date` AND a.`id_mysql_server` = b.`id_mysql_server` ";
                $INNER .= "\n INNER JOIN `ts_variable` c ON a.`id_ts_variable` = c.id AND b.`id_ts_file` = c.`id_ts_file` ";
            }

            //Debug::debug($var, "VAR");

            $sql2     = array();
            $WINDOW   = "";

            $variable = self::getIdVariable($var);
            //Debug::debug($variable);

            foreach ($variable as $radical => $data_type) {
                foreach ($data_type as $type => $tab_ids) {

                    //Debug::debug($radical);

                    $fields = self::buildSelectFields((string) $radical, (string) $type, (bool) $graph);

                    if ($graph === true && !in_array($radical, ['slave', 'digest'], true)) {
                        $WINDOW = " WINDOW W AS (ORDER BY a.date) ";
                    }

                    foreach ($tab_ids as $id_ts_variable) {

                        if (empty(self::$partition[$id_ts_variable])){
                            continue;
                        }

                        // meilleur plan d'execution en splitant par id_varaible pour un meilleur temps d'exec 
                        // => ce qui n'est plus le cas avec 7 Milliards d'enregistremenets avec une date specifique
                        // permet d'avoir toutes les données 
                        $filter_partition = "";
                        if (implode(",", $server ) != "-999") {
                            $filter_partition = "PARTITION (".self::$partition[$id_ts_variable].")";
                        }
                        
                        $sql4 = "(SELECT ".$fields." FROM `".self::buildValueTableName((string) $radical, (string) $type)."` $filter_partition a "
                        .$INNER."
                        WHERE id_ts_variable = ".$id_ts_variable."
                        AND a.id_mysql_server IN (".implode(",", $server).")  $extra_where ".$WINDOW.") ";

                        $sql2[] = $sql4;
                    }
                


                }

                $sql3 = implode(" \nUNION ALL\n ", $sql2);

                if ($graph === true) {

                    $sql3 = "WITH t as ($sql3)
                        SELECT t.id_mysql_server,
                        id_ts_variable,";

                    if (self::$groupbyday) {
                        $sql3 .= " date(t.`date`) as day, ";
                    }


                    $sql3 .= "
                        connection_name,
                        group_concat(concat('{x:new Date(\'',t.`date`, '\'),y:',t.`value`,'}') ORDER BY t.`date` ASC) as graph,
                        round(min(t.`value`),2) as `min`,
                        round(max(t.`value`),2) as `max`,
                        round(avg(t.`value`),2) as `avg`,
                        round(std(t.`value`),2) as `std`
                    FROM t GROUP BY id_mysql_server, id_ts_variable ";

                    //$sql3 .= ", date(t.`date`), hour(t.`date`), minute(t.`date`)";

                    if (self::$groupbyday) {
                        $sql3 .= " ,date(t.`date`) ";
                    } else {
                        $sql3 .= " order by `std` desc;";
                    }
                }

            }
        }
        
        if (empty($sql3)) {
            return [];
        }

        //Debug::sql($sql3);


        $db->sql_query('SET SESSION group_concat_max_len = 100000000');


        //echo \SqlFormatter::format($sql3)."\n";
        //Debug::sql($sql3);


        
        $res2 = $db->sql_query($sql3);

        if ($db->sql_num_rows($res2) === 0) {
            return false;
        }

        return $res2;
    }

/**
 * Retrieve extraction2 state through `getServerList`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getServerList.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getServerList()
 * @example /fr/extraction2/getServerList
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getServerList()
    {


        $db = Sgbd::sql(DB_DEFAULT);

        if (empty(self::$server)) {
            $sql = "SELECT id FROM mysql_server PARTITION(`pn`) a WHERE is_deleted=0 ".self::getFilter();

            $res = $db->sql_query($sql);

            $server = array();
            while ($ob     = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $server[] = $ob['id'];
            }

            if (count($server) === 0) {//int negatif pour être sur de rien remonté
                $server[] = "-999";
            }

            self::$server = $server;
        }

        return self::$server;
    }

/**
 * Handle extraction2 state through `filterServerList`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $server Input value for `server`.
 * @phpstan-param array $server
 * @psalm-param array $server
 * @return mixed Returned value for filterServerList.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::filterServerList()
 * @example /fr/extraction2/filterServerList
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static private function filterServerList(array $server)
    {
        if (empty($server)) {
            return $server;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id FROM mysql_server a WHERE a.is_deleted=0 ".self::getFilter($server, 'a');
        //Debug::sql($sql, "FILTER SERVER LIST");

        $res = $db->sql_query($sql);

        $filtered = array();
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $filtered[] = $ob['id'];
        }

        if (count($filtered) === 0) { // int negatif pour être sur de rien remonté
            $filtered[] = "-999";
        }

        return $filtered;
    }

/**
 * Handle extraction2 state through `display`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @param mixed $server Input value for `server`.
 * @phpstan-param mixed $server
 * @psalm-param mixed $server
 * @param mixed $date Input value for `date`.
 * @phpstan-param mixed $date
 * @psalm-param mixed $date
 * @param mixed $range Input value for `range`.
 * @phpstan-param mixed $range
 * @psalm-param mixed $range
 * @param mixed $graph Input value for `graph`.
 * @phpstan-param mixed $graph
 * @psalm-param mixed $graph
 * @return mixed Returned value for display.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::display()
 * @example /fr/extraction2/display
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function display($var = array(), $server = array(), $date = "", $range = false, $graph = false)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = self::extract($var, $server, $date, $range, $graph);


        $table = array();

        if ($res === false || !($res instanceof \mysqli_result)) {
            return $table;
        }

        while ($ob = $db->sql_fetch_object($res)) {
            $table = self::appendDisplayRow($table, $ob, (bool) $range);
        }

//debug($table);
        return $table;
    }

/**
 * Retrieve extraction2 state through `getIdVariable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @return mixed Returned value for getIdVariable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getIdVariable()
 * @example /fr/extraction2/getIdVariable
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getIdVariable($var)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sqls = array();

        if (!is_array($var)) {
            throw new \Exception("PMACTRL-548 : ".__FILE__."/".__FUNCTION__." : arg $var must be an array !", 548);
        }

        foreach ($var as $val) {
            $split = explode("::", $val);

            if (count($split) === 2) {

                //Debug::debug($split);
                $name = $split[1];
                $from = $split[0];

                if (empty($name)) {
                    // get partition in this case
                    $sqls[] = "(SELECT * FROM ts_variable where `from` = '".strtolower($from)."')";
                } else {

                    $sqls[] = "(SELECT * FROM ts_variable where `name` = '".strtolower($name)."' AND `from` = '".strtolower($from)."' LIMIT 1)";
                }
            } else {
                $name   = $split[0];
                $sqls[] = "(SELECT * FROM ts_variable where `name` = '".strtolower($name)."' LIMIT 1)";
            }
        }

        $sql = implode(' UNION ALL ', $sqls);
        //Debug::sql($sql);

        $res = $db->sql_query($sql);

//echo \SqlFormatter::format($sql) . "\n";
        $from     = array();
        $variable = array();
        $ids_variables = array();
        
        while ($ob       = $db->sql_fetch_object($res)) {
            if (! in_array($ob->id_ts_file, self::$ts_file ))
            {
                self::$ts_file[] = $ob->id_ts_file;
            }

            self::$variable[$ob->id]['name']                 = $ob->name;
            self::$variable[$ob->id]['radical']              = $ob->radical;
            self::$variable[$ob->id]['id_ts_file'] = $ob->id_ts_file;
            self::$variable[$ob->id]['type']       = strtolower($ob->type);

            $variable[$ob->radical][strtolower($ob->type)][] = $ob->id;
            //$radical                              = $ob->radical;

            $ids_variables[] = $ob->id;
        }

        self::getPartition($ids_variables);

        return $variable;
    }


/**
 * Retrieve extraction2 state through `getPartition`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $ids_variable Input value for `ids_variable`.
 * @phpstan-param array $ids_variable
 * @psalm-param array $ids_variable
 * @return mixed Returned value for getPartition.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getPartition()
 * @example /fr/extraction2/getPartition
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getPartition(array $ids_variable)
    {

        if (count($ids_variable) === 0){
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $list_id_variable = implode(",",$ids_variable);
        $max_date = self::getLastPartition();

        // array_sub 
        // Only request missing id

        $sql = "SELECT 
        c.id AS ts_variable_id,
        group_concat(distinct concat('p',TO_DAYS(b.date) + 1)) AS partition_day,
        group_concat(b.id_mysql_server)
        FROM ts_max_date b
        JOIN ts_variable c ON b.id_ts_file = c.id_ts_file
        WHERE c.id in (".$list_id_variable.")
        AND b.date != b.date_p4
        AND date > '".$max_date."' AND b.id_mysql_server IN (".implode(',', self::$server).")
        GROUP BY c.id;";

        // AND b.date != b.date_p4 => empeche de ramener unitilement la premiere partition dans le cas ou il y a jamais eu de données.

        //Debug::debug($sql, "PARTITIONS");
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)){
            self::$partition[$ob->ts_variable_id] = $ob->partition_day;
        }

        return self::$partition;
    }


/**
 * Retrieve extraction2 state through `getPartitionFrom`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getPartitionFrom.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getPartitionFrom()
 * @example /fr/extraction2/getPartitionFrom
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getPartitionFrom($param)
    {
        $id_mysql_server = $param[0];
        $from  = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT id_ts_file, `from`, count(1) FROM ts_variable GROUP BY id_ts_file, `from`;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)){
            self::$partition[$ob->ts_variable_id] = $ob->partition_day;
        }
    }


/**
 * Retrieve extraction2 state through `getPartitionFromDate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $date Input value for `date`.
 * @phpstan-param mixed $date
 * @psalm-param mixed $date
 * @return mixed Returned value for getPartitionFromDate.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getPartitionFromDate()
 * @example /fr/extraction2/getPartitionFromDate
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getPartitionFromDate($date)
    {
        //$date = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT to_days('$date')+1 AS `partition`;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)){
            return "p{$ob->partition}";
        }
    }


    // trick to optimze query when 1 server with one date !

/**
 * Retrieve `getQuery`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getQuery.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example getQuery(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getQuery($param)
    {
        self::resetRuntimeStateIfStale();

        $variables = $param[0];
        $id_mysql_server = end($param[1]);
        $date = $param[2];
        
        $id_ts_variables = self::getIdVariable($variables);
        //Debug::debug($id_ts_variables);

        $partition = self::getPartitionFromDate($date);

        $sql3 = implode(" \nUNION ALL\n ", self::buildDirectQuerySegments($id_ts_variables, (int) $id_mysql_server, (string) $date, (string) $partition));

        return $sql3;

    }

/**
 * Retrieve `getLastPartition`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getLastPartition.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example getLastPartition(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getLastPartition()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT FROM_DAYS(CAST(SUBSTRING(MIN(PARTITION_NAME), 2) AS UNSIGNED) - 1) AS partition_date
        FROM information_schema.partitions
        WHERE TABLE_NAME = 'ts_value_general_int' AND table_schema=database();";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $date = $ob->partition_date;
        }

        return $date;
    }

    /*
    public static function getLast5Value($var = array(), $server = array())
    {

        $db = Sgbd::sql(DB_DEFAULT);

        if (empty($server)) {
            $server = self::getServerList();
        }


        $sql = "SELECT a.id_mysql_server, a.date, a.date_p1, a.date_p2,a.date_p3,a.date_p4 FROM ts_max_date a
        INNER JOIN ts_file b ON a.id_ts_file = b.id
        INNER JOIN ts_variable c on c.id_ts_file = b.id
        WHERE `name` = 'server_uid' and a.id_mysql_server in('".$server."');";

        $res = $db->sql_query($sql);

        //return  Display::display($var, $id_mysql_server);
    }*/





    public static function getLast5Value($var = array(), $server = array())
    {
        self::resetRuntimeStateIfStale();

        $db = Sgbd::sql(DB_DEFAULT);

        if (empty($server)) {
            $server = self::getServerList();
        } else {
            $server = self::filterServerList($server);
            self::$server = $server;
        }

        // 1. Variables avec radical / type / id_ts_file
        $variables = self::getIdVariable($var);

        $result = [];

        foreach ($server as $id_server) {

            foreach ($variables as $radical => $types) {

                if (!in_array($radical, ["general", "slave"])) {
                    continue; // on ignore digest / status / variables
                }

                foreach ($types as $type => $list_ids) {

                    foreach ($list_ids as $id_ts_variable) {

                        $var_name = self::$variable[$id_ts_variable]['name'];
                        $id_ts_file = self::$variable[$id_ts_variable]['id_ts_file'];

                        // 2. Récupère les 5 dernières dates
                        $sql = "SELECT date, date_p1, date_p2, date_p3, date_p4
                            FROM ts_max_date
                            WHERE id_mysql_server = {$id_server}
                            AND id_ts_file = {$id_ts_file};
                        ";
                        Debug::sql($sql);

                        $res = $db->sql_query($sql);
                        if ($row = $db->sql_fetch_object($res)) {

                            $dates = array_filter([
                                $row->date,
                                $row->date_p1,
                                $row->date_p2,
                                $row->date_p3,
                                $row->date_p4
                            ]);

                            // 3. Pour chaque date → partition directe
                            foreach ($dates as $dt) {

                                $partition = self::getPartitionFromDate($dt);
                                $table = "ts_value_{$radical}_{$type}";

                                $extra = ($radical === "slave") ? ", connection_name" : "";

                                $sqlv = "SELECT value {$extra}
                                    FROM {$table} PARTITION({$partition})
                                    WHERE id_mysql_server = {$id_server}
                                    AND id_ts_variable = {$id_ts_variable}
                                    AND date = '{$dt}'
                                    LIMIT 1;
                                ";
                                Debug::sql($sqlv);

                                $res2 = $db->sql_query($sqlv);

                                if ($ob = $db->sql_fetch_object($res2)) {

                                    // JSON decode
                                    $value = ($type === "json")
                                        ? json_decode($ob->value, true)
                                        : trim($ob->value);

                                    if ($radical === "slave") {
                                        $result[$id_server]['@slave'][$ob->connection_name][$var_name][] = [
                                            'date'  => $dt,
                                            'value' => $value
                                        ];
                                    } else {
                                        $result[$id_server][$var_name][] = [
                                            'date'  => $dt,
                                            'value' => $value
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

}
