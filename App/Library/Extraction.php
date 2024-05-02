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

 *
 * vertical line
 * https://jsfiddle.net/pu68rhLd/7/
 *  */

namespace App\Library;

use \Glial\Sgbd\Sgbd;

class Extraction
{

    use \App\Library\Filter;
    static $variable   = array();
    static $server     = array();
    static $groupbyday = false;

    static public function extract($var = array(), $server = array(), $date = "", $range = false, $graph = false)
    {

        /*
          debug($var);
          debug($server);
          debug($date);
          /**** */

        $db = Sgbd::sql(DB_DEFAULT);

        if (empty($server)) {
            $server = self::getServerList();
        }

        $extra_where = "";
        $INNER       = "";
        if (empty($date)) {

            $INNER = " INNER JOIN ts_max_date b ON a.id_mysql_server = b.id_mysql_server AND a.date = b.date ";
            $INNER .= " INNER JOIN `ts_variable` c ON a.`id_ts_variable` = c.id AND b.`id_ts_file` = c.`id_ts_file` ";

            //$extra_where = " AND a.`date` > date_sub(now(), INTERVAL 1 DAY) ";
        } else {


            if (is_array($date)) {
                if ($range) {
                    $date_min = $date[0];
                    $date_max = $date[1];

                    $extra_where = " AND a.`date` BETWEEN '".$date_min."' AND '".$date_max."' ";
                } else {

                    //still used ?
                    $all_date    = implode('","', $date);
                    $extra_where = " AND a.`date` IN ('".$all_date."') ";
                }
            } else {
                $extra_where = " AND a.`date` > date_sub(now(), INTERVAL $date) "; // JIRA-MARIADB : https://jira.mariadb.org/browse/MDEV-17355?filter=-2
                $extra_where .= " AND a.`date` <= now() ";
            }

            $extra_where .= " AND a.`date` <= now() ";

            //$extra_where .= " GROUP BY id_mysql_server, id_ts_variable, date(a.`date`), hour(a.`date`)";
            //$extra_where .= ", minute(a.`date`)";

            $INNER = " INNER JOIN `ts_date_by_server` b on a.`date` = b.`date` AND a.`id_mysql_server` = b.`id_mysql_server` ";
            $INNER .= " INNER JOIN `ts_variable` c ON a.`id_ts_variable` = c.id AND b.`id_ts_file` = c.`id_ts_file` ";
        }

        $variable = self::getIdVariable($var);
        $sql2     = array();
        $WINDOW   = "";

        //Debug::debug($var, "VARIABLES");

        foreach ($variable as $radical => $data_type) {
            foreach ($data_type as $type => $tab_ids) {


                //debug($radical);

                if ($radical == "slave") {
                    $fields = " a.`id_mysql_server`, a.`id_ts_variable`, a.`connection_name`,a.`date`,a.`value` ";
                } else {
                    $fields = " a.`id_mysql_server`, a.`id_ts_variable`, '' as connection_name,a.`date`";

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

                    // meilleur plan d'execution en splitant par id_varaible pour un meilleur temps d'exec
                    $sql4 = "(SELECT ".$fields."   FROM `ts_value_".$radical."_".$type."` a "
                        .$INNER." WHERE id_ts_variable = ".$id_ts_variable."";

                    if ($server !== "ALL") {
                        $sql4 .= " AND a.id_mysql_server IN (".implode(",", $server).")";
                    }
                    $sql4 .= $extra_where." ".$WINDOW.") ";

                    $sql2[] = $sql4;
                }
            }
        }

        //debug($sql2);

        $sql3 = implode(" UNION ALL ", $sql2);

        if ($graph === true) {

            // quel est l'interet pour l'order by ?
            //$sql3 .= " ORDER BY id_mysql_server, id_ts_variable, date";
        } else {
            //$sql3 .= "ORDER by date";
        }

        //Debug::debug($sql3);
        //echo \SqlFormatter::format($sql3) . "\n";


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
            FROM t GROUP BY id_mysql_server, connection_name, id_ts_variable ";

            //$sql3 .= ", date(t.`date`), hour(t.`date`), minute(t.`date`)";

            if (self::$groupbyday) {
                $sql3 .= " ,date(t.`date`) ";
            } else {
                $sql3 .= " order by `std` desc;";
            }
        }

        if (empty($sql3)) {
            return false;
        }

        //echo \SqlFormatter::format($sql3)."\n";

        $db->sql_query('SET SESSION group_concat_max_len = 100000000');

        //Debug::sql($sql3);
        $res2 = $db->sql_query($sql3);

        if ($db->sql_num_rows($res2) === 0) {
            return false;
        }

        return $res2;
    }

    static private function getServerList()
    {


        $db = Sgbd::sql(DB_DEFAULT);

        if (empty(self::$server)) {
            $sql = "SELECT id FROM mysql_server a WHERE 1=1 ".self::getFilter();

            $res = $db->sql_query($sql);

            $server = array();
            while ($ob     = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $server[] = $ob['id'];
                //self::$server[] = $ob;
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


        self::isExist($var);

        //return(array());
        if (! is_array($var))
        {
            throw new \Exception('PMACTRL-472 : "'.$var.'" have to be in an array');
        }

        $res = self::extract($var, $server, $date, $range, $graph);

        $table = array();

        if ($res === false) {
            return $table;
        }

        while ($ob = $db->sql_fetch_object($res)) {

            //debug(self::$variable[$ob->id_ts_variable]);
            //$ob->value ?? '';

            if (is_null($ob->value)){
                $ob->value = '';
            }

            if ($range) {
                $table[$ob->id_mysql_server][$ob->connection_name][$ob->date]['id_mysql_server']                            = $ob->id_mysql_server;
                $table[$ob->id_mysql_server][$ob->connection_name][$ob->date]['date']                                       = $ob->date;
                $table[$ob->id_mysql_server][$ob->connection_name][$ob->date][self::$variable[$ob->id_ts_variable]['name']] = trim($ob->value);
            } else {
                $table[$ob->id_mysql_server][$ob->connection_name]['id_mysql_server']                            = $ob->id_mysql_server;
                $table[$ob->id_mysql_server][$ob->connection_name]['date']                                       = $ob->date;
                $table[$ob->id_mysql_server][$ob->connection_name][self::$variable[$ob->id_ts_variable]['name']] = trim($ob->value);
            }
        }

        //debug($table);
        return $table;
    }

    static public function getIdVariable($var)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sqls = array();
        foreach ($var as $val) {
            $split = explode("::", $val);

            if (count($split) === 2) {

                $name = $split[1];
                $from = $split[0];

                if (empty($name)) {
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

        //Debug::debug($sql);

        $res = $db->sql_query($sql);

        
        //echo \SqlFormatter::format($sql) . "\n";
        $from     = array();
        $variable = array();
        while ($ob       = $db->sql_fetch_object($res)) {
            self::$variable[$ob->id]['name']                 = $ob->name;
            $variable[$ob->radical][strtolower($ob->type)][] = $ob->id;
            //$radical                              = $ob->radical;
        }

        //Debug::debug($variable);

        return $variable;
    }

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
    }
    /*
     * Cette fonction prend comme paramètres la sortie de la fonction
     * Extraction::display(array("databases::databases"));
     */

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

    static public function display2($var = array(), $server = array(), $date = "", $range = false, $graph = false)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        //return(array());


        $res = self::extract($var, $server, $date, $range, $graph);
    }


    static public function isExist($var)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        return true;
        foreach ($var as $val) {
            $split = explode("::", $val);

            if (count($split) === 2) {

                $name = $split[1];
                $from = $split[0];

                if (empty($name)) {
                    $sql = "SELECT count(1) as cpt FROM ts_variable where `from` = '".strtolower($from)."'";
                } else {

                    $sql = "SELECT count(1) as cpt FROM ts_variable where `from` = '".strtolower($from)."' and `name` = '".strtolower($name)."'";
                }
            } else {

                $name   = $split[0];
                $sql = "SELECT count(1) as cpt FROM ts_variable where `name` = '".strtolower($name)."'";
            }

            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res))
            {
                if ($ob->cpt === "0") {
                    throw new \Exception("Error Processing Request with Extract value not found : ($val)", 1);
                    
                }elseif ($ob->cpt === "2") {
                    throw new \Exception("Error Processing Request with Extract value found * 2 : ($val)", 1);
                }
            }
        }
    }
}