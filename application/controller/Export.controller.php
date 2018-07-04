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

class Export extends Controller
{
    var $table_with_data = array("menu", "menu_group", "translation_main", "geolocalisation_city",
        "geolocalisation_continent", "geolocalisation_country", "history_etat",
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

                file_put_contents("/tmp/gg", $json);

                $this->import($json);
            }
        }
    }

    private function import($json)
    {

        $arr = json_decode($json, true);

        $db = $this->di['db']->sql(DB_DEFAULT);

        foreach ($arr['mysql'] as $mysql) {

            unset($mysql['id']);

            $mysql['error'] = '';
            debug($mysql);

            $data['mysql_server'] = $mysql;

            $res = $db->sql_save($data);

            if (!$res) {

                debug($db->sql_error());
            }
        }
    }

    public function test_import($param)
    {
        $file = $param[0];
        $json = file_get_contents($file);
        $this->import($json);
    }

    private function _export($options = array())
    {
        $db   = $this->di['db']->sql(DB_DEFAULT);
        $sql1 = "SELECT * FROM `export_option` where active ='1'";
        $res1 = $db->sql_query($sql1);
        $backup = array();

        while ($ob = $db->sql_fetch_object($res1, MYSQLI_ASSOC)) {

            $tables  = explode(",", trim($ob->table_name));
            $crypted = explode(",", $ob->crypted_fields);

            Debug::debug("--------------");
            Debug::debug($ob->config_file,"config file");


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
}
/* $compressed   = gzcompress('Compresse moi', 9);
  $uncompressed = gzuncompress($compressed);
  echo $uncompressed; */