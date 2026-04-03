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

/**
 * Class responsible for mysql table workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class MysqlTable extends Controller
{
/**
 * Handle mysql table state through `menu`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for menu.
 * @phpstan-return void
 * @psalm-return void
 * @see self::menu()
 * @example /fr/mysqltable/menu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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


/**
 * Retrieve mysql table state through `getTableByDatabase`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getTableByDatabase.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getTableByDatabase()
 * @example /fr/mysqltable/getTableByDatabase
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
            $res2 = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql2, $id_mysql_server, __METHOD__);
    
 

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
