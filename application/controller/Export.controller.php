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
            $db = $this->di['db']->sql(DB_DEFAULT);


            $sql = "SELECT * FROM `export_option`";

            $res = $db->sql_query($sql);

            $data['options'] = array();
            while ($arr             = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $data['options'][$arr['id']] = $arr;
            }

            $backup = array();
            foreach ($_POST['export_option'] as $id => $val) {

                if (!empty($data['options'][$id]['table_name'])) {

                    $tables = explode(",", $data['options'][$id]['table_name']);
                    $crypted = explode(",", $data['options'][$id]['crypted_fields']);



                    foreach ($tables as $table) {
                        $sql = "SELECT * FROM `".$table."`";

                        $res = $db->sql_query($sql);

                        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                            if (!empty($data['options'][$id]['decrypt'])) {

                                $fields = explode(",", $data['options'][$id]['crypted_fields']);

                                foreach ($fields as $field) {
                                    $arr[$field] = Chiffrement::decrypt($arr[$field]);
                                }
                            }


                            $backup[$data['options'][$id]['key']][] = $arr;
                        }
                    }
                } else if ($data['options'][$id]['config_file']) {

                    $config = file_get_contents(CONFIG.$data['options'][$id]['config_file']);
                    preg_match_all("/define\(\"(\w+)\"\s*,\s*\"(.*)\"/", $config, $output_array);

                    foreach ($output_array[1] as $key => $val) {

                        $backup[$data['options'][$id]['key']][$val] = $output_array[2][$key];
                    }
                    //debug($output_array);
                }
            }


            $json = json_encode($backup);

            $compressed = gzcompress($json, 9);




            $crypted = Crypt::encrypt($compressed, $_POST['export']['password']);


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

                $this->import(array($json));
            }
        }
    }

    public function import($param)
    {

        $file = $param[0];

        $json = file_get_contents($file);

        $arr = json_decode($json, true);

        //debug($arr);


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
}
/*$compressed   = gzcompress('Compresse moi', 9);
$uncompressed = gzuncompress($compressed);
echo $uncompressed;*/