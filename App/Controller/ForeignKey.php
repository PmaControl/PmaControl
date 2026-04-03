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

use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \App\Library\Debug;
use \App\Library\Mysql;

/**
 * Class responsible for foreign key workflows.
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
class ForeignKey extends Controller
{

    CONST BEGIN = "id%";
    CONST END = "%id";

/**
 * Stores `$primary_key` for primary key.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $primary_key = array();

/**
 * Handle foreign key state through `autoDetect`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for autoDetect.
 * @phpstan-return void
 * @psalm-return void
 * @see self::autoDetect()
 * @example /fr/foreignkey/autoDetect
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function autoDetect($param)
    {
        $this->view = false;
        Debug::parseDebug($param);
        //$id_mysql_server = $param[0];

        $this->autoId($param);

        if ( ! IS_CLI){

            $location = $_SERVER['HTTP_REFERER'];
            header("location: $location");
            //exit;
        }
    }

/**
 * Handle foreign key state through `import`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for import.
 * @phpstan-return void
 * @psalm-return void
 * @see self::import()
 * @example /fr/foreignkey/import
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function import($param)
    {
        $this->view = false;
        Debug::parseDebug($param);
        //$id_mysql_server = $param[0];

        $this->importRealForeignKey($param);

        if ( ! IS_CLI){

            $location = $_SERVER['HTTP_REFERER'];
            header("location: $location");
            //exit;
        }
    }

/**
 * Handle foreign key state through `autoId`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for autoId.
 * @phpstan-return void
 * @psalm-return void
 * @see self::autoId()
 * @example /fr/foreignkey/autoId
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function autoId($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database = $param[0];
        
        $default         = Sgbd::sql(DB_DEFAULT);

        $sql = "DELETE FROM foreign_key_virtual WHERE (id_mysql_server ='".$id_mysql_server."' OR id_mysql_server__link=".$id_mysql_server.")
        and is_automatic = 1";
        //$sql = "TRUNCATE table foreign_key_virtual;";
        $default->sql_query($sql);

        $databases = $this->getDatabase($param);

        foreach($databases as $database)
        {
            $table_not_found = array();
            $id_position = $this->getIdPosition(array($id_mysql_server, $database));

            $column_1 = $this->getIdFromColumnName(array($id_mysql_server, $database, $id_position));
            $column_2 = $this->getIdFromComposedPk(array($id_mysql_server, $database, $id_position));

            $nb_key = count($column_1) + count($column_2);

            Debug::debug(count($column_1), "Nombre de clefs étrangère potentiels hors PK");
            Debug::debug(count($column_2), "Nombre de clefs étrangère potentiels from composed PK");
            Debug::debug($nb_key, "Nombre total de clefs étrangère potentiels");

            $all_column = array_merge($column_1, $column_2);

            $nb_fk_found = 0;
            foreach ($all_column as $arr) {

                Debug::success($arr, "reference to find ");

                $schema_ref = $arr['TABLE_SCHEMA'];

                //start with id
                if ($id_position === self::BEGIN) {
                    $table_ref  = preg_replace('/(^id\_?)/i', '$2', $arr['COLUMN_NAME']);
                }
                //end with id
                else if ($id_position === self::END) {
                    $table_ref  = preg_replace('/(\_?id$)/i', '$2', $arr['COLUMN_NAME']);
                }
                else {
                    Debug::error( "Error");
                }


                if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref))) {
                    
                } 
                else if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref."s"))) {
                        
                }
                else if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref."x"))) {
                            
                } /*
                else if ($arr2 = $this->isTableExist(array($id_mysql_server, $schema_ref, $table_ref2))) {
                    $table_id = $arr['COLUMN_NAME'];
                    //Debug::debug($arr2, 'ARR2');   
                } */
                else if ($arr2 = $this->getConbinaison(array($id_mysql_server, $schema_ref, $table_ref)))
                {
                    Debug::debug($arr2, '##################################################');
                }
                else {

                    $table_not_found[] = $table_ref;
                    Debug::error($arr, "Impossible de trouver la table");
                    continue;
                }
                
                $schema_ref = $arr2['TABLE_SCHEMA'];
                $table_ref  = $arr2['TABLE_NAME'];

                //find PRIMARY KEY
                $primary_key = $this->getPrimaryKey($id_mysql_server, $schema_ref, $table_ref);


                $foreign_key_virtual                                                 = array();
                $foreign_key_virtual['foreign_key_virtual']['id_mysql_server']       = $id_mysql_server;
                $foreign_key_virtual['foreign_key_virtual']['constraint_schema']     = $arr['TABLE_SCHEMA'];
                $foreign_key_virtual['foreign_key_virtual']['constraint_table']      = $arr['TABLE_NAME'];
                $foreign_key_virtual['foreign_key_virtual']['constraint_column']     = $arr['COLUMN_NAME'];
                $foreign_key_virtual['foreign_key_virtual']['id_mysql_server__link'] = $id_mysql_server;
                $foreign_key_virtual['foreign_key_virtual']['referenced_schema']     = $schema_ref;
                $foreign_key_virtual['foreign_key_virtual']['referenced_table']      = $table_ref;
                $foreign_key_virtual['foreign_key_virtual']['referenced_column']     = $primary_key;

                if ($primary_key !== false) {
                    $nb_fk_found++;
                    $default->sql_save($foreign_key_virtual);
                } else {
                    $foreign_key_proposal                                                 = array();
                    $foreign_key_proposal['foreign_key_proposal']['id_mysql_server']       = $id_mysql_server;
                    $foreign_key_proposal['foreign_key_proposal']['constraint_schema']     = $arr['TABLE_SCHEMA'];
                    $foreign_key_proposal['foreign_key_proposal']['constraint_table']      = $arr['TABLE_NAME'];
                    $foreign_key_proposal['foreign_key_proposal']['constraint_column']     = $arr['COLUMN_NAME'];
                    $foreign_key_proposal['foreign_key_proposal']['id_mysql_server__link'] = $id_mysql_server;
                    $foreign_key_proposal['foreign_key_proposal']['referenced_schema']     = $schema_ref;
                    //$default->sql_save($foreign_key_proposal);

                    //save to other table and propose to set link manually
                    Debug::error($foreign_key_virtual, "No found\n");
                }
                
            }

            if ($nb_key > 0)
            {
                Debug::debug($nb_key, "Nombre de clefs étrangère potentiels");
                Debug::debug($nb_fk_found, "Nombre de clefs étrangère trouvé");
    
                $percent = round($nb_fk_found / $nb_key * 100, 2);
                Debug::debug($percent."%", "Nombre de clefs étrangère trouvé");
    
                $nb_not_found = count($table_not_found);
                Debug::error($nb_not_found, "Number of link not found");
    
                $list_error = $this->sort_and_count_array($table_not_found);
                Debug::error( $list_error, "Impossible to find these tables :");
            }

            //Debug::warning($percent,'-----------------------------------------------------------');
        }
    }

/**
 * Handle foreign key state through `isTableExist`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for isTableExist.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isTableExist()
 * @example /fr/foreignkey/isTableExist
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function isTableExist($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_name      = $param[2];
        $database_name   = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT TABLE_SCHEMA, TABLE_NAME 
        FROM `information_schema`.`tables` WHERE `TABLE_SCHEMA` = '".$database_name."' 
        AND  LOWER(`TABLE_NAME`) = LOWER('".$table_name."');";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);

        $nb_tables = $db->sql_num_rows($res);
        if ($nb_tables > 1) {
            Debug::error($nb_tables, "Nombre de tables");
        }

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            //Debug::debug($arr, "Table trouvé");
            return $arr;
        }

        return false;
    }

/**
 * Handle foreign key state through `cleanUp`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for cleanUp.
 * @phpstan-return void
 * @psalm-return void
 * @see self::cleanUp()
 * @example /fr/foreignkey/cleanUp
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cleanUp($param)
    {

        Debug::parseDebug($param);

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "TRUNCATE TABLE `foreign_key_virtual`;";
        $db->sql_query($sql);
        Debug::sql($sql);
    }

/**
 * Handle foreign key state through `findField`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for findField.
 * @phpstan-return void
 * @psalm-return void
 * @see self::findField()
 * @example /fr/foreignkey/findField
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function findField($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database_name   = $param[1];
        $table_name      = $param[2];
        $field_name      = $param[3];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT count(1) as cpt
        from information_schema.COLUMNS     
        where TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema') 
        AND TABLE_SCHEMA = '".$database_name."'
        AND TABLE_NAME = '".$table_name."'
        AND COLUMN_NAME = '".$field_name."';";
    }

/**
 * Retrieve foreign key state through `getAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getAll()
 * @example /fr/foreignkey/getAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getAll($param)
    {

        $this->cleanUp($param);
        Debug::parseDebug($param);

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "select id from mysql_server;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->autoDetect(array($ob->id));
        }
    }

/**
 * Handle foreign key state through `fill`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for fill.
 * @phpstan-return void
 * @psalm-return void
 * @see self::fill()
 * @example /fr/foreignkey/fill
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function fill($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];
        $database = $param[1];

        $sql = "SELECT * FROM foreign_key_virtual WHERE id_mysql_server = ".$id_mysql_server." 
        AND (constraint_schema ='".$database."' OR referenced_schema ='".$database."')";
        
        $res = $db->sql_query($sql);

        $data['virtual_fk'] = array();
        
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['virtual_fk'][] = $ob;
        }

        $data['real_fk'] = Mysql::getRealForeignKey($param);
        $this->set('data', $data);
    }


    /*

    @param id_mysql_server int
    @param database_name
    
    Récupère les préfix des tables a exclure pour une base de données,

    exemple : tb_bulletinnepasimporter.IdAgentMois  => tb_agentmois.IdAgentMois
    on veux ici pas tenir compte du prefix "tb_"
    */

    public function getPrefix($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database_name   = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM foreign_key_remove_prefix WHERE id_mysql_server='".$id_mysql_server."' AND database_name='".$database_name."'";
        $res = $db->sql_query($sql);

        $data['prefix'] = array();
        while ($ob          = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
            $data['prefix'][] = $ob->prefix;
        }

        Debug::debug($data['prefix']);

        return $data['prefix'];

    }

    /*
        On detectee le paterne pour une table 
        id_nametable => table.id 

        si id au debut ou à la fin

        return begining or end
    */

    public function getIdPosition($param)
    {
        $id_mysql_server = $param[0];   
        
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1]; 

        $db = Mysql::getDbLink($id_mysql_server);
        $id_positions = array(self::BEGIN, self::END);
        $result = array();

        foreach($id_positions as $id_position)
        {
            $sql = "select count(1) as cpt
            from information_schema.COLUMNS 
            where TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema', 'sys') 
            AND TABLE_SCHEMA = '".$table_schema."'
            AND COLUMN_KEY != 'PRI' 
            AND COLUMN_NAME like '".$id_position."'";
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res)) {
                $result[$id_position] = $ob->cpt;
            }
        }

        Debug::debug($result,"Stats");

        if ($result[self::BEGIN] > $result[self::END]) {
            Debug::debug(self::BEGIN,"return");
            return self::BEGIN;
        }
        else {
            Debug::debug(self::END,"return");
            return self::END;
        }
    }

/**
 * Retrieve foreign key state through `getConbinaison`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getConbinaison.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getConbinaison()
 * @example /fr/foreignkey/getConbinaison
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getConbinaison($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database_name   = $param[1];
        $table_name      = $param[2];

        $all_prefix = $this->getPrefix($param);
        $all_prefix[] = "";

        foreach($all_prefix as $prefix)
        {
            Debug::debug($prefix, "prefix");

            $table_to_try = "$prefix$table_name";
            Debug::debug($table_to_try, "table to try");

            $ret = $this->isTableExist(array($id_mysql_server,$database_name, $table_to_try));
            
            if ($ret !== false) {
                return $ret;
            }
        }

        return false;
    }

/**
 * Retrieve foreign key state through `getDatabase`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getDatabase.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getDatabase()
 * @example /fr/foreignkey/getDatabase
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getDatabase($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "select SCHEMA_NAME as schema_name from information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN('information_schema', 'sys', 'performance_schema', 'mysql');";
        $res = $db->sql_query($sql);

        $databases = array();
        while ($ob = $db->sql_fetch_object($res))
        {
            $databases[] = $ob->schema_name;
        }

        Debug::debug($databases, "databases");

        return $databases;
    }

/**
 * Handle foreign key state through `sort_and_count_array`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $name_arr Input value for `name_arr`.
 * @phpstan-param mixed $name_arr
 * @psalm-param mixed $name_arr
 * @return mixed Returned value for sort_and_count_array.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::sort_and_count_array()
 * @example /fr/foreignkey/sort_and_count_array
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function sort_and_count_array($name_arr)
    {
        $new_arr = array_count_values($name_arr);
        ksort($new_arr);
        
        return $new_arr;
    }


/**
 * Retrieve foreign key state through `getPrimaryKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @return mixed Returned value for getPrimaryKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getPrimaryKey()
 * @example /fr/foreignkey/getPrimaryKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getPrimaryKey($id_mysql_server, $database, $table)
    {
        $db = Mysql::getDbLink($id_mysql_server);

        if (empty(self::$primary_key[$database][$table])) {

            $sql = "SHOW INDEX FROM `".$database."`.`".$table."` WHERE `Key_name` ='PRIMARY';";
            $res = $db->sql_query($sql);

            $cpt = $db->sql_num_rows($res);
            if ($cpt == "0") {
                //log ERROR
                Debug::error($table, "PMACONTROL-069 : this table '".$table."' haven't Primary key !");
                return false;
            } else if ($cpt == "1"){
                while ($ob = $db->sql_fetch_object($res)) {
                    self::$primary_key[$database][$table] = $ob->Column_name;
                }
            }
            else {
                //log ERROR
                Debug::error($table, "PMACONTROL-069 : this table '".$table."' have composed Primary key ($cpt) !");
                return false;
            }
        }

        return self::$primary_key[$database][$table];
    }


/**
 * Retrieve foreign key state through `getIdFromComposedPk`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getIdFromComposedPk.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getIdFromComposedPk()
 * @example /fr/foreignkey/getIdFromComposedPk
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getIdFromComposedPk($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $id_position = $param[2];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT group_concat(kcu.COLUMN_NAME) as col, tc.TABLE_NAME as table_name, COUNT(*) as nb_columns 
        FROM information_schema.TABLE_CONSTRAINTS tc 
        JOIN information_schema.KEY_COLUMN_USAGE kcu ON tc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA 
        AND tc.TABLE_NAME = kcu.TABLE_NAME
        AND tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME 
        WHERE tc.CONSTRAINT_TYPE = 'PRIMARY KEY' AND tc.TABLE_SCHEMA = '".$table_schema."'
        GROUP BY tc.TABLE_SCHEMA, tc.TABLE_NAME HAVING COUNT(*) > 1;";

        $res = $db->sql_query($sql);

        $resultat = array();
        $notfound = array();

        while ($ob = $db->sql_fetch_object($res)) {

            $cols = explode(",", $ob->col);
            
            foreach($cols as $col)
            {
                $output_array = array();
                //start with id
                if ($id_position === self::BEGIN) {
                    preg_match('/(^id\_?)/i', $col, $output_array);
                }
                //end with id
                else if ($id_position === self::END) {
                    preg_match('/(\_?id$)/i', $col, $output_array);
                }

                $tmp = array();
                $tmp['TABLE_SCHEMA'] = $table_schema;
                $tmp['TABLE_NAME'] = $ob->table_name;
                $tmp['COLUMN_NAME'] = $col;

                if (count($output_array) === 2)
                {
                    // match
                    $resultat[] = $tmp;
                }
                else
                {
                    // not match
                    $notfound[] = $tmp;
                }
            }
        }

        Debug::success($resultat, "RESULTAT");
        Debug::success($notfound, "ERROR");

        return $resultat;
    }

    /*
    get all id from column name except from primary key
    */

    public function getIdFromColumnName($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $id_position = $param[2];

        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "select TABLE_SCHEMA,TABLE_NAME, COLUMN_NAME 
        from information_schema.COLUMNS 
        where TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema', 'sys') 
        AND TABLE_SCHEMA = '".$table_schema."'
        AND COLUMN_KEY != 'PRI' and COLUMN_NAME like '".$id_position."'";

        $res = $db->sql_query($sql);

        $resultat = array();
        $notfound = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $tmp = array();
            $tmp['TABLE_SCHEMA'] = $arr['TABLE_SCHEMA'];
            $tmp['TABLE_NAME'] = $arr['TABLE_NAME'];
            $tmp['COLUMN_NAME'] = $arr['COLUMN_NAME'];
            $resultat[] = $tmp;
        }

        Debug::success($resultat, "RESULTAT");

        return $resultat;
    }

/**
 * Create foreign key state through `createVirtualForeignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for createVirtualForeignKey.
 * @phpstan-return void
 * @psalm-return void
 * @see self::createVirtualForeignKey()
 * @example /fr/foreignkey/createVirtualForeignKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createVirtualForeignKey($param)
    {
        $this->view = false;
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];
        $field_name = $param[3];
        $database_constraint = $param[4];
        $table_constraint = $param[5];
        $field_constraint = $param[6];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "ALTER TABLE `".$table_schema."`.`".$table_name."` 
        ADD FOREIGN KEY (`".$field_name."`) 
        REFERENCES `".$database_constraint."`.`".$table_constraint."`(`".$field_constraint."`) ON DELETE RESTRICT ON UPDATE RESTRICT;";

        $res = $db->sql_query($sql);
    }

/**
 * Create foreign key state through `addForeignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for addForeignKey.
 * @phpstan-return void
 * @psalm-return void
 * @see self::addForeignKey()
 * @example /fr/foreignkey/addForeignKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function addForeignKey($param)
    {
        $this->view = false;
        Debug::parseDebug($param);

        $id_foreign_key_virtual = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id_mysql_server,constraint_schema,constraint_table, constraint_column,
        referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_virtual` WHERE id =".$id_foreign_key_virtual."";

        $res = $db->sql_query($sql);

        while($param = $db->sql_fetch_array($res, MYSQLI_NUM))
        {
            Debug::debug($param);
            $this->createVirtualForeignKey($param);
        }

        if ( ! IS_CLI){
            $location = $_SERVER['HTTP_REFERER'];
            header("location: $location");
        }
    }

/**
 * Handle foreign key state through `settingPrefix`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for settingPrefix.
 * @phpstan-return void
 * @psalm-return void
 * @see self::settingPrefix()
 * @example /fr/foreignkey/settingPrefix
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function settingPrefix($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM foreign_key_remove_prefix";

        $res = $db->sql_query($sql);

        $data['prefix'] = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['prefix'][] = $arr;
        }

        $this->set('data', $data);

    }

/**
 * Create foreign key state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/foreignkey/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param)
    {
        $this->di['js']->code_javascript('$("#foreign_key_remove_prefix-id_mysql_server").change(function () {
            data = $(this).val();
            $("#foreign_key_remove_prefix-database_name").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
               function(){
            $("#foreign_key_remove_prefix-database_name").selectpicker("refresh");
            });
        });');

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['foreign_key_remove_prefix']['id_mysql_server']) && !empty($_POST['foreign_key_remove_prefix']['database_name']) && !empty($_POST['foreign_key_remove_prefix']['prefix'])) {

                $db = Sgbd::sql(DB_DEFAULT);
                $db->sql_save($_POST);
                
                header('location: '.LINK.$this->getClass().'/settingPrefix/');
            }
        }
    }

/**
 * Handle foreign key state through `dropForeignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dropForeignKey.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dropForeignKey()
 * @example /fr/foreignkey/dropForeignKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dropForeignKey($param)
    {
        $this->view = false;
        Debug::parseDebug($param);
        $id_foreign_key_remove_prefix = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "DELETE FROM foreign_key_remove_prefix WHERE id=".$id_foreign_key_remove_prefix."";

        $res = $db->sql_query($sql);

        if ( ! IS_CLI){
            $location = $_SERVER['HTTP_REFERER'];
            header("location: $location");
        }

    }

/**
 * Handle foreign key state through `rmForeignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for rmForeignKey.
 * @phpstan-return void
 * @psalm-return void
 * @see self::rmForeignKey()
 * @example /fr/foreignkey/rmForeignKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function rmForeignKey($param)
    {
        $this->view = false;
        Debug::parseDebug($param);

        $id_foreign_key_virtual = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "DELETE FROM `foreign_key_virtual` WHERE id =".$id_foreign_key_virtual."";
        $db->sql_query($sql);

        if ( ! IS_CLI){
            $location = $_SERVER['HTTP_REFERER'];
            header("location: $location");
        }
    }

/**
 * Retrieve foreign key state through `getRealForeignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getRealForeignKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getRealForeignKey()
 * @example /fr/foreignkey/getRealForeignKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getRealForeignKey($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT * FROM `foreign_key_real` WHERE id_mysql_server = ".$id_mysql_server." AND "
            ." (constraint_schema = '".$database."' OR 	referenced_schema = '".$database."'";

        Debug::sql($sql);

        $res = $db->sql_query($sql);
        $foreign_key = array();

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $md5 = md5($ob['constraint_schema'].$ob['constraint_table'].$ob['constraint_column']);
            $foreign_key[$md5] = $ob;
        }

        //Debug::debug($foreign_key);
        Debug::debug(count($foreign_key));

        return $foreign_key;
    }


    /*
        recupere en cache toute les clefs étrangère
        a faire en webservice également
        le but est de pouvoir travailler dessus même depuis un serveur distant
    */

    public function importRealForeignKey($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1] ?? false;

        $db = Mysql::getDbLink($id_mysql_server);
        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT CONSTRAINT_NAME as constraint_name,  CONSTRAINT_SCHEMA as constraint_schema,TABLE_NAME as constraint_table,COLUMN_NAME as constraint_column,"
            ." REFERENCED_TABLE_SCHEMA as referenced_schema, REFERENCED_TABLE_NAME as referenced_table,REFERENCED_COLUMN_NAME as referenced_column"
            ." FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `REFERENCED_TABLE_NAME` IS NOT NULL ";

        Debug::sql($sql);

        if ($database !== false)
        {
            $sql .= " AND `REFERENCED_TABLE_SCHEMA`='".$database."' "
            ." AND `CONSTRAINT_SCHEMA` ='".$database."' ";
        }

        Debug::sql($sql);
        $res = $db->sql_query($sql);
        $sql2 = "DELETE FROM `foreign_key_real` WHERE id_mysql_server=".$id_mysql_server." OR id_mysql_server__link=".$id_mysql_server."";
        
        if ($database !== false)
        {
            $sql2 .=" AND (constraint_table ='".$database."' OR referenced_table ='".$database."')";
        }
        Debug::sql($sql2);

        $default->sql_query($sql2);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $table = array();
            $table['foreign_key_real'] = $arr;
            $table['foreign_key_real']['id_mysql_server'] = $id_mysql_server;
            $table['foreign_key_real']['id_mysql_server__link'] = $id_mysql_server;
            
            Debug::debug($table);
            $default->sql_save($table);
        }
    }

/**
 * Handle foreign key state through `menu`.
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
 * @example /fr/foreignkey/menu
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

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }

/**
 * Render foreign key state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/foreignkey/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {
        $data = array();




        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }


/**
 * Handle foreign key state through `virtual`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for virtual.
 * @phpstan-return void
 * @psalm-return void
 * @see self::virtual()
 * @example /fr/foreignkey/virtual
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function virtual($param)
    {
        $data = array();

        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];
        $database = $param[1];

        $sql = "SELECT * FROM foreign_key_virtual WHERE id_mysql_server = ".$id_mysql_server." 
        AND (constraint_schema ='".$database."' OR referenced_schema ='".$database."')
        ORDER BY id_mysql_server, constraint_schema,constraint_table, constraint_column";
        
        $res = $db->sql_query($sql);

        $data['virtual_fk'] = array();
        
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['virtual_fk'][] = $ob;
        }

        $this->set('data', $data);

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }

/**
 * Handle foreign key state through `real`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for real.
 * @phpstan-return void
 * @psalm-return void
 * @see self::real()
 * @example /fr/foreignkey/real
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function real($param)
    {
        $data = array();

        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];
        $database = $param[1];

        $_GET['mysql_server']['id'] = $id_mysql_server;

        $sql = "SELECT * FROM foreign_key_real WHERE id_mysql_server = ".$id_mysql_server." 
        AND (constraint_schema ='".$database."' OR referenced_schema ='".$database."') 
        ORDER BY id_mysql_server, constraint_schema,constraint_table, constraint_column";
        
        $res = $db->sql_query($sql);

        $data['real_fk'] = array();
        
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['real_fk'][] = $ob;
        }

   

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }

/**
 * Handle foreign key state through `proposal`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for proposal.
 * @phpstan-return void
 * @psalm-return void
 * @see self::proposal()
 * @example /fr/foreignkey/proposal
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function proposal($param)
    {

        $data = array();


        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];
        $database = $param[1];

        $sql = "SELECT * FROM foreign_key_proposal WHERE id_mysql_server = ".$id_mysql_server." 
        AND (constraint_schema ='".$database."' OR referenced_schema ='".$database."') 
        ORDER BY id_mysql_server, constraint_schema,constraint_table, constraint_column";
        
        $res = $db->sql_query($sql);

        $data['fk'] = array();
        
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['fk'][] = $ob;
        }

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }


/**
 * Handle foreign key state through `blackList`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for blackList.
 * @phpstan-return void
 * @psalm-return void
 * @see self::blackList()
 * @example /fr/foreignkey/blackList
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function blackList($param)
    {
        $data = array();


        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];
        $database = $param[1];

        $sql = "SELECT * FROM foreign_key_blacklist WHERE id_mysql_server = ".$id_mysql_server." 
        AND (constraint_schema ='".$database."' OR referenced_schema ='".$database."') 
        ORDER BY id_mysql_server, constraint_schema,constraint_table, constraint_column";
        
        $res = $db->sql_query($sql);

        $data['fk'] = array();
        
        while ($ob          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['fk'][] = $ob;
        }

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }

/**
 * Handle foreign key state through `custom`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for custom.
 * @phpstan-return void
 * @psalm-return void
 * @see self::custom()
 * @example /fr/foreignkey/custom
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function custom($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);


    }

}
