<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Extraction
{

    use \App\Library\Filter;
    static $db;
    static $variable   = array();
    static $server     = array();
    static $groupbyday = false;

    static public function setDb($db)
    {
        self::$db = $db;
    }

    static public function extract($var = array(), $server = array(), $date = "", $range = false, $graph = false, $order_by = '')
    {
        /*
          debug($var);
          debug($server);
          debug($date);
         */
        if (empty($server)) {
            $server = self::getServerList();
        }

        $extra_where = "";
        $INNER       = "";
        if (empty($date)) {

            $INNER = " INNER JOIN ts_max_date b ON a.id_mysql_server = b.id_mysql_server AND a.date = b.date ";

            //$extra_where = " AND a.`date` > date_sub(now(), INTERVAL 1 DAY) ";
        } else {


            if (is_array($date)) {
                if ($range) {
                    $date_min = $date[0];
                    $date_max = $date[1];

                    $extra_where = " AND a.`date` BETWEEN '".$date_min."' AND '".$date_max."' ";
                } else {
                    $extra_where = " AND a.`date` IN ('".implode("','", $date)."') ";
                }
            } else {
                $extra_where = " AND a.`date` > date_sub(now(), INTERVAL $date) ";
            }
        }


        $variable = self::getIdVariable($var);



        /*
          if (count($var) != self::count_recursive($variable)) {

          //echo from(__FILE__);
          //debug(self::count_recursive($variable));
          //debug($var);
          //debug($variable);


          throw new \Exception('PMACTRL-058 : The number of row is not the same please check you data '.count($var).' != '.self::count_recursive($variable).' :'.json_encode($var));
          } */

        $sql2 = array();



        foreach ($variable as $radical => $data_type) {
            foreach ($data_type as $type => $tab_ids) {


                //debug($radical);




                if ($radical == "slave") {
                    $fields = " a.`id_mysql_server`, a.`id_ts_variable`, a.`connection_name`,a.`date`,a.`value` ";
                } else {
                    $fields = " a.`id_mysql_server`, a.`id_ts_variable`, '' as connection_name,a.`date`,a.`value` ";
                }




                $sql4 = "(SELECT ".$fields."   FROM `ts_value_".$radical."_".$type."` a "
                    .$INNER."
                WHERE id_ts_variable IN (".implode(",", $tab_ids).")
                    AND a.id_mysql_server IN (".implode(",", $server).") $extra_where)";




                $sql2[] = $sql4;
            }
        }

        $sql3 = implode(" UNION ALL ", $sql2);

        if ($graph === true) {
            $sql3 .= " ORDER BY id_mysql_server, id_ts_variable, date";
        } else {
            //$sql3 .= "ORDER by date";
        }


        if ($graph === true) {

            $sql3 = "WITH t as ($sql3)
                SELECT t.id_mysql_server,
                id_ts_variable,";

            if (self::$groupbyday) {
                $sql3 .= " date(t.`date`) as day, ";
            }


            $sql3 .= "
                connection_name,
                group_concat(concat('{ x: new Date(\'',t.`date`, '\'), y: ',t.`value`,'}') ORDER BY t.`date` ASC) as graph,
                min(t.`value`) as `min`,
                max(t.`value`) as `max`,
                avg(t.`value`) as `avg`,
                std(t.`value`) as `std`
            FROM t GROUP BY id_mysql_server ";

            if (self::$groupbyday) {
                $sql3 .= " ,date(t.`date`) ";
            } else {
                $sql3 .= " order by `std` desc;";
            }
        }



        //echo \SqlFormatter::format($sql3)."\n";

        $res2 = self::$db->sql_query($sql3);

        return $res2;
    }

    static private function getServerList()
    {
        $sql = "SELECT id FROM mysql_server a WHERE 1=1 ".self::getFilter();

        //debug($sql);
        $res = self::$db->sql_query($sql);

        $server = array();
        while ($ob     = self::$db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server[]       = $ob['id'];
            self::$server[] = $ob;
        }

        if (count($server) === 0) {//int negatif pour être sur de rien remonté
            $server[] = "-999";
        }

        return $server;
    }

    static public function display($var = array(), $server = array(), $date = "", $range = false, $graph = false)
    {
        $res = self::extract($var, $server, $date, $range, $graph);


        $table = array();
        while ($ob    = self::$db->sql_fetch_object($res)) {

            //debug(self::$variable[$ob->id_ts_variable]);

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
        $sqls = array();
        foreach ($var as $val) {
            $split = explode("::", $val);

            if (count($split) === 2) {

                $name = $split[1];
                $from = $split[0];

                if (empty($name)) {
                    $sqls[] = "(SELECT * FROM ts_variable where `from` = '".strtolower($from)."')";
                } else {

                    $sqls[] = "(SELECT * FROM ts_variable where `name` = '".strtolower($name)."' AND `from` = '".strtolower($from)."')";
                }
            } else {

                $name   = $split[0];
                $sqls[] = "(SELECT * FROM ts_variable where `name` = '".strtolower($name)."')";
            }
        }

        $sql = implode(' UNION ALL ', $sqls);

        $res = self::$db->sql_query($sql);

        //echo \SqlFormatter::format($sql)."\n";
        $from     = array();
        $variable = array();
        while ($ob       = self::$db->sql_fetch_object($res)) {
            self::$variable[$ob->id]['name']                 = $ob->name;
            $variable[$ob->radical][strtolower($ob->type)][] = $ob->id;
            //$radical                              = $ob->radical;
        }


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

    static public function graph($var, $server, $range)
    {
        $res   = self::extract($var, $server, $date  = "", $range, true);
        $graph = array();
        while ($ar    = self::$db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $graph[$ar['id_mysql_server']] = $ar;
        }

        return $graph;
    }

    static public function setOption($var, $val)
    {
        self::$$var = $val;
    }
}