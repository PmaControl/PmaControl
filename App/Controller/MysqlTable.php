<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sql\Mysql\MasterSlave;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \App\Library\Debug;
use \App\Library\Graphviz;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

class MysqlTable extends Controller
{
    public function menu($param)
    {
        $data = array();

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2] ?? "";

        $_GET['mysql_table']['id'] = $table_name;

        $default = Sgbd::sql(DB_DEFAULT);

        $sql ="SELECT id from mysql_database WHERE id_mysql_server=".$id_mysql_server." AND schema_name='".$table_schema."' ORDER BY schema_name;";

        $res = $default->sql_query($sql);

        while($ob = $default->sql_fetch_object($res)) {
            $_GET['id_mysql_database']['id'] = $ob->id;
        }

        $this->di['js']->code_javascript('
        $("#mysql_table-id").change(function () {
            data = $("#mysql_table-id option:selected").text();
            var segments = GLIAL_URL.split("/");

            if(segments.length > 4) {
                segments[4] = data;
            }
            newPath = GLIAL_LINK + segments.join("/");

            window.location.href=newPath;
        });');

        $data['param'] = $param;
        $this->set('param', $param);
        $this->set('data', $data);
        
    }


    public function getTableByDatabase($param)
    {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js', 'Common/getDatabaseByServer.js'));

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $options = (array) $param[2];
        
        $data['options'] = $options;

        if (!empty($id_mysql_server)) {



            $db = Mysql::getDbLink($id_mysql_server);

            $sql2 = "SELECT table_name FROM information_schema.tables WHERE table_schema ='".$table_schema."' ORDER BY table_name";
            $res2 = $db->sql_query($sql2);
    
 

            $data['tables'] = [];
            while ($ob                = $db->sql_fetch_object($res2)) {
                $tmp                 = [];
                $tmp['id']           = $ob->table_name;
                $tmp['libelle']      = $ob->table_name;
                $data['tables'][] = $tmp;

            }
        } else {
            $data['tables'] = array();
        }

        $this->set("data", $data);
        return $data;
    }




}