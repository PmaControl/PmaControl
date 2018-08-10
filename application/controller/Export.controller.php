<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Debug;
use App\Library\Chiffrement;
use App\Library\Mysql;

class Export extends Controller
{
    var $table_with_data = array("menu", "menu_group", "translation_main", "geolocalisation_city",
        "geolocalisation_continent", "geolocalisation_country", "history_etat", "ts_file",
        "group", "environment", "daemon_main", "version", "sharding", "ts_variable", "architecture_legend", "home_box");
    var $exlude_table    = array("translation_*", "slave_*", "master_*", "variables_*", "status_*", "ts_value_*");

    function generateDump($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $tables = $db->getListTable()['table'];

        $table_with_data    = array();
        $table_without_data = array();

        foreach ($tables as $key => $table) {
            if (in_array($table, $this->table_with_data)) {
                $table_with_data[] = $table;
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
        Debug::debug($table_without_data);

        $connect    = $db->getParams();
        Crypt::$key = CRYPT_KEY;

        if (empty($connect['port'])) {
            $connect['port'] = 3306;
        }

// a remplacer par une implementation full PHP
        $cmd = "mysqldump --skip-dump-date -h ".$connect['hostname']
            ." -u ".$connect['user']
            ." -P ".$connect['port']
            ." -p'".Crypt::decrypt($connect['password'])
            ."' ".$connect['database']." ".implode(" ", $table_with_data)." | sed 's/ AUTO_INCREMENT=[0-9]*\b//' > ".ROOT."/sql/full/pmacontrol.sql 2>&1";


        shell_exec($cmd);

        $cmd = "mysqldump --skip-dump-date -h ".$connect['hostname']
            ." -u ".$connect['user']
            ." -P ".$connect['port']
            ." -d "
            ." -p'".Crypt::decrypt($connect['password'])
            ."' ".$connect['database']." ".implode(" ", $table_without_data)." | sed 's/ AUTO_INCREMENT=[0-9]*\b//' >> ".ROOT."/sql/full/pmacontrol.sql 2>&1";


        shell_exec($cmd);
    }

    function index()
    {

        $this->title = '<span class="glyphicon glyphicon-import"></span> '.__("Import / Export");

        $db = $this->di['db']->sql(DB_DEFAULT);



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

            file_put_contents("/tmp/json", $json);

            $compressed = gzcompress($json, 9);


            $crypted   = Crypt::encrypt($compressed, $_POST['export']['password']);
            $file_name = $_POST['export']['name_file'];

            file_put_contents("/tmp/export", $crypted);

            header("Content-Disposition: attachment; filename=\"".$file_name."\"");
            header("Content-Length: ".filesize("/tmp/export"));
            header("Content-Type: application/octet-stream;");

            readfile("/tmp/export");

//debug($backup);
        }
    }

    public function import_conf($param)
    {
        $this->view = false;


//debug($_FILES);
//debug($_POST);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            if (!empty($_FILES['export']['tmp_name']['file'])) {

                $crypted    = file_get_contents($_FILES['export']['tmp_name']['file']);
                $compressed = Crypt::decrypt($crypted, $_POST['export']['password']);

                $json = gzuncompress($compressed);

                $file = "/tmp/".uniqid();
                file_put_contents($file, $json);

                $this->import(array($file));
            }
        }
    }

    public function import($json)
    {
        $json[] = "--debug";



        Debug::parseDebug($json);
        //$file = $param[0];

        if (file_exists($json[0])) {

            $file = $json[0];
            $json[0] = file_get_contents($file);

            unlink($file);
        }


        $arr = json_decode($json[0], true);


        Debug::debug($arr);

        $db = $this->di['db']->sql(DB_DEFAULT);



        $options = $this->getExportOption();

        foreach ($arr['mysql'] as $mysql) {

            $option = $options['mysql'];

            unset($mysql['id']);

            $mysql['error'] = '';


            $data['mysql_server']                   = $mysql;
            $data['mysql_server']['id_client']      = 1;
            $data['mysql_server']['id_environment'] = 1;

            $data['mysql_server']['is_password_crypted'] = 1;

            foreach ($option['crypted_fields'] as $crypted_field) {
                $data['mysql_server'][$crypted_field] = Crypt::encrypt($data['mysql_server'][$crypted_field], CRYPT_KEY);
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

                $cpt = $db->sql_num_rows($res10);

                if ($cpt !== 0) {
                    continue;
                }
            }

            $res = $db->sql_save($data);

            if (!$res) {
                debug($data);
                debug($db->sql_error());
            }

            $this->generateMySQLConfig();

            Mysql::onAddMysqlServer($db);
        }
    }

    public function generateMySQLConfig($param = '')
    {
        $this->view = false;

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

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

    public function test_import($param)
    {
        $file = $param[0];
        $json = file_get_contents($file);
        $this->import($json);
    }

    private function _export($options = array())
    {
        $db     = $this->di['db']->sql(DB_DEFAULT);
        $sql1   = "SELECT * FROM `export_option` where active ='1'";
        $res1   = $db->sql_query($sql1);
        $backup = array();

        while ($ob = $db->sql_fetch_object($res1, MYSQLI_ASSOC)) {

            $tables  = explode(",", trim($ob->table_name));
            $crypted = explode(",", $ob->crypted_fields);

            Debug::debug("--------------");
            Debug::debug($ob->config_file, "config file");


            if (!empty($tables)) {
                foreach ($tables as $table) {
                    if (empty($table)) {
                        continue;
                    }

                    Debug::debug($table, "table MySQL");

                    $sql2 = "SELECT * FROM `".$table."`";

                    $res2 = $db->sql_query($sql2);

                    while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                        if (!empty($ob->crypted_fields)) {

                            $fields = explode(",", $ob->crypted_fields);

                            foreach ($fields as $field) {
                                $arr2[$field] = Chiffrement::decrypt($arr2[$field]);
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

        debug($backup);
    }

    public function getExportOption()
    {
        $db  = $this->di['db']->sql(DB_DEFAULT);
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

        return $export_option;
    }

    private function getUniqueKey($table_name)
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "select group_concat(COLUMN_NAME) as colonne from information_schema.KEY_COLUMN_USAGE where TABLE_schema = 'pmacontrol' and table_name ='".$table_name."' and POSITION_IN_UNIQUE_CONSTRAINT is null and CONSTRAINT_NAME != 'PRIMARY' group by CONSTRAINT_NAME;";

        $res = $db->sql_query($sql);

        $unique = array();
        while ($arr    = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $unique[] = $arr['colonne'];
        }

        return $unique;
    }

    //a mettre dans une librairy

}
/* $compressed   = gzcompress('Compresse moi', 9);
  $uncompressed = gzuncompress($compressed);
  echo $uncompressed; */
