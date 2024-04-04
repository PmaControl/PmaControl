<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

class MysqlDatabase extends Controller
{
    public function menu($param)
    {
        $id_mysql_server = $param[0];
        $table_schema = $param[1];

        $_GET['mysql_server']['id'] = $id_mysql_server;

        $default = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT a.id, a.schema_name from mysql_database a WHERE a.schema_name ='".$table_schema."' AND id_mysql_server=".$id_mysql_server." 
        UNION ALL SELECT max(b.id) as id, b.schema_name from mysql_database b WHERE b.id_mysql_server=".$id_mysql_server;

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)){
            $_GET['mysql_database']['id'] = $ob->id;

            if ($table_schema != $ob->schema_name) {
                $url = str_replace("/$table_schema/", "/".$ob->schema_name."/", $_GET['url'] );
                header("location: ".LINK.$url);
            }
            break;
        }

        $this->di['js']->code_javascript('
        $("#mysql_database-id").change(function () {
            data = $("#mysql_database-id option:selected").text();
            var segments = GLIAL_URL.split("/");

            if(segments.length > 3) {
                segments[3] = data;
            }
            newPath = GLIAL_LINK + segments.join("/");

            window.location.href=newPath;
        });');

        $this->set('param', $param);
    }

    function getDatabaseByServer($param)
    {
        $this->di['js']->addJavascript(array('bootstrap-select.min.js', 'Common/getDatabaseByServer.js'));

        $id_mysql_server = $param[0];
        $options = (array) $param[1];
        $data['options'] = $options;

        if (!empty($id_mysql_server)) {
            $db_to_get_db = Sgbd::sql(DB_DEFAULT);

            $sql  = "select id,schema_name from mysql_database where id_mysql_server=".$id_mysql_server." ORDER BY schema_name;";
            $res2 = $db_to_get_db->sql_query($sql);

            $data['databases'] = [];
            while ($ob                = $db_to_get_db->sql_fetch_object($res2)) {
                $tmp                 = [];
                $tmp['id']           = $ob->id;
                $tmp['libelle']      = $ob->schema_name;
                $data['databases'][] = $tmp;

            }
        } else {
            $data['databases'] = array();
        }

        $this->set("data", $data);
        return $data;
    }

    public function mpd($param)
    {
        $data['param'] = $param;

        $_GET['mysql_server']['id'] = $param[0];

        $this->set('data', $data);
        $this->set('param', $param);
    }

    public function foreignKey($param)
    {
        $data = array();

        $_GET['mysql_server']['id'] = $param[0];

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }


    public function table($param)
    {
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $_GET['mysql_server']['id'] = $id_mysql_server;

        $db              = Mysql::getDbLink($id_mysql_server);
        $default         = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM information_schema.tables WHERE table_schema='".$table_schema."' ORDER BY table_name";
        $res = $db->sql_query($sql);

        $data['table'] = array();
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)){
            $data['table'][] = $arr;
        }

        $data['table_schema'] = $table_schema;

        $data['param'] = $param;
        $this->set('data',$data);
        $this->set('param', $param);
    }



}