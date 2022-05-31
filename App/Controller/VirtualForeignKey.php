<?php

/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

// Installation des gestionnaires de signaux
declare(ticks=1);

namespace App\Controller;

use Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \App\Library\Mysql;

class VirtualForeignKey extends Controller {

    public function autoDetect($param) {
        Debug::parseDebug($param);

        //$id_mysql_server = $param[0];

        $this->autoId($param);
    }

    public function autoId($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);
        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "select TABLE_SCHEMA,TABLE_NAME, COLUMN_NAME 
from information_schema.COLUMNS 
where TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema') 
and COLUMN_NAME != 'id' and COLUMN_NAME like 'id%'";

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {



            $schema_ref = $arr['TABLE_SCHEMA'];
            $table_ref = preg_replace('/(^id\_?)/i', '$2', $arr['COLUMN_NAME']);
            $table_id = "id";

            $sql2 = "SELECT count(1) as cpt
            from information_schema.COLUMNS     
            where TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema') 
            AND table_schema = '" . $schema_ref . "'
                AND TABLE_NAME = '" . $table_ref . "'
                    AND COLUMN_NAME = '" . $table_id . "'";

            $res2 = $db->sql_query($sql2);

            while ($ob = $db->sql_fetch_object($res2)) {

                $virtual_foreign_key = array();
                $virtual_foreign_key['virtual_foreign_key']['id_mysql_server'] = $id_mysql_server;
                $virtual_foreign_key['virtual_foreign_key']['constraint_schema'] = $arr['TABLE_SCHEMA'];
                $virtual_foreign_key['virtual_foreign_key']['constraint_table'] = $arr['TABLE_NAME'];
                $virtual_foreign_key['virtual_foreign_key']['constraint_column'] = $arr['COLUMN_NAME'];
                $virtual_foreign_key['virtual_foreign_key']['referenced_schema'] = $schema_ref;
                $virtual_foreign_key['virtual_foreign_key']['referenced_table'] = $table_ref;
                $virtual_foreign_key['virtual_foreign_key']['referenced_column'] = $table_id;

                if ($ob->cpt === "1") {
                    $default->sql_save($virtual_foreign_key);
                } else {
                    
                    Debug::debug($ob->cpt, "count(1)");
                    Debug::debug($virtual_foreign_key,"No found\n");
                }
            }
        }
    }

    /*
     * CREATE TABLE `virtual_foreign_key` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_mysql_server` int(11) NOT NULL DEFAULT 0,
      `constraint_schema` varchar(64) NOT NULL DEFAULT '',
      `constraint_table` varchar(64) NOT NULL DEFAULT '',
      `constraint_column` varchar(64) NOT NULL DEFAULT '',
      `referenced_schema` varchar(64) NOT NULL DEFAULT '',
      `referenced_table` varchar(64) NOT NULL DEFAULT '',
      `referenced_column` varchar(64) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`),
      KEY `id_mysql_server` (`id_mysql_server`),
      CONSTRAINT `id_mysql_server_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
     * 
     * 
     * 
     */
}
