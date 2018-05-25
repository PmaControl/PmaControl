<?php

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;
use \Glial\Net\Ssh;
use \Glial\Security\Crypt\Crypt;

class Common extends Controller {

    use \App\Library\Filter;

    //dba_source

    public function index() {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_database";
    }

    /*
      @author: Aurélien LEQUOY
      Obtenir la liste dans un select des server MySQL operationels
     */

    public function displayClientEnvironment($param) {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $db = $this->di['db']->sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            if (!empty($_POST['client_environment'])) {
                $ret = "";
                if (!empty($_POST['client']['libelle']) || !empty($_POST['environment']['libelle'])) {

                    /* header("location: ".LINK."".\Glial\Synapse\FactoryController::$controller."/".\Glial\Synapse\FactoryController::$method."/client:libelle:"
                      .$_POST['client']['libelle']."/environment:libelle:".$_POST['environment']['libelle']); */


                    if (!empty($_POST['client']['libelle'])) {
                        $_SESSION['client']['libelle'] = json_encode($_POST['client']['libelle']);
                        $ret .= "/client:libelle:" . json_encode($_POST['client']['libelle']);
                    } else {
                        unset($_SESSION['client']['libelle']);
                    }

                    if (!empty($_POST['environment']['libelle'])) {
                        $_SESSION['environment']['libelle'] = json_encode($_POST['environment']['libelle']);
                        $ret .= "/environment:libelle:" . json_encode($_POST['environment']['libelle']);
                    } else {
                        unset($_SESSION['environment']['libelle']);
                    }
                } elseif (!empty($_POST['client_environment'])) {
                    unset($_SESSION['client']['libelle']);
                    unset($_SESSION['environment']['libelle']);
                }

                header("location: " . LINK . "" . $this->remove(array("client:libelle", "environment:libelle")) . $ret);
            }
        }

        if (empty($_GET['environment']['libelle']) && !empty($_SESSION['environment']['libelle'])) {
            $_GET['environment']['libelle'] = $_SESSION['environment']['libelle'];
        }

        if (empty($_GET['client']['libelle']) && !empty($_SESSION['client']['libelle'])) {
            $_GET['client']['libelle'] = $_SESSION['client']['libelle'];
        }


        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);


        $data['client'] = array();

        /*
          $tmp = [];
          $tmp['id'] = "";
          $tmp['libelle'] = __("All");
          $data['environment'][] = $tmp;
         */

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);


        $data['environment'] = array();

        /*
          $tmp = [];
          $tmp['id'] = "";
          $tmp['libelle'] = __("All");
          $data['environment'][] = $tmp;
         */


        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public function remove($array) {

        $params = explode("/", $_GET['url']);
        foreach ($params as $key => $param) {
            foreach ($array as $var) {
                if (strstr($param, $var)) {
                    unset($params[$key]);
                }
            }
        }

        $ret = implode('/', $params);

        $ret = trim($ret, "/");

        return $ret;
    }

    function getDatabaseByServer($param) {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];

        $db_to_get_db = $this->getDbLinkFromId($id_mysql_server);

        $sql = "SHOW DATABASES";
        $res2 = $db_to_get_db->sql_query($sql);

        $data['databases'] = [];
        while ($ob = $db_to_get_db->sql_fetch_object($res2)) {
            $tmp = [];
            $tmp['id'] = $ob->Database;
            $tmp['libelle'] = $ob->Database;
            $data['databases'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }

    private function getDbLinkFromId($id_db) {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($id_db) . "';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_link = $this->di['db']->sql($ob->name);
        }

        return $db_link;
    }

    function getTableByServerAndDatabase($param) {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];
        $database = $param[1];

        $db_to_get_db = $this->getDbLinkFromId($id_mysql_server);

        $sql = "use " . $database . ";";
        $db_to_get_db->sql_query($sql);

        $tables = $db_to_get_db->getListTable();


        $data['tables'] = [];
        foreach ($tables['table'] as $table) {
            $tmp = [];
            $tmp['id'] = $table;
            $tmp['libelle'] = $table;
            $data['tables'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }

}
