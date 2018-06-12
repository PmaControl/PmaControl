<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Debug;

class Export extends Controller
{

    
    var $table_with_data = array("menu", "menu_group", "translation_main", "geolocalisation_city",
        "geolocalisation_continent", "geolocalisation_country","history_etat",
        "group", "environment", "daemon_main", "version", "sharding", "ts_variable", "architecture_legend", "home_box");
    var $exlude_table    = array("translation_*", "slave_*","master_*", "variables_*", "status_*", "ts_value_*");

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

        $this->title = '<span class="glyphicon glyphicon-import"></span> ' . __("Import / Export");

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->di['js']->code_javascript("
function toggle(source) {
  checkboxes = document.getElementsByName('foo');
  for(var checkbox in checkboxes)
    checkbox.checked = source.checked;
}");




        $sql = "SELECT * FROM `export_option`";

        $res = $db->sql_query($sql);

        $data['options'] = array();
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['options'][] = $arr;

        }



        $this->set('data', $data);
        
    }
}