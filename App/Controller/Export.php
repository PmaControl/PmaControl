<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Chiffrement;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Mysql;
use \App\Library\Json;
use \Glial\Sgbd\Sgbd;

class Export extends Controller
{
    var $table_with_data        = array("translation_main", "geolocalisation_city",
        "geolocalisation_continent", "geolocalisation_country");
    var $table_with_data_expand = array("menu", "menu_group", "history_etat", "ts_file",
        "group", "environment", "daemon_main", "version", "sharding", "ts_variable", "dot3_legend",
        "home_box", "backup_type", "export_option", "database_size", "mysql_type", "translation_google", "translation_glial", "benchmark_config");
    var $exlude_table = array("translation_*", "slave_*", "master_*", "variables_*", "status_*", "ts_value_*", "ts_date_by_server");

    function generateDump($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);

        $tables = $db->getListTable()['table'];

        $table_with_data        = array();
        $table_with_data_expand = array();
        $table_without_data     = array();

        foreach ($tables as $key => $table) {
            if (in_array($table, $this->table_with_data)) {
                $table_with_data[] = $table;
                unset($tables[$key]);
            }
        }

        foreach ($tables as $key => $table) {
            if (in_array($table, $this->table_with_data_expand)) {
                $table_with_data_expand[] = $table;
                unset($tables[$key]);
            }
        }


        $to_remove = array();

        foreach ($tables as $key => $table) {

            foreach ($this->exlude_table as $table_to_exclude) {
                if (fnmatch($table_to_exclude, $table)) {
                    $to_remove[] = $table;
                }
            }
        }

        $table_without_data = array_diff($tables, $to_remove);

        Debug::debug($table_with_data);
        Debug::debug($table_with_data_expand);
        Debug::debug($table_without_data);

        $connect = $db->getParams();

        if (empty($connect['port'])) {
            $connect['port'] = 3306;
        }

// a remplacer par une implementation full PHP

        $cmd = "mysqldump --skip-dump-date -h ".$connect['hostname']
            ." -u ".$connect['user']
            ." -P ".$connect['port']
            ." -p'".Chiffrement::decrypt($connect['password'])
            ."' ".$connect['database']." ".implode(" ", $table_with_data)." | sed 's/ AUTO_INCREMENT=[0-9]*\b//' > ".ROOT."/sql/full/pmacontrol.sql 2>&1";
        shell_exec($cmd);

        $cmd = "mysqldump --skip-dump-date -h ".$connect['hostname']
            ." -u ".$connect['user']
            ." -P ".$connect['port']
            ." --skip-extended-insert"
            ." -p'".Chiffrement::decrypt($connect['password'])
            ."' ".$connect['database']." ".implode(" ", $table_with_data_expand)." | sed 's/ AUTO_INCREMENT=[0-9]*\b//' >> ".ROOT."/sql/full/pmacontrol.sql 2>&1";
        shell_exec($cmd);

        $cmd = "mysqldump --skip-dump-date -h ".$connect['hostname']
            ." -u ".$connect['user']
            ." -P ".$connect['port']
            ." -d "
            ." -p'".Chiffrement::decrypt($connect['password'])
            ."' ".$connect['database']." ".implode(" ", $table_without_data)." | sed 's/ AUTO_INCREMENT=[0-9]*\b//' >> ".ROOT."/sql/full/pmacontrol.sql 2>&1";
        shell_exec($cmd);
    }

    function index()
    {

        $this->title = '<span class="glyphicon glyphicon-import"></span> '.__("Import / Export");

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->code_javascript('
$("#export_all-all").click(function(){
    $(".form1 input:checkbox").not(this).prop("checked", this.checked);
});
$("#export_all-all2").click(function(){
    $(".form2 input:checkbox").not(this).prop("checked", this.checked);
});
');

        $sql = "SELECT * FROM `export_option` where active =1";

        $res = $db->sql_query($sql);

        $data['options'] = array();
        while ($arr             = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['options'][] = $arr;
        }

        $this->set('data', $data);
    }

    public function export_conf()
    {

        $this->view = false;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {


            $backup = $this->_export();

            $json = json_encode($backup);

            //file_put_contents("/tmp/json", $json);
            //$compressed = gzcompress($json, 9);


            Debug::debug($json, "JSON");

            $crypted = Chiffrement::encrypt($json, $_POST['export']['password']);

            //$file_name = $_POST['export']['name_file'];
            $file_name = "export_".date('Y-m-d').".pmactrl";

            file_put_contents("/tmp/export", $crypted);

            header("Content-Disposition: attachment; filename=\"".$file_name."\"");
            header("Content-Length: ".filesize("/tmp/export"));
            header("Content-Type: application/octet-stream;");
            readfile("/tmp/export");
        }
    }

    public function import_conf($param)
    {
        $this->view = false;

        Debug::parseDebug($param);
        //Debug::$debug = true;


        $data  = array();
        $error = false;

        if (IS_CLI) {

            $file     = $param[0];
            $password = $param[1];

            Debug::debug($file, "file");
        } else {

            if ($_SERVER['REQUEST_METHOD'] === "POST") {

                if (!empty($_FILES['export']['tmp_name']['file'])) {
                    $file     = $_FILES['export']['tmp_name']['file'];
                    $password = $_POST['export']['password'];
                }

                if (empty($file)) {
                    $error = true;
                    set_flash("error", __('Error'), __("Please select the config file"));
                }

                if (empty($password)) {
                    $error = true;
                    set_flash("error", __('Error'), __("Please request the password to uncrypt file"));
                }

                if ($error == true) {
                    header("location: ".LINK.$this->getClass()."/index");
                    exit;
                }
            }
        }


        if (!empty($file) && !empty($password)) {

            $crypted = file_get_contents($file);
            $json    = Chiffrement::decrypt($crypted, $password);
            Debug::debug($json, "json");
            $data    = $this->import(array($json));

            Debug::debug($data);
        }

        $json = Chiffrement::decrypt($crypted, $password);

        Debug::debug(json_encode(json_decode($json), JSON_PRETTY_PRINT), "json");

        $data = $this->import(array($json));

        if (!empty($data['mysql']['updated'])) {
            $msg = implode(", ", $data['mysql']['updated']);
            set_flash("success", __('Server updated'), $msg);
        }


        if (!IS_CLI) {

            if (!empty($data['mysql']['updated'])) {
                $msg = implode(", ", $data['mysql']['updated']);
                set_flash("success", __('Server updated'), $msg);
            }

            if (!empty($data['mysql']['inserted'])) {
                $msg = implode(", ", $data['mysql']['inserted']);
                set_flash("success", __('Server inserted'), $msg);
            }

            if (!empty($data['error'])) {
                $msg = "<ul><li>".implode("</li><li> ", $data['error'])."</li></ul>";
                set_flash("error", __('Error'), $msg);
            }

            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }

        //debug($data);

        $this->set('data', $data);
    }

    public function import($param)
    {
        //$param[] = "--debug";
        //debug($param);

        Debug::debug(DB_DEFAULT, "DB");

        Crypt::$key = CRYPT_KEY;
        $db         = Sgbd::sql(DB_DEFAULT);
        Debug::parseDebug($param);
        $json       = $param[0];
        //debug(json_decode($json,JSON_PRETTY_PRINT));

        $data = Json::isJson($json);

        foreach ($data as $server_type => $servers) {
            foreach ($servers as $server) {
                switch ($server_type) {
                    case 'mysql':

                        Mysql::addMysqlServer($server);
                        break;
                }
            }
        }
    }


    /*
    private function addMysql($arr)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        debug($arr);

        foreach ($arr['arr']['mysql'] as $mysql) {

            $option = $arr['options']['mysql'];

            unset($mysql['id']);

            $mysql['error'] = '';

            $data                                   = array();
            $data['mysql_server']                   = $mysql;
            $data['mysql_server']['id_client']      = 1;
            $data['mysql_server']['id_environment'] = 1;

            $data['mysql_server']['is_password_crypted'] = 1;

            foreach ($option['crypted_fields'] as $crypted_field) {
                $data['mysql_server'][$crypted_field] = Chiffrement::encrypt($data['mysql_server'][$crypted_field]);
            }

            unset($data['mysql_server']['key_private_path']);
            unset($data['mysql_server']['key_private_user']);

            $uniques = $this->getUniqueKey('mysql_server');

            Debug::debug($uniques);

            $sql2 = array();
            foreach ($uniques as $unique) {

                $keys = explode(",", $unique);

                $sql = array();
                foreach ($keys as $key) {
                    $sql[] = " `".$key."` = '".$data['mysql_server'][$key]."' ";
                }

                $sql2[] = "SELECT * FROM `mysql_server` WHERE ".implode(" AND ", $sql);
            }


            if (!empty($sql2)) {
                $sql_good = '( '.implode(") UNION (", $sql2).' )';

                Debug::debug($sql_good);

                $res10 = $db->sql_query($sql_good);
                $cpt   = $db->sql_num_rows($res10);

                if ($cpt !== 0) {
                    continue;
                }
            }


            if ($data['mysql_server']['name'] !== "pmacontrol") {
                $id_mysql_server = $db->sql_save($data);
            }


            if (!$id_mysql_server) {
                debug($data);
                debug($db->sql_error());
            } else {
                Mysql::addMaxDate(array($id_mysql_server));
            }
        }

        //$this->generateMySQLConfig();
        Mysql::onAddMysqlServer();
        Mysql::generateMySQLConfig($db);
    }*/


    /*
    public function generateMySQLConfig($param = '')
    {
        $this->view = false;

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server a ORDER BY id_client";
        $res = $db->sql_query($sql);

        $config = ';[name_of_connection] => will be acceded in framework with $this->di[\'db\']->sql(\'name_of_connection\')->method()
;driver => list of SGBD avaible {mysql, pgsql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas
';

        while ($ob = $db->sql_fetch_object($res)) {
            $string = "[".$ob->name."]\n";
            $string .= "driver=mysql\n";
            $string .= "hostname=".$ob->ip."\n";
            $string .= "port=".$ob->port."\n";
            $string .= "user=".$ob->login."\n";
            $string .= "password=".$ob->passwd."\n";
            $string .= "crypted=1\n";
            $string .= "database=".$ob->database."\n";

            $config .= $string."\n\n";

//Debug::debug($string);
        }

        file_put_contents(ROOT."/configuration/db.config.ini.php", $config);
    }
    */

    public function test_import($param)
    {
        $file = $param[0];
        $json = file_get_contents($file);
        $this->import($json);
    }

    private function _export($options = array())
    {
        $db     = Sgbd::sql(DB_DEFAULT);
        $sql1   = "SELECT * FROM `export_option` where active ='1'";
        $res1   = $db->sql_query($sql1);
        $backup = array();

        while ($ob = $db->sql_fetch_object($res1, MYSQLI_ASSOC)) {

            $tables  = explode(",", trim($ob->table_name));
            $crypted = explode(",", $ob->crypted_fields);
            $splited = explode(",", $ob->splited_fields);

            Debug::debug("--------------");
            Debug::debug($ob->config_file, "config file");

            if (!empty($tables)) {
                foreach ($tables as $table) {
                    if (empty($table)) {
                        continue;
                    }

                    Debug::debug($table, "table MySQL");

                    if (!empty($ob->sql)) {


                        $sql2 = str_replace('{$DB_DEFAULT}', DB_DEFAULT, $ob->sql);
                    } else {
                        $sql2 = "SELECT * FROM `".$table."`";
                    }


                    $res2 = $db->sql_query($sql2);

                    while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                        if (!empty($ob->crypted_fields)) {

                            $fields = explode(",", $ob->crypted_fields);

                            foreach ($fields as $field) {
                                $arr2[$field] = Chiffrement::decrypt($arr2[$field]);
                            }
                        }




                        if (!empty($ob->splited_fields)) {
                            foreach ($splited as $field_split) {


                                if (empty($arr2[$field_split])) {
                                    unset($arr2[$field_split]);
                                } else {

                                    $arr2[$field_split] = explode(",", $arr2[$field_split]);
                                }
                            }
                        }



                        $backup[$ob->key][] = $arr2;
                    }
                }
            } else if (!empty($ob->config_file)) {
//cas des fichiers de configurations (ldap etc...)


                Debug::debug($ob->config_file, "config file");
//Debug::debug($tables);

                $config = file_get_contents(CONFIG.$ob->config_file);
                preg_match_all("/define\(\"(\w+)\"\s*,\s*\"(.*)\"/", $config, $output_array);

                foreach ($output_array[1] as $key => $val) {
                    $backup[$ob->key][$val] = $output_array[2][$key];
                }
            } else {
// error
                Debug::debug($ob, "PROBLEME !!");
            }
        }


        return $backup;
    }

    public function test_export($param)
    {

        Debug::parseDebug($param);

        $backup = $this->_export();

        Debug::debug(json_encode($backup, JSON_PRETTY_PRINT));
    }

    public function getExportOption()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM export_option";

        $res = $db->sql_query($sql);

        $export_option = array();
        while ($arr           = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $arr['table_name']     = explode(",", trim($arr['table_name']));
            $arr['crypted_fields'] = explode(",", trim($arr['crypted_fields']));

            if (empty($arr['table_name'][0])) {
                $arr['table_name'] = array();
            }

            if (empty($arr['crypted_fields'][0])) {
                $arr['crypted_fields'] = array();
            }

            $export_option[$arr['key']] = $arr;
        }


        if (count($export_option) == 0) {
            throw new \Exception('PMACTRL-158 : The table export_option is empty !');
        }



        return $export_option;
    }

    private function getUniqueKey($table_name)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "select group_concat(COLUMN_NAME) as colonne from information_schema.KEY_COLUMN_USAGE where TABLE_schema = 'pmacontrol' and table_name ='".$table_name."' and POSITION_IN_UNIQUE_CONSTRAINT is null and CONSTRAINT_NAME != 'PRIMARY' group by CONSTRAINT_NAME;";

        $res = $db->sql_query($sql);

        $unique = array();
        while ($arr    = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $unique[] = $arr['colonne'];
        }

        return $unique;
    }

    public function encrypt()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id,passwd from mysql_server where id !=1";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $sql = "UPDATE mysql_server SET passwd = '".Chiffrement::encrypt($ob->passwd)."' WHERE id=".$ob->id;
            $db->sql_query($sql);
        }
    }

    public function option()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `export_option` order by `active` desc;";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $data['export_option'][] = $ob;
        }


        $this->set('data', $data);
    }

//a mettre dans une librairy


    public function test_dechiffrement($param)
    {
        if (IS_CLI) {

            $file     = $param[0];
            $password = $param[1];

            Debug::debug($file, "file");
        } else {

            if ($_SERVER['REQUEST_METHOD'] === "POST") {

                if (!empty($_FILES['export']['tmp_name']['file'])) {
                    $file     = $_FILES['export']['tmp_name']['file'];
                    $password = $_POST['export']['password'];
                }

                $error = false;

                if (empty($file)) {
                    $error = true;
                    set_flash("error", __('Error'), __("Please select the config file"));
                }

                if (empty($password)) {
                    $error = true;
                    set_flash("error", __('Error'), __("Please request the password to uncrypt file"));
                }

                if ($error == true) {
                    header("location: ".LINK.$this->getClass()."/".__FUNCTION__);
                    exit;
                }
            }
        }

        $data = array();

        if (!empty($file) && !empty($password)) {
            $crypted    = file_get_contents($file);
            $compressed = Chiffrement::decrypt($crypted, $password);

            if ($this->is_gzipped($compressed) === true) {

                $json         = gzuncompress($compressed);
                $data['json'] = $json;
            } else {


                set_flash("error", __('Error'), __("The password is not good"));
                header("location: ".LINK.$this->getClass()."/".__FUNCTION__);
                exit;
            }
            //false
        }

        $this->set('data', $data);
    }

    function is_gzipped($in)
    {

        if (mb_strpos($in, "\x1f"."\x8b"."\x08") === 0) {
            return true;
        } else if (@gzuncompress($in) !== false) {
            return true;
        } else if (@gzinflate($in) !== false) {
            return true;
        } else {
            return false;
        }
    }
}
/* $compressed   = gzcompress('Compresse moi', 9);
  $uncompressed = gzuncompress($compressed);
  echo $uncompressed; */
