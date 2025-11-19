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

class Extraction2
{
    use \App\Library\Filter;
    static $variable   = array();
    static $server     = array();
    static $groupbyday = false;
    static $ts_file = array();


    static $partition = array();

    static public function extract($var = array(), $server = array(), $date = "", $range = false, $graph = false)
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

        $db = Sgbd::sql(DB_DEFAULT);

        //il faudrait retirer la liste qui peut ralentir la requette quand il y a beaucoup de serveurs
        if (empty($server)) {
            $server = self::getServerList();
        }
        else
        {
            sort($server);
            self::$server = $server;
        }
        //Debug::debug($server, "SERVER LIST");

        //Debug::debug($variable);



        if (! empty($date) && $range == false 
        && count($server) == 1 
        && count($date) == 1){
            
            $date  = implode(',', $date);

            $sql3 = self::getQuery(array($var, $server, $date));
        } else {

            $extra_where = "";
            $INNER       = "";
            if (empty($date)) {

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
                        AND id_mysql_server IN (".implode(",", $server ).")
                        AND id_ts_file in (".implode(",", self::$ts_file ).")
                        group by 1,2;";

                        Debug::sql($sql_get_mindate, "GET MINDATE");

                        $res2 = $db->sql_query($sql_get_mindate);

                        $dates = array();
                        while ($arr = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                            $dates[] = $arr['date'];                        
                        }        

                        $all_date    = implode("','", $dates);
                        $extra_where = " AND a.`date` IN ('".$all_date."') ";

                    }
                } if($date === "MAX_DATE")
                {

                }
                else {
                    $extra_where = " AND a.`date` > date_sub(now(), INTERVAL $date) "; // JIRA-MARIADB : https://jira.mariadb.org/browse/MDEV-17355?filter=-2
                    $extra_where .= " AND a.`date` <= now() ";
                }

                $extra_where .= " AND a.`date` <= now() ";

                //$extra_where .= " GROUP BY id_mysql_server, id_ts_variable, date(a.`date`), hour(a.`date`)";
                //$extra_where .= ", minute(a.`date`)";

                $INNER = "\n INNER JOIN `ts_date_by_server` b on a.`date` = b.`date` AND a.`id_mysql_server` = b.`id_mysql_server` ";
                $INNER .= "\n INNER JOIN `ts_variable` c ON a.`id_ts_variable` = c.id AND b.`id_ts_file` = c.`id_ts_file` ";
            }

            //Debug::debug($var, "VAR");

            $sql2     = array();
            $WINDOW   = "";

            $variable = self::getIdVariable($var);
            Debug::debug($variable);

            foreach ($variable as $radical => $data_type) {
                foreach ($data_type as $type => $tab_ids) {

                    //Debug::debug($radical);

                    if ($radical == "slave") {
                        $fields = "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, a.`connection_name`,a.`date`,a.`value` ";
                    }
                    elseif ($radical === "digest") {
                        // digest => pas de connection_name
                        $fields = "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, a.`id_ts_mysql_query` as `id_ts_mysql_query`, a.`date`, a.`value` ";
                    } else {
                        $fields = "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, 'N/A' as `connection_name`,a.`date` ";

                        if ($graph === true) {
                            //$fields .= ",  (a.`value` - LAG(a.`value`) OVER W) as value ";
                            //$fields .= ",  TIME_TO_SEC(TIMEDIFF(a.date, lag(a.date) OVER W)) as diff "; //diefenre en sec entre 2 capture de metrics
                            $fields .= ", ((a.`value` - LAG(a.`value`) OVER W))/(TIME_TO_SEC(TIMEDIFF(a.date, lag(a.date) OVER W))) as value  "; // in case of difference

                            $WINDOW = " WINDOW W AS (ORDER BY a.date) ";
                            //$WINDOW = " WINDOW W AS (PARTION BY EXTRACT(DAY_MINUTE FROM a.date) ORDER BY a.date) ";
                        } else {
                            $fields .= ", a.`value` as value ";
                        }

                        //a.`value`";
                        // $fields = " a.`id_mysql_server`, a.`id_ts_variable`, '' as connection_name,a.`date`,avg(a.`value`) as value";
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
                        
                        $sql4 = "(SELECT ".$fields." FROM `ts_value_".$radical."_".$type."` $filter_partition a "
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
        
        Debug::sql($sql3);

        if (empty($sql3)) {
            return [];
        }


        $db->sql_query('SET SESSION group_concat_max_len = 100000000');


        //echo \SqlFormatter::format($sql3)."\n";
        //Debug::sql($sql3);


        
        $res2 = $db->sql_query($sql3);

        if ($db->sql_num_rows($res2) === 0) {
            return false;
        }

        return $res2;
    }

    static function getServerList()
    {


        $db = Sgbd::sql(DB_DEFAULT);

        if (empty(self::$server)) {
            $sql = "SELECT id FROM mysql_server a WHERE 1=1 ".self::getFilter();

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

    static public function display($var = array(), $server = array(), $date = "", $range = false, $graph = false)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = self::extract($var, $server, $date, $range, $graph);


        $table = array();

        if ($res === false) {
            return $table;
        }

        $extra = [
            'digest' => 'id_ts_mysql_query',
            'slave' => 'connection_name'
        ];

        while ($ob = $db->sql_fetch_object($res)) {
            //Debug::debug($ob);
            //Debug::debug(self::$variable[$ob->id_ts_variable]['name']);

            $radical = self::$variable[$ob->id_ts_variable]['radical'];
            //$is_digest = ($radical === 'digest');

            if ($range) {



                if (! in_array($radical, ["digest", "slave"])) {
                    $ob->value = trim($ob->value);
                    if ($ob->type == "json") {
                        $ob->value = json_decode($ob->value, true);
                    }

                    //$group = $is_digest ? '@digest' : null;

                    $table[$ob->id_mysql_server][$ob->date]['id_mysql_server']                            = $ob->id_mysql_server;
                    $table[$ob->id_mysql_server][$ob->date]['date']                                       = $ob->date;
                    $table[$ob->id_mysql_server][$ob->date][self::$variable[$ob->id_ts_variable]['name']] = $ob->value;
                } else {
                    $table[$ob->id_mysql_server]['@'.$radical][$ob->{$extra[$radical]}][$ob->date]['id_mysql_server']                            = $ob->id_mysql_server;
                    $table[$ob->id_mysql_server]['@'.$radical][$ob->{$extra[$radical]}][$ob->date]['date']                                       = $ob->date;
                    $table[$ob->id_mysql_server]['@'.$radical][$ob->{$extra[$radical]}][$ob->date][self::$variable[$ob->id_ts_variable]['name']] = trim($ob->value);
                }
            } else {
                if (! in_array($radical, ["digest", "slave"])) {

                    $ob->value = trim($ob->value);
                    if ($ob->type == "json") {
                        $ob->value = json_decode($ob->value, true);
                    }
                    $table[$ob->id_mysql_server]['id_mysql_server']                            = $ob->id_mysql_server;
                    $table[$ob->id_mysql_server]['date']                                       = $ob->date;
                    $table[$ob->id_mysql_server][self::$variable[$ob->id_ts_variable]['name']] = $ob->value;
                } else {

                    $table[$ob->id_mysql_server]['@'.$radical][$ob->{$extra[$radical]}]['id_mysql_server']                            = $ob->id_mysql_server;
                    $table[$ob->id_mysql_server]['@'.$radical][$ob->{$extra[$radical]}]['date']                                       = $ob->date;

                    if (! isset($ob->value)) {
                        $ob->value = "";
                    }
                    $table[$ob->id_mysql_server]['@'.$radical][$ob->{$extra[$radical]}][self::$variable[$ob->id_ts_variable]['name']] = trim($ob->value);
                }
            }
        }

//debug($table);
        return $table;
    }

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
        Debug::sql($sql);

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
            $variable[$ob->radical][strtolower($ob->type)][] = $ob->id;
            //$radical                              = $ob->radical;

            $ids_variables[] = $ob->id;
        }

        self::getPartition($ids_variables);

        return $variable;
    }


    /*
    static function count_recursive($array)
    {
        if (!is_array($array)) {
            return 1;
        }

        $count = 0;
        foreach ($array as $sub_array) {
            $count += self::count_recursive($sub_array);
        }

        return $count;
    }



    static public function setOption($var, $val)
    {
        self::$$var = $val;
    } */
    /*
     * Cette fonction prend comme paramètres la sortie de la fonction
     * Extraction::display(array("databases::databases"));
     */


     /*
    static public function getSizeByEngine($data)
    {
        $res = array();

        $engines = array();

        foreach ($data as $id_mysql_server => $elems) {
            foreach ($elems as $databases) {

                if (empty($databases['databases'])) {

//Debug($databases);
                    continue;
                }

                $dbs = json_decode($databases['databases'], true);

                foreach ($dbs as $db_attr) {
                    foreach ($db_attr['engine'] as $engine => $row_formats) {
                        foreach ($row_formats as $details) {

                            $res['server'][$id_mysql_server][$engine]['size_data']  = $res['server'][$id_mysql_server][$engine]['size_data'] ?? 0;
                            $res['server'][$id_mysql_server][$engine]['size_index'] = $res['server'][$id_mysql_server][$engine]['size_index'] ?? 0;
                            $res['server'][$id_mysql_server][$engine]['size_free']  = $res['server'][$id_mysql_server][$engine]['size_free'] ?? 0;

                            $res['server'][$id_mysql_server][$engine]['size_data']  += $details['size_data'];
                            $res['server'][$id_mysql_server][$engine]['size_index'] += $details['size_index'];
                            $res['server'][$id_mysql_server][$engine]['size_free']  += $details['size_free'];

                            $engines[] = $engine;
                        }
                    }
                }
            }
        }

        $res['engine'] = array_unique($engines);
        sort($res['engine']);

        return $res;
    }
    */


    static public function getPartition(array $ids_variable)
    {

        if (count($ids_variable) === 0){
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $list_id_variable = implode(",",$ids_variable);


        $max_date = self::getLastPartition();

        //debug($max_date);
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

        Debug::debug($sql, "PARTITIONS");
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)){
            self::$partition[$ob->ts_variable_id] = $ob->partition_day;
        }

        return self::$partition;
    }


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

    public static function getQuery($param)
    {
        $variables = $param[0];
        $id_mysql_server = end($param[1]);
        $date = $param[2];
        
        $id_ts_variables = self::getIdVariable($variables);
        //Debug::debug($id_ts_variables);

        $partition = self::getPartitionFromDate($date);

        $sql2 = [];

        foreach ($id_ts_variables as $radical => $data_type) {
            foreach ($data_type as $type => $tab_ids) {
                //Debug::debug($radical);
                if ($radical == "slave") {
                    $fields = "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, a.`connection_name`,a.`date`,a.`value` ";
                } else {
                    $fields = "'".$type."' as 'type', a.`id_mysql_server`, a.`id_ts_variable`, 'N/A' as `connection_name`,a.`date`, a.value ";
                }

                $id_ts_variable = implode(',',$tab_ids);

                $sql4 = "(SELECT ".$fields." FROM `ts_value_".$radical."_".$type."` PARTITION(`".$partition."`) a "
                ." WHERE id_ts_variable IN (".$id_ts_variable.")
                AND a.id_mysql_server = ".$id_mysql_server." AND a.`date` = '".$date."' ) ";

                $sql2[] = $sql4;
                
            }
        }

        $sql3 = implode(" \nUNION ALL\n ", $sql2);

        return $sql3;

    }

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

        return  Display::display($var, $id_mysql_server);
    }


    

}
