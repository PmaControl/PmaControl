<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;

class Spider extends Controller {

    //dba_source


    use \App\Library\Filter;

    public function index() {
        $this->title = '<img src="/pmacontrol/image/main/spider-icon32.png" height="16" width="16px">' . "Spider";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > ' . $this->title;

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_database";
    }

    /*
     * add restriction for MySQL & MariaDB < 5.5 (Spider not supported)
     */

    public function Server($param) {

        $id_mysql_server = $param[0];

        $db = $this->getServerLink($id_mysql_server);
        $sql = "SELECT TABLE_NAME,TABLE_SCHEMA FROM information_schema.TABLES where ENGINE = 'Spider';";

        $res = $db->sql_query($sql);


        // test if a table with spider exit
        $data['no_spider'] = 0;
        if ($db->sql_num_rows($res) == 0) {
            $data['no_spider'] = 1;
        }

        if ($data['no_spider'] === 0) {
            while ($ob = $db->sql_fetch_object($res)) {
                $sql = "SHOW CREATE TABLE `" . $ob->TABLE_SCHEMA . "`.`" . $ob->TABLE_NAME . "`;";
                $res2 = $db->sql_query($sql);
                $tab = $db->sql_fetch_array($res2, MYSQLI_ASSOC);

                $data['spider']['server_id'][$ob->TABLE_SCHEMA][$ob->TABLE_NAME] = $this->extractSpiderInfoFromCreateTable($tab['Create Table']);
            }
        }
        $this->set('data', $data);
    }

    /*
      Test if engine Spider exit on MySQL server else give solution to install it
     */

    public function testIfSpiderExist($param) {
        $id_mysql_server = $param[0];
        $db = $this->getServerLink($id_mysql_server);
        $sql = 'SELECT count(1) as cpt FROM information_schema.engines where engine = "spider";';

        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $cpt = $ob->cpt;
        }

        $data['spider_activated'] = 0;
        
        if ($cpt === "1") {
            $data['spider_activated'] = 1;
        }

        $this->set('data', $data);
    }

    /*
      return MySQL link from id_mysql_server
     */

    private function getServerLink($id_mysql_server) {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server where id = '" . $id_mysql_server . "'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $name_id = $ob->name;
        }
        return $this->di['db']->sql($name_id);
    }

    private function extractSpiderInfoFromCreateTable($createTable) {
        $comment = stristr($createTable, 'COMMENT=');
        $main = substr($comment, 8, 1);
        $tmp = [];
        preg_match("@COMMENT=\\" . $main . "(.*)\\" . $main . "@", $comment, $output_array);
        $resultats = $output_array[1];
        $results = explode(',', $resultats);

        foreach ($results as $result) {
            $result = trim($result);
            $elem = explode(' ', $result);

            $tmp['connection'][$elem[0]] = substr($elem[1], 1, -1);
        }

        return $tmp;
    }

    public function create() {

        $db = $this->di['db']->sql(DB_DEFAULT);


        $this->di['js']->addJavascript(array("jquery-latest.min.js", "bootstrap-select.min.js", "spider/create.js"));


        $data['databases'] = array();
        if (!empty($_GET['spider']['database'])) {
            $select1 = $this->getDatabaseByServer(array($_GET['mysql_server']['id']));
            $data['databases'] = $select1['databases'];
        }



        $data['tables'] = array();
        if (!empty($_GET['spider']['tables'])) {
            $select1 = $this->getTableByServerAndDatabase(array($_GET['mysql_server']['id'], $_GET['spider']['database'],));
            $data['tables'] = $select1['tables'];
        }



        $this->set('data', $data);
    }

    public function addLinkDb($param) {
        $id_mysql_source = $param[0];
        $database_source = $param[1];
        $id_mysql_destination = $param[2];
        $database_destination = $param[3];
        
        
        
        
    }

}
