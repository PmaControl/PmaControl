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

        $nb_key = $db->sql_num_rows($res);

        Debug::debug($nb_key, "Nombre de clefs étrangère potentiels");

        $nb_fk_found = 0;
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $schema_ref = $arr['TABLE_SCHEMA'];
            $table_ref = preg_replace('/(^id\_?)/i', '$2', $arr['COLUMN_NAME']);
            $table_id = "id";


            if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref))) {
                
            } else {
                if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref . "s"))) {
                    
                } else {
                    if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref . "x"))) {
                        
                    } else {
                        //Debug::debug($arr, "Impossible de trouver la table");
                        continue;
                    }
                }
            }



            $schema_ref = $arr2['TABLE_SCHEMA'];
            $table_ref = $arr2['TABLE_NAME'];


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
                    $nb_fk_found++;
                    $default->sql_save($virtual_foreign_key);
                } else {

                    Debug::debug($ob->cpt, "count(1)");
                    Debug::debug($virtual_foreign_key, "No found\n");
                }
            }
        }

        Debug::debug($nb_key, "Nombre de clefs étrangère potentiels");
        Debug::debug($nb_fk_found, "Nombre de clefs étrangère trouvé");

        $percent = round($nb_fk_found / $nb_key * 100, 2);
        Debug::debug($percent . "%", "Nombre de clefs étrangère trouvé");
    }

    /*
      CREATE TABLE `virtual_foreign_key` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_mysql_server` int(11) NOT NULL DEFAULT 0,
      `constraint_schema` varchar(64) NOT NULL DEFAULT '',
      `constraint_table` varchar(64) NOT NULL DEFAULT '',
      `constraint_column` varchar(64) NOT NULL DEFAULT '',
      `referenced_schema` varchar(64) NOT NULL DEFAULT '',
      `referenced_table` varchar(64) NOT NULL DEFAULT '',
      `referenced_column` varchar(64) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`),
      UNIQUE KEY `id_mysql_server_2` (`id_mysql_server`,`constraint_schema`,`constraint_table`,`constraint_column`,`referenced_schema`,`referenced_table`,`referenced_column`),
      KEY `id_mysql_server` (`id_mysql_server`),
      CONSTRAINT `id_mysql_server_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4

     */

    public function isTableExist($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_name = $param[2];
        $database_name = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT TABLE_SCHEMA, TABLE_NAME from `information_schema`.`tables` WHERE `TABLE_SCHEMA` = '" . $database_name . "' AND  LOWER(`TABLE_NAME`) = LOWER('" . $table_name . "');";
        //Debug::sql($sql);
        $res = $db->sql_query($sql);

        $nb_tables = $db->sql_num_rows($res);
        if ($nb_tables > 1) {
            Debug::parseDebug($nb_tables, "Nombre de tables");
        }

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            //Debug::debug($arr, "Table trouvé");
            return $arr;
        }

        return false;
    }

    public function cleanUp($param) {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "TRUNCATE TABLE `virtual_foreign_key`;";
        $db->sql_query($sql);

        Debug::sql($sql);
    }

    public function findField($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database_name = $param[1];
        $table_name = $param[2];
        $field_name = $param[3];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT count(1) as cpt
        from information_schema.COLUMNS     
        where TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema') 
        AND TABLE_SCHEMA = '" . $database_name . "'
        AND TABLE_NAME = '" . $table_name . "'
        AND COLUMN_NAME = '" . $field_name . "';";
    }

    public function getAll($param) {

        $this->cleanUp($param);
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "select id from mysql_server where id_environment=1 and id != 1;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->autoDetect(array($ob->id));
        }
    }

    public function fill($param) {
        
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM virtual_foreign_key";
        $res = $db->sql_query($sql);

        $data['fks'] = array();
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $data['fks'][] = $ob;
        }

        $this->set('data', $data);
    }

}
