<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Mysql;

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
    var $destination     = 'fiber_prod';
    var $base_de_travail = 'reprise';

    /*
      var $tables          = array('commande_services', 'otelligibilite', 'histo_etape_commande_service',
      'ArCommandeAcces', 'CrCommandeAcces', 'CmdStoc', 'CrStoc', 'NotifRaccoKo',
      'CrmadAcces', 'crmes', 'CrAnnulationCommandeAcces', 'AnnulationCommandeAcces',
      'service_passif', 'NotifEcrasement', 'NotifReprovisionning', 'flux_commande_acces');
     */
    var $tables = array('CrStoc');
    //  NotifReprovisionning  flux_commande_acces

    var $primary_key  = array('');
    var $diff         = array('');
    var $table_rename = array('action','cassette','cassette_modele','cassette_modele_equipement_modele','champs','commande_acces','constructeur','corbeille','couleur',
        'couleur_table_couleur','droit','emplacement_cassette','enregistrement','etape','extraction','fibre','HistoriqueOntEligibilite','ipe','lien_optique','lien_route_optique',
        'objet','OntEligibilite','otelligibiliteservice','parametres','planning','port','port_pto','reporting','reporting_data','reseau_users',
        'service_corbeille','table_couleur','type_cable','type_erreur','type_lien_optique','valeur');

    var $table_rollback = array('task');
    
    public function creationTableSpider()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

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
      $db = $this->di['db']->sql(DB_DEFAULT);
      $db->sql_query("CREATE DATABASE IF NOT EXISTS `".$this->base_de_travail."`;");
      $db->sql_select_db($this->base_de_travail);
      } */

    public function getEnvs()
    {
        return array($this->source, $this->destination);
    }

    public function all($param)
    {

        Debug::parseDebug($param);
        $this->drop();
        $this->before(array());
        $this->creationTableSpider();

        $this->saveRef(array());

        $db = $this->di['db']->sql(DB_DEFAULT);
        Debug::debugShowQueries($db);
    }

    public function drop()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "DROP DATABASE IF EXISTS `".$this->base_de_travail."`;";
        $db->sql_query($sql);
        Debug::sql($sql);
    }

    public function diff($param)
    {

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

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

    public function getPrimaryKey($database, $table)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

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

    public function reprise($param)
    {
        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

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

    public function saveRef()
    {

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

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

    public function getFields($database, $table)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT COLUMN_NAME as colonne
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = '".$database."' AND TABLE_NAME = '".$table."';";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $fields = array();
        while ($ob     = $db->sql_fetch_object($res)) {
            $fields[] = $ob->colonne;
        }

        return $fields;
    }

    public function createTableSpider($param)
    {

        Debug::parseDebug($param);

        $backend  = $param[0];
        $database = $param[1];
        $table    = $param[2];


        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = 'create table `'.$database.'`.`'.$table.'` engine=spider '
            .'comment=\'wrapper "mysql", srv "'.$backend.'", table "'.$table.'"\';';

        Debug::sql($sql);
        $db->sql_query($sql);
    }

    public function createDbLink($param)
    {

        Debug::parseDebug($param);


        $backend              = $param[0];
        $id_mysql_destination = $param[1];
        $database_destination = $param[2];

        $db         = $this->di['db']->sql(DB_DEFAULT);
        $name       = Mysql::getDbLink($db, $id_mysql_destination);
        $remote_dst = $this->di['db']->sql($name);



        Debug::debug($param);


        $host = $this->getInfoFromBackend($backend, $id_mysql_destination);

        Debug::debug($host);

        $sql = "select * from mysql_server where ip='".$host['Host']."' and port='".$host['Port']."'";
        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {

            $remote_src = $this->di['db']->sql($ob->name);

            $remote_src->sql_select_db($host['Db']);
            $tables = $remote_src->getListTable()['table'];


            Debug::debug($tables);

            foreach ($tables as $table) {
                $this->createTableSpider(array($backend, $database_destination, $table));
                sleep(1);
            }
        }
    }

    public function getInfoFromBackend($backend, $id_mysql_server)
    {

        $db     = $this->di['db']->sql(DB_DEFAULT);
        $name   = Mysql::getDbLink($db, $id_mysql_server);
        $remote = $this->di['db']->sql($name);

        $sql = "select * from mysql.servers where Server_name='".$backend."';";
        Debug::debug($sql);


        $res = $remote->sql_query($sql);

        while ($arr = $remote->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return $arr;
        }


        throw new \Exception("PMACTRL-581 : Impossible to find this backend : '".$backend."'");
    }

    public function toRename($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];


        $name = Mysql::getDbLink($this->di['db']->sql(DB_DEFAULT), $id_mysql_server);
        $db   = $this->di['db']->sql($name);



        foreach ($this->table_rename as $table) {

            $sql = "RENAME TABLE `".$database."`.`".$table."` TO `".$database."`.`zzz_".$table."`;";
            Debug::sql($sql);

            $db->sql_query($sql);
        }
    }

    public function toRenameRollback($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $name = Mysql::getDbLink($this->di['db']->sql(DB_DEFAULT), $id_mysql_server);
        $db   = $this->di['db']->sql($name);

        foreach ($this->table_rollback as $table) {

            $sql = "RENAME TABLE `".$database."`.`zzz_".$table."` TO `".$database."`.`".$table."`;";
            Debug::sql($sql);

            $db->sql_query($sql);
        }
    }
}