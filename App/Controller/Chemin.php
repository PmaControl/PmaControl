<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */



namespace App\Controller;


use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;

/**
 * Class responsible for chemin workflows.
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
class Chemin extends Controller
{

/**
 * Handle chemin state through `possibilite`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for possibilite.
 * @phpstan-return void
 * @psalm-return void
 * @see self::possibilite()
 * @example /fr/chemin/possibilite
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function possibilite($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $name = $this->getNameMysqlServer($id_mysql_server);
        $db   = Sgbd::sql($name);

        $db->sql_select_db($database);


        $sql = "SELECT distinct `TABLE_NAME` as table_name 
        FROM `information_schema`.`KEY_COLUMN_USAGE` 
        where TABLE_SCHEMA='".$database."' and REFERENCED_TABLE_NAME is not null
UNION
SELECT REFERENCED_TABLE_NAME as table_name 
FROM `information_schema`.`KEY_COLUMN_USAGE` 
where REFERENCED_TABLE_SCHEMA='".$database."' and REFERENCED_TABLE_NAME is not null ORDER BY TABLE_NAME;";
        $res = $db->sql_query($sql);

        Debug::sql($sql);

        $tables = array();
        while ($ob     = $db->sql_fetch_object($res)) {
            $tables[] = $ob->table_name;
        }


        $tables2 = $tables;

        foreach ($tables as $table) {
            foreach ($tables2 as $table2) {

                if ($table === $table2)
                {
                    continue;
                }
                $sql2 = $this->getPaths($table, $table2, $database);
                $res2 = $db->sql_query($sql2);

                
                if ($db->sql_num_rows($res2) > 0)
                {
                    echo "---------------------------\n";
                    echo $table." ===> ".$table2."\n";
                }
                
                
                while ($ob = $db->sql_fetch_object($res2)) {
                    echo $ob->cur_path."  | ".$ob->cur_dest."\n";
                }
                
                
            }
        }
    }

/**
 * Retrieve chemin state through `getNameMysqlServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $id Input value for `id`.
 * @phpstan-param mixed $id
 * @psalm-param mixed $id
 * @return mixed Returned value for getNameMysqlServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getNameMysqlServer()
 * @example /fr/chemin/getNameMysqlServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Retrieve chemin state through `getPaths`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @param mixed $table2 Input value for `table2`.
 * @phpstan-param mixed $table2
 * @psalm-param mixed $table2
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @return mixed Returned value for getPaths.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getPaths()
 * @example /fr/chemin/getPaths
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
