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

                    $sql = "SELECT * FROM `sys`.`" . $_GET['mysqlsys'] . "` LIMIT 2000";
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
        $sql = "SELECT name FROM mysql_server a WHERE error = '' " . self::getFilter() . " AND id=" . $id_mysql_server;
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
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $rapport = $param[1];

        $db = Mysql::getDbLink($id_mysql_server, "mysqlsys");


        $def = Sgbd::sql(DB_DEFAULT);
        $sql2 = "SELECT * FROM mysqlsys_config_export WHERE rapport ='".$rapport."'";
        $res2 = $def->sql_query($sql2);
        
        $select = '*';
        $limit =50;
        while ($arr = $def->sql_fetch_array($res2, MYSQLI_ASSOC))
        {
            $select = $arr['select'];
            $limit = $arr['limit'];
        }




        $sql = "SELECT ".$select." FROM `sys`.`".$rapport."` LIMIT ".$limit;
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
