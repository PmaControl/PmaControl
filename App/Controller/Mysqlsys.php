<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use App\Library\Mysql;
use App\Library\Debug;


class Mysqlsys extends Controller {

    use \App\Library\Filter;

    public function index() {

        $this->title = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> ' . "MySQL-sys";

        $db = Sgbd::sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['mysql_server']['id'])) {

                $sql = "SELECT * FROM mysql_server where id='" . $_POST['mysql_server']['id'] . "'";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    $id_mysql_server = $ob->id;
                    $url = LINK . strtolower($this->getClass()) . '/index/mysql_server:id:' . $id_mysql_server;
                    header('location: ' . $url);
                }
            }
        } else {

            $data = [];

            // get server available
            $available = Common::getAvailable();
            $case = $available['case'];

            $sql = "SELECT *,".$case." FROM mysql_server a WHERE 1=1 " . self::getFilter() . " order by a.name ASC";

            $res = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob = $db->sql_fetch_object($res)) {
                $tmp = [];
                $tmp['id'] = $ob->id;
                $tmp['libelle'] = $ob->name . " (" . $ob->ip . ")";
                $data['servers'][] = $tmp;

                if (!empty($_GET['mysql_server']['id']) && $ob->id == $_GET['mysql_server']['id']) {
                    $link_name = $ob->name;
                }
            }

            //Debug::debug($link_name);

            if (!empty($link_name)) {

                $remote = Sgbd::sql($link_name);
                $sql = "select TABLE_NAME from information_schema.tables "
                        . "WHERE table_schema = 'sys' and table_name not like 'x$%' ORDER BY table_name ASC;";
                $res = $remote->sql_query($sql);
                $data['view_available'] = [];
                while ($ob = $remote->sql_fetch_object($res)) {
                    //$data['view_available'][] = str_replace('x$','',$ob->table_name);
                    $data['view_available'][] = $ob->TABLE_NAME;
                }

                //test if InnoDB activated
                $sql = "select * from information_schema.engines where engine = 'InnoDB';";
                $res = $remote->sql_query($sql);

                $data['innodb'] = 0;
                while ($ob = $remote->sql_fetch_object($res)) {
                    if ($ob->SUPPORT == "YES" || $ob->SUPPORT == "DEFAULT") {
                        $data['innodb'] = 1;
                    }
                }

                //test if spider / rocksdb etc...
                if (!empty($_GET['mysqlsys']) && in_array($_GET['mysqlsys'], $data['view_available'])) {

                //patch
                //$sql = "UPDATE sys.sys_config SET value = '100000' where variable ='statement_truncate_len';";
                //$remote->sql_query($sql);
                //fin patch
                    if ($remote->checkVersion(array('MariaDB'=> '10.1.1'))) {
                        $sql = "SET STATEMENT MAX_STATEMENT_TIME = 10 FOR SELECT * FROM `sys`.`" . $_GET['mysqlsys'] . "` LIMIT 200";
                    }
                    else {
                        $sql = "SELECT * FROM `sys`.`" . $_GET['mysqlsys'] . "` LIMIT 200";
                    }
                    
                    $data['table'] = $remote->sql_fetch_yield($sql);
                    $data['name_table'] = $_GET['mysqlsys'];
                }

                $data['variables'] = $remote->getVersion();
            }
        }
        $this->set('data', $data);
    }


    public function install() {
        $this->title = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> ' . "MySQL-sys";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > ' . $this->title . ' > <i style="font-size: 16px" class="fa fa-upload"></i> Install';

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id='" . $_GET['mysql_server']['id'] . "'";
        $res = $db->sql_query($sql);

//test si "vendor/esysteme/mysql-sys/gen/" est crée et writable


        $data = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $cmd = 'cd ' . ROOT . '/vendor/esysteme/mysql-sys ';

            
            $cmd .= '&& ./generate_sql_file.sh -v 100 -u "\'' . $ob->login . '\'@\'localhost\'" 2>&1';
            $ret = shell_exec($cmd);

            $out = explode("\n", $ret)[1];
            $data['file_name'] = trim(str_replace('Wrote file:', '', $out));

            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                Crypt::$key = CRYPT_KEY;

                $cmd = "mysql -h " . $ob->ip . " -u " . $ob->login . " -P " . $ob->port . " -p'" . Crypt::decrypt($ob->passwd) . "' < " . $data['file_name'] . " 2>&1";
                $ret = shell_exec($cmd);

                if (!empty($ret)) {

                    header('location: ' . LINK . 'mysqlsys/install/mysql_server:id:' . $_GET['mysql_server']['id'] . '/error_msg:' . base64_encode($ret) . '/');
                } else {
                    header('location: ' . LINK . 'mysqlsys/index/mysql_server:id:' . $_GET['mysql_server']['id']);
                }
            } else {
                $data['file'] = file_get_contents($data['file_name']);
            }
        }

        $this->set('data', $data);
    }

    public function addFormat($tab) {
        foreach ($tab as $key => $elem) {
            if ($key == "value") {
                yield "gg";
            }
        }
    }

    public function reset($param) {

        $this->view = false;
        $this->layout_name = false;


        $id_mysql_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);


// get server available

        $available = Common::getAvailable();
        $case = $available['case'];

        $sql = "SELECT name, ".$case." FROM mysql_server a WHERE 1=1 " . self::getFilter() . " AND id=" . $id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $remote = Sgbd::sql($ob->name);

            $sql = "set sql_log_bin=0; call `sys`.ps_truncate_all_tables(false);";
            $remote->sql_multi_query($sql);
        }

        //$msg = I18n::getTranslation(__("The statistics has been reseted"));
        //$title = I18n::getTranslation(__("Success"));
        //set_flash("success", $title, $msg);

        header("location: " . $_SERVER['HTTP_REFERER']);
    }

    public function drop($param) {

        $this->view = false;
        $this->layout_name = false;


        $id_mysql_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);


// get server available
        $sql = "SELECT name FROM mysql_server a WHERE 1=1 " . self::getFilter() . " AND id=" . $id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $remote = Sgbd::sql($ob->name);

            $sql = "set sql_log_bin=0; DROP DATABASE IF EXISTS `sys`;";
            $remote->sql_multi_query($sql);
        }

        $msg = I18n::getTranslation(__("MySQL-sys has been uninstalled"));
        $title = I18n::getTranslation(__("Success"));
        set_flash("success", $title, $msg);

        header("location: " . $_SERVER['HTTP_REFERER']);
    }

    public function updateConfig($param) {
        $this->view = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            
            $db = Mysql::getDbLink($_POST['pk']);
            
            $sql = "UPDATE sys.sys_config SET `value` = '" . $_POST['value'] . "' 
            WHERE `variable` = '" . $db->sql_real_escape_string($_POST['name']) . "'";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }


    public function export($param)
    {
        //$this->view = true;
        $this->layout_name = false;

        $_GET['ajax'] = true;
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $rapport = $param[1];

        $db = Mysql::getDbLink($id_mysql_server, "mysqlsys");

        $def = Sgbd::sql(DB_DEFAULT);
        $sql2 = "SELECT * FROM mysqlsys_config_export WHERE rapport ='".$rapport."'";
        $res2 = $def->sql_query($sql2);
        
        $select = '*';
        $limit = 2000;
        while ($arr = $def->sql_fetch_array($res2, MYSQLI_ASSOC))
        {
            $select = $arr['select'];
            $limit = $arr['limit'];
        }




        switch ($rapport)
        {
            case 'schema_auto_increment_columns':
                $sql = "SELECT ".$select." FROM `sys`.`".$rapport."` WHERE auto_increment_ratio > 0.5 LIMIT ".$limit;
                break;


            case 'engines':
                $sql ="SELECT 
  ENGINE as Engine, 
  group_concat(distinct table_schema) AS `Database`, 
  ROUND( SUM(INDEX_LENGTH) / 1024 / 1024 / 1024, 2 ) AS `Index (Go)`, 
  ROUND( SUM(DATA_LENGTH) / 1024 / 1024 / 1024, 2 ) AS `data (Go)`, 
  ROUND( SUM(DATA_FREE) / 1024 / 1024 / 1024, 2 ) AS `Free (Go)`, 
  COUNT(*) AS `Tables`, 
  SUM(TABLE_ROWS) as `Rows (sum)`
FROM 
  information_schema.tables 
WHERE 
  TABLE_SCHEMA NOT IN (
    'mysql', 'performance_schema', 'information_schema', 
    'sys'
  ) 
  AND TABLE_TYPE NOT IN ('SYSTEM VIEW', 'VIEW') 
GROUP BY 
  ENGINE;";
                break;


            case 'top10tables':

                $sql = "SELECT
  t.TABLE_SCHEMA AS 'Schéma',
  t.TABLE_NAME AS 'Table',
  ROUND(t.DATA_LENGTH / 1024 / 1024 / 1024, 2) AS 'Données (Go)',
  ROUND(t.INDEX_LENGTH / 1024 / 1024 / 1024, 2) AS 'Index (Go)',
  ROUND(t.DATA_FREE / 1024 / 1024 / 1024, 2) AS 'Libre (Go)',
  t.TABLE_ROWS AS 'Nb lignes',
  t.ENGINE AS 'Moteur',
  COUNT(s.INDEX_NAME) AS 'Nb index'
FROM
  information_schema.TABLES t
LEFT JOIN
  information_schema.STATISTICS s
  ON t.TABLE_SCHEMA = s.TABLE_SCHEMA AND t.TABLE_NAME = s.TABLE_NAME
GROUP BY
  t.TABLE_SCHEMA, t.TABLE_NAME
ORDER BY
  (t.DATA_LENGTH + t.INDEX_LENGTH) DESC
LIMIT 10;";

                break;

            case "alter_table_redundant_index":

                break;
            

            default:
                $sql = "SELECT ".$select." FROM `sys`.`".$rapport."` LIMIT ".$limit;

            break;
        }


        
        $res = $db->sql_query($sql);

        $data['export'] = array();
        while ($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            $data['export'][] = $arr;
        }

        //Debug::debug($data['export']);

        $this->set('data', $data);/***/

    }




}
