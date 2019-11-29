<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

// Installation des gestionnaires de signaux
declare(ticks=1);

use Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

class Chemin extends Controller
{

    public function possibilite($param)
    {
        $id_mysql_server = $param[0];
        $database        = $param[1];

        $name = $this->getNameMysqlServer($id_mysql_server);
        $db   = Sgbd::sql($name);

        $db->sql_select_db($database);


        $sql = "SELECT distinct `TABLE_NAME` as table_name FROM `information_schema`.`KEY_COLUMN_USAGE` where TABLE_SCHEMA='mydb' and REFERENCED_TABLE_NAME is not null
UNION
SELECT REFERENCED_TABLE_NAME as table_name FROM `information_schema`.`KEY_COLUMN_USAGE` where REFERENCED_TABLE_SCHEMA='mydb' and REFERENCED_TABLE_NAME is not null;";
        $res = $db->sql_query($sql);


        $tables = array();
        while ($ob     = $db->sql_fetch_object($res)) {
            $tables[] = $ob->table_name;
        }


        $tables2 = $tables;

        foreach ($tables as $table) {
            foreach ($tables2 as $table2) {


                
                
                $sql2 = $this->getPaths($table, $table2, $database);
                $res2 = $db->sql_query($sql2);

                
                if ($db->sql_num_rows($res2) > 0)
                {
                    echo "---------------------------\n";
                    echo $table2." ===> ".$table."\n";
                }
                
                
                while ($ob = $db->sql_fetch_object($res2)) {
                    echo $ob->cur_path."  | ".$ob->cur_dest."\n";
                }
                
                
            }
        }
    }

    function getNameMysqlServer($id)
    {

        $default = Sgbd::sql(DB_DEFAULT);

        $sql                 = "SELECT `name` FROM mysql_server WHERE id ='".$id."';";
        $res_id_mysql_server = $default->sql_query($sql);
        if ($default->sql_num_rows($res_id_mysql_server) == 1) {
            $ob   = $default->sql_fetch_object($res_id_mysql_server);
            $name = $ob->name;
        } else {
            throw new \Exception("PMACTRL-001 : Impossible to find the MySQL server");
        }

        return $name;
    }

    public function getPaths($table, $table2, $database)
    {
        $sql = "WITH RECURSIVE paths (cur_path, cur_dest) AS (
    SELECT `TABLE_NAME`, `TABLE_NAME` FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE `TABLE_NAME`='".$table."' AND TABLE_SCHEMA='".$database."'  
  UNION
    SELECT CONCAT(paths.cur_path, ',', `information_schema`.`KEY_COLUMN_USAGE`.REFERENCED_TABLE_NAME), `information_schema`.`KEY_COLUMN_USAGE`.REFERENCED_TABLE_NAME 
      FROM paths, `information_schema`.`KEY_COLUMN_USAGE` 
      WHERE paths.cur_dest = `information_schema`.`KEY_COLUMN_USAGE`.`TABLE_NAME` AND 
      NOT FIND_IN_SET(`information_schema`.`KEY_COLUMN_USAGE`.REFERENCED_TABLE_NAME, paths.cur_path)
) 
SELECT * FROM paths where cur_dest = '".$table2."';";
        
        return $sql;
    }
}