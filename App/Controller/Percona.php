<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \App\Library\Debug;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

class Percona extends Controller
{
    var $mysql_server = array();

    public function execQuery($param)
    {
        Debug::parseDebug($param);

        $sql = $param[0];

        if (!empty($param[1])) {
            $id_mysql_server = $param[1];
        }


        $data          = array();
        $mysql_servers = $this->getServeAvailable(array());

        foreach ($mysql_servers as $id_mysql_server) {

            $link = Mysql::getDbLink($id_mysql_server);

            Debug::sql($sql);

            $res = $link->sql_query($sql);

            while ($arr = $link->sql_fetch_array($res, MYSQLI_ASSOC)) {

                $tmp                    = $arr;
                $tmp['id_mysql_server'] = $id_mysql_server;
                $data[]                 = $tmp;
            }
        }

        Debug::debug($data);

        return $data;
    }

// to export somewhere
    public function getServeAvailable($param)
    {
        Debug::parseDebug($param);

        if (count($this->mysql_server) === 0) {



            $db  = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT id FROM `mysql_server` WHERE is_monitored=1 AND error='' AND is_available=1";
//AND TIMESTAMPDIFF(SECOND, date_refresh,now) > 10
// see why date_refresh is no more refreshed

            Debug::sql($sql);

            $res = $db->sql_query($sql);

            $data = array();
            while ($ob   = $db->sql_fetch_object($res)) {
                $data[] = $ob->id;
            }

            Debug::debug($data, "id_mysql_server");

            $this->mysql_server = $data;
        }
        return $this->mysql_server;
    }

    public function ptOsc($param)
    {

    }

    public function updateOsc($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "select table_schema as table_schema, table_name as table_name, table_rows as table_rows, DATA_LENGTH as data_length,INDEX_LENGTH as index_length,DATA_FREE as data_free, CREATE_TIME as create_time   "
            ."FROM information_schema.tables where table_name like '__old%'"
            ." UNION ALL "
            ."select table_schema as table_schema, table_name as table_name, table_rows as table_rows, DATA_LENGTH as data_length,INDEX_LENGTH as index_lengyh,DATA_FREE as data_free, CREATE_TIME as create_time  "
            ."FROM information_schema.tables where table_name like '__new%';";

        $tables = $this->execQuery(array($sql));

        foreach ($tables as $table) {


            //$sql2= "DELETE FROM "



            $to_save                      = array();
            $to_save['percona_osc_table'] = $table;

            Debug::debug($to_save);

            $res = $db->sql_save($to_save);

            if (!$res) {
                Debug::debug($db->sql_error(), "Impossible to save");
            }



            //$data['trigger'] = $this->execQuery(array("select table_schema, table_name FROM information_schema.tables where table_name like '__new%';"));
        }
    }

    function delOscTable($param)
    {
        Debug::parseDebug($param);

        $id_percona_osc_table = $param[0];
    }
}