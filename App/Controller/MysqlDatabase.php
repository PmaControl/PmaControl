<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sql\Mysql\MasterSlave;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \App\Library\Debug;
use \App\Library\Graphviz;
use \App\Library\Mysql as Mysql2;
use \Glial\Sgbd\Sgbd;

class MysqlDatabase extends Controller
{
    public function menu($param)
    {
        $id_mysql_server = $param[0];
        $table_schema = $param[1];

        $default = Sgbd::sql(DB_DEFAULT);

        Mysql::getDbLink($id_mysql_server);
        
    }


    public function getSchema($param)
    {





        
        
    }
   
}