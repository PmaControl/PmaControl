<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for covage workflows.
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
class Covage extends Controller
{
    /*
     * apt install mariadb-plugin-spider
     *
     * check with :
     * mysql> SHOW ENGINES;
     *  +--------------------+---------+-------------------------------------------------------------------------------------------------+--------------+------+------------+
      | Engine             | Support | Comment                                                                                         | Transactions | XA   | Savepoints |
      +--------------------+---------+-------------------------------------------------------------------------------------------------+--------------+------+------------+
      ...
      | SPIDER             | YES     | Spider storage engine                                                                           | YES          | YES  | NO         |
      +--------------------+---------+-------------------------------------------------------------------------------------------------+--------------+------+------------+

     * creation backend :
     * create server fiber_qualif2 foreign data wrapper mysql options (host '127.0.0.1', database 'integration', user 'pmacontrol', password '****', port 3306);
     * create server fiber_prod foreign data wrapper mysql options (host '127.0.0.2', database 'mydb', user 'pmacontrol', password '*****', port 3306);
     *
     *
     *  MariaDB [mysql]> select * from mysql.servers;
      +---------------+-----------+-------------+------------+--------------+------+--------+---------+-------+
      | Server_name   | Host      | Db          | Username   | Password     | Port | Socket | Wrapper | Owner |
      +---------------+-----------+-------------+------------+--------------+------+--------+---------+-------+
      | fiber_qualif2 | 127.0.0.1 | integration | pmacontrol | ************ | 3306 |        | mysql   |       |
      | fiber_prod    | 127.0.0.2 | mydb        | pmacontrol | ************ | 3306 |        | mysql   |       |
      +---------------+-----------+-------------+------------+--------------+------+--------+---------+-------+
      2 rows in set (0.001 sec)
     *
     *
     */


    /* bug
     *
     *
     * [2019-10-04 16:34:51] SQL : SHOW INDEX FROM `reprise`.`NotifReprovisionning_fiber_prod` WHERE `Key_name` ='PRIMARY';
      Error (1054) : Unknown column 'NA' in 'DEFAULT'


      [2019-10-04 16:31:05] SQL : SHOW INDEX FROM `reprise`.`flux_commande_acces_fiber_prod` WHERE `Key_name` ='PRIMARY';
      Error (1054) : Unknown column 'ACCES_FTTH' in 'DEFAULT'
     */





    /*
     * Liste des backends
     */
    var $source          = 'fiber_qualif2';
/**
 * Stores `$destination` for destination.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $destination     = 'fiber_prod';
/**
 * Stores `$base_de_travail` for base de travail.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    var $base_de_travail = 'reprise';

    /*
      var $tables          = array('commande_services', 'otelligibilite', 'histo_etape_commande_service',
      'ArCommandeAcces', 'CrCommandeAcces', 'CmdStoc', 'CrStoc', 'NotifRaccoKo',
      'CrmadAcces', 'crmes', 'CrAnnulationCommandeAcces', 'AnnulationCommandeAcces',
      'service_passif', 'NotifEcrasement', 'NotifReprovisionning', 'flux_commande_acces');
     */
    var $tables = array('CrStoc');
    //  NotifReprovisionning  flux_commande_acces

/**
 * Stores `$primary_key` for primary key.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $primary_key    = array('');
/**
 * Stores `$diff` for diff.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $diff           = array('');
/**
 * Stores `$table_rename` for table rename.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $table_rename   = array('action', 'cassette', 'cassette_modele', 'cassette_modele_equipement_modele', 'champs', 'commande_acces', 'constructeur', 'corbeille', 'couleur',
        'couleur_table_couleur', 'droit', 'emplacement_cassette', 'enregistrement', 'etape', 'extraction', 'fibre', 'HistoriqueOntEligibilite', 'ipe', 'lien_optique', 'lien_route_optique',
        'objet', 'OntEligibilite', 'otelligibiliteservice', 'parametres', 'planning', 'port', 'reporting', 'reporting_data', 'reseau_users',
        'service_corbeille', 'table_couleur', 'type_cable', 'type_erreur', 'type_lien_optique', 'valeur', 'type_planning');
/**
 * Stores `$table_rollback` for table rollback.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $table_rollback = array('task');

/**
 * Handle covage state through `creationTableSpider`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for creationTableSpider.
 * @phpstan-return void
 * @psalm-return void
 * @see self::creationTableSpider()
 * @example /fr/covage/creationTableSpider
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function creationTableSpider()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($this->getEnvs() as $env) {
            foreach ($this->tables as $table) {
                $sql = 'create table '.$table.'_'.$env.' engine=spider '
                    .'comment=\'wrapper "mysql", srv "'.$env.'", table "'.$table.'"\';';
                $db->sql_query($sql);
            }
        }
    }
    /*
      public function before($param): void
      {
      $db = Sgbd::sql(DB_DEFAULT);
      $db->sql_query("CREATE DATABASE IF NOT EXISTS `".$this->base_de_travail."`;");
      $db->sql_select_db($this->base_de_travail);
      } */

    public function getEnvs()
    {
        return array($this->source, $this->destination);
    }

/**
 * Handle covage state through `all`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for all.
 * @phpstan-return void
 * @psalm-return void
 * @see self::all()
 * @example /fr/covage/all
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function all($param)
    {

        Debug::parseDebug($param);
        $this->drop();
        $this->before(array());
        $this->creationTableSpider();

        $this->saveRef(array());

        $db = Sgbd::sql(DB_DEFAULT);
        Debug::debugShowQueries($db);
    }

/**
 * Handle covage state through `drop`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for drop.
 * @phpstan-return void
 * @psalm-return void
 * @see self::drop()
 * @example /fr/covage/drop
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function drop()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "DROP DATABASE IF EXISTS `".$this->base_de_travail."`;";
        $db->sql_query($sql);
        Debug::sql($sql);
    }

/**
 * Handle covage state through `diff`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for diff.
 * @phpstan-return void
 * @psalm-return void
 * @see self::diff()
 * @example /fr/covage/diff
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function diff($param)
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($this->tables as $table) {

            $pk = $this->getPrimaryKey($this->base_de_travail, $table.'_'.$this->destination);

            Debug::debug($pk);


            $primary_key = $pk[0];

            Debug::debug($primary_key);


            $sql = 'SELECT count(1) as cpt FROM `'.$table.'_'.$this->source.'` a '
                .'LEFT JOIN `'.$table.'_'.$this->destination.'` b ON a.`'.$primary_key.'` = b.`'.$primary_key.'` '
                .'WHERE b.`'.$primary_key.'` IS NULL';

            Debug::sql($sql);


            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $this->diff[$table] = $ob->cpt;
            }
        }


        print_r($this->diff);
    }

/**
 * Retrieve covage state through `getPrimaryKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @return mixed Returned value for getPrimaryKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getPrimaryKey()
 * @example /fr/covage/getPrimaryKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getPrimaryKey($database, $table)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        if (empty($this->primary_key[$database][$table])) {

            $sql = "SHOW INDEX FROM `".$database."`.`".$table."` WHERE `Key_name` ='PRIMARY';";
            $res = $db->sql_query($sql);

            if ($db->sql_num_rows($res) == "0") {
                throw new \Exception("PMACTRL-067 : this table '".$table."' haven't primary key !");
            } else {

                $index = array();

                while ($ob = $db->sql_fetch_object($res)) {
                    $this->primary_key[$database][$table][] = $ob->Column_name;
                }
            }
        }

        return $this->primary_key[$database][$table];
    }

/**
 * Handle covage state through `reprise`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for reprise.
 * @phpstan-return void
 * @psalm-return void
 * @see self::reprise()
 * @example /fr/covage/reprise
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function reprise($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($this->tables as $table) {

            $pk = $this->getPrimaryKey($table.'_'.$this->destination, $this->base_de_travail);

            $primary_key = $pk[0];


            $sql = 'INSERT INTO `'.$table.'_'.$this->destination.'`
                SELECT a.* FROM `'.$table.'_'.$this->source.'` a
               LEFT JOIN `'.$table.'_'.$this->destination.'` b ON ON a.`'.$primary_key.'` = b.`'.$primary_key.'`
                WHEERE b.`'.$primary_key.'` IS NULL;
                ';


            Debug::sql($sql);
        }
    }

/**
 * Update covage state through `saveRef`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for saveRef.
 * @phpstan-return void
 * @psalm-return void
 * @see self::saveRef()
 * @example /fr/covage/saveRef
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function saveRef()
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($this->tables as $table) {



            $sql = 'DROP TABLE IF EXISTS `'.$table.'`';
            $db->sql_query($sql);

            $sql = 'Create table `'.$table.'` LIKE '.$table.'_'.$this->destination.';';
            $db->sql_query($sql);


            $pk          = $this->getPrimaryKey($this->base_de_travail, $table.'_'.$this->destination);
            $primary_key = $pk[0];

            $all_fields = $this->getFields($this->base_de_travail, $table.'_'.$this->source);

            $fields  = "a.`".implode('`, a.`', $all_fields)."`";
            $libelle = implode(',', $all_fields);

            $sql = "INSERT INTO ".$table." (".$libelle.")
                SELECT ".$fields." "
                ."FROM `".$table.'_'.$this->source.'` a '
                .'LEFT JOIN `'.$table.'_'.$this->destination.'` b ON a.`'.$primary_key.'` = b.`'.$primary_key.'` '
                .'WHERE b.`'.$primary_key.'` IS NULL';


            Debug::sql($sql);
            $db->sql_query($sql);
        }
    }

/**
 * Retrieve covage state through `getFields`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @return mixed Returned value for getFields.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getFields()
 * @example /fr/covage/getFields
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getFields($database, $table)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT COLUMN_NAME as colonne
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = '".$database."' AND TABLE_NAME = '".$table."';";

        Debug::sql($sql);

        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);

        $fields = array();
        while ($ob     = $db->sql_fetch_object($res)) {
            $fields[] = $ob->colonne;
        }

        return $fields;
    }

/**
 * Create covage state through `createTableSpider`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for createTableSpider.
 * @phpstan-return void
 * @psalm-return void
 * @see self::createTableSpider()
 * @example /fr/covage/createTableSpider
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createTableSpider($param)
    {

        Debug::parseDebug($param);

        $backend  = $param[0];
        $database = $param[1];
        $table    = $param[2];


        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = 'create table `'.$database.'`.`'.$table.'` engine=spider '
            .'comment=\'wrapper "mysql", srv "'.$backend.'", table "'.$table.'"\';';

        Debug::sql($sql);
        $db->sql_query($sql);
    }

/**
 * Create covage state through `createDbLink`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for createDbLink.
 * @phpstan-return void
 * @psalm-return void
 * @see self::createDbLink()
 * @example /fr/covage/createDbLink
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createDbLink($param)
    {

        Debug::parseDebug($param);


        $backend              = $param[0];
        $id_mysql_destination = $param[1];
        $database_destination = $param[2];

        $db         = Sgbd::sql(DB_DEFAULT);
        $name       = Mysql::getDbLink($db, $id_mysql_destination);
        $remote_dst = Sgbd::sql($name);



        Debug::debug($param);


        $host = $this->getInfoFromBackend($backend, $id_mysql_destination);

        Debug::debug($host);

        $sql = "select * from mysql_server where ip='".$host['Host']."' and port='".$host['Port']."'";
        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {

            $remote_src = Sgbd::sql($ob->name);

            $remote_src->sql_select_db($host['Db']);
            $tables = $remote_src->getListTable()['table'];


            Debug::debug($tables);

            foreach ($tables as $table) {
                $this->createTableSpider(array($backend, $database_destination, $table));
                sleep(1);
            }
        }
    }

/**
 * Retrieve covage state through `getInfoFromBackend`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $backend Input value for `backend`.
 * @phpstan-param mixed $backend
 * @psalm-param mixed $backend
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for getInfoFromBackend.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getInfoFromBackend()
 * @example /fr/covage/getInfoFromBackend
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getInfoFromBackend($backend, $id_mysql_server)
    {

        $db     = Sgbd::sql(DB_DEFAULT);
        $name   = Mysql::getDbLink($db, $id_mysql_server);
        $remote = Sgbd::sql($name);

        $sql = "select * from mysql.servers where Server_name='".$backend."';";
        Debug::debug($sql);


        $res = $remote->sql_query($sql);

        while ($arr = $remote->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return $arr;
        }


        throw new \Exception("PMACTRL-581 : Impossible to find this backend : '".$backend."'");
    }

/**
 * Handle covage state through `toRename`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for toRename.
 * @phpstan-return void
 * @psalm-return void
 * @see self::toRename()
 * @example /fr/covage/toRename
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function toRename($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];


        $name = Mysql::getDbLink(Sgbd::sql(DB_DEFAULT), $id_mysql_server);
        $db   = Sgbd::sql($name);



        foreach ($this->table_rename as $table) {

            $sql = "RENAME TABLE `".$database."`.`".$table."` TO `".$database."`.`zzz_".$table."`;";
            Debug::sql($sql);

            $db->sql_query($sql);
        }
    }

/**
 * Handle covage state through `toRenameRollback`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for toRenameRollback.
 * @phpstan-return void
 * @psalm-return void
 * @see self::toRenameRollback()
 * @example /fr/covage/toRenameRollback
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function toRenameRollback($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $name = Mysql::getDbLink(Sgbd::sql(DB_DEFAULT), $id_mysql_server);
        $db   = Sgbd::sql($name);

        foreach ($this->table_rollback as $table) {

            $sql = "RENAME TABLE `".$database."`.`zzz_".$table."` TO `".$database."`.`".$table."`;";
            Debug::sql($sql);

            $db->sql_query($sql);
        }
    }

/**
 * Handle covage state through `convertToUtf8`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for convertToUtf8.
 * @phpstan-return void
 * @psalm-return void
 * @see self::convertToUtf8()
 * @example /fr/covage/convertToUtf8
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function convertToUtf8($param)
    {

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);


        $sql = "SELECT concat('ALTER TABLE ',table_schema,'.',table_name,' CONVERT TO CHARACTER SET utf8 collate utf8_unicode_ci;') as gg, table_collation , table_schema, table_name "
            ."FROM information_schema.tables where table_schema NOT IN ('mysql','information_schema', 'performance_schema') "
            ."and TABLE_TYPE='BASE TABLE' and TABLE_COLLATION != 'utf8_unicode_ci' "
            ."order by TABLE_SCHEMA, TABLE_COLLATION";


        $res = $db->sql_query($sql);

        $i  = 0;
        while ($ob = $db->sql_fetch_object($res)) {
            //echo "[".$ob->table_collation."]\t";
            $i++;
            echo "SELECT '[#".$i." ".$ob->table_collation."] ".$ob->table_schema.".".$ob->table_name."';\n";
            echo $ob->gg."\n";
        }
    }
}
