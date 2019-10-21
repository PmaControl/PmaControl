<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;

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
    
    var $tables          = array( 'CrStoc');
    //  NotifReprovisionning  flux_commande_acces

    var $primary_key = array('');
    var $diff        = array('');

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

    public function before($param): void
    {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $db->sql_query("CREATE DATABASE IF NOT EXISTS `".$this->base_de_travail."`;");
        $db->sql_select_db($this->base_de_travail);
    }

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

            $fields = "a.`".implode('`, a.`', $all_fields)."`";
            $libelle = implode(',', $all_fields);

            $sql = "INSERT INTO ".$table." (".$libelle.")
                SELECT ".$fields." "
                . "FROM `".$table.'_'.$this->source.'` a '
                .'LEFT JOIN `'.$table.'_'.$this->destination.'` b ON a.`'.$primary_key.'` = b.`'.$primary_key.'` '
                .'WHERE b.`'.$primary_key.'` IS NULL';


            Debug::sql($sql);
            $db->sql_query($sql);
        }
    }

    public function getFields($database, $table)
    {
        Debug::parseDebug($param);

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
}