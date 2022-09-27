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

    const MAX_SIZE_TABLE = 10737418240; //10G

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
            $sql = "SELECT id FROM `mysql_server` WHERE is_monitored=1 AND error='' AND is_available=1 AND is_proxy=0";
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

    function delOldOscTable($param)
    {
        $this->view = false;

        Debug::parseDebug($param);

        $id_percona_osc_table = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `percona_osc_table` WHERE `id`=".$id_percona_osc_table.";";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob, "out");

            $link = Mysql::getDbLink($ob->id_mysql_server);

            $sql3 = "SET sql_log_bin=0;";
            $link->sql_query($sql3);
            Debug::debug($sql3);

            $sql2 = "DROP TABLE `".$ob->table_schema."`.`".$ob->table_name."`; ";
            Debug::debug($sql2);
            $link->sql_query($sql2);

            $sql4 = "DELETE FROM `percona_osc_table` WHERE `id`=".$id_percona_osc_table.";";
            Debug::debug($sql4);
            $db->sql_query($sql4);
        }


        if (!IS_CLI) {
            header('location: '.LINK.'percona/displayOsc');
        }
    }

    function displayOsc($param)
    {
        Debug::parseDebug($param);

        //$this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT b.*, a.id as id_mysql_server, a.display_name, c.libelle, c.class, DATEDIFF(now(), b.create_time) as days
            FROM mysql_server a
            INNER JOIN percona_osc_table b ON a.id = b.id_mysql_server
            INNER JOIN environment c ON a.id_environment = c.id
            WHERE a.is_proxy=0 AND b.table_name LIKE '__old%'
            ORDER BY  b.create_time,c.id, a.id,b.table_schema, b.table_schema";

        $res = $db->sql_query($sql);

        $data['ptosc_old'] = array();
        while ($arr               = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['ptosc_old'][] = $arr;
        }


        $sql = "SELECT b.*, a.id as id_mysql_server, a.display_name, c.libelle, c.class, DATEDIFF(now(), b.create_time) as days
            FROM mysql_server a
            INNER JOIN percona_osc_table b ON a.id = b.id_mysql_server
            INNER JOIN environment c ON a.id_environment = c.id
            WHERE a.is_proxy=0 AND b.table_name LIKE '__new%'
            ORDER BY  b.create_time,c.id, a.id,b.table_schema, b.table_schema";

        $res = $db->sql_query($sql);

        $data['ptosc_new'] = array();
        while ($arr               = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['ptosc_new'][] = $arr;
        }



        $this->set('data', $data);
    }

    function delAllOldOscTable($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id FROM percona_osc_table a"
            ." INNER JOIN mysql_server b ON a.id_mysql_server = b.id"
            ." INNER JOIN environment c ON c.id = b.id_environment"
            ." WHERE a.table_name LIKE '__old%' "
            ."AND c.libelle != 'Production'"
            ." AND (a.data_length+a.index_length + a.data_free) < ".self::MAX_SIZE_TABLE.";";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob, "out");

            $this->delOldOscTable(array($ob->id));
        }
    }
}