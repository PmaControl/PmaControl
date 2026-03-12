<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Shell\Color;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;

//use \App\Library\System;

/**
 * Class responsible for b i workflows.
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
class BI extends Controller
{
/**
 * Stores `$server` for server.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $server = array(3, 24, 33, 173, 167, 21, 157);

    const DATABASE = 'spider';

/**
 * Handle b i state through `searchField`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for searchField.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::searchField()
 * @example /fr/bi/searchField
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function searchField($param)
    {

        Debug::parseDebug($param);

        $field       = $param[0];
        $environment = $param[1] ?? '';

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id,a.ip,a.port,a.display_name  FROM mysql_server a
            INNER JOIN environment b ON b.id = a.id_environment
            WHERE is_available=1 and is_proxy=0 and error = ''";

        if (!empty($environment)) {
            $sql .= " AND b.Libelle='".$environment."'";
        }

        $res = $db->sql_query($sql);

        $data           = array();
        $data['server'] = array();

        $i  = 0;
        while ($ob = $db->sql_fetch_object($res)) {


            $link  = Mysql::getDbLink($ob->id);
            $field = $param[0];

            $sql3      = "SELECT @@global.read_only as read_only;";
            $res3      = $link->sql_query($sql3);
            $read_only = 0;
            while ($ob3       = $link->sql_fetch_object($res3)) {

                $read_only = $ob3->read_only;
            }

            if ($read_only == 1) {
                continue;
            }


            $sql2 = "SELECT TABLE_SCHEMA as TABLE_SCHEMA, TABLE_NAME as TABLE_NAME, COLUMN_NAME as COLUMN_NAME, COLUMN_TYPE as COLUMN_TYPE "
                ."FROM information_schema.COLUMNS WHERE COLUMN_NAME = '".$field."'";

            $res2 = $link->sql_query($sql2);

            while ($ob2 = $link->sql_fetch_object($res2)) {
                $i++;

                if (empty($data['field'][$ob2->TABLE_SCHEMA][$ob2->TABLE_NAME])) {
                    $data['field'][$ob2->TABLE_SCHEMA][$ob2->TABLE_NAME] = array();
                }
                $data['field'][$ob2->TABLE_SCHEMA][$ob2->TABLE_NAME][] = $ob->id;

                if (empty($data['server'][$ob->id][$ob2->TABLE_SCHEMA])) {

                    $data['server'][$ob->id][$ob2->TABLE_SCHEMA] = 1;
                } else {
                    $data['server'][$ob->id][$ob2->TABLE_SCHEMA]++;
                }

//echo $i."\t".$ob->display_name."\t".$ob->ip.":".$ob->port."\t".$ob2->TABLE_SCHEMA." ".$ob2->TABLE_NAME."\n";
            }
        }

//$data['server'] = array_unique($data['server']);

        Debug::debug($data['server'], "result");
        return $data;
    }

/**
 * Create b i state through `createServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for createServer.
 * @phpstan-return void
 * @psalm-return void
 * @see self::createServer()
 * @example /fr/bi/createServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createServer($param)
    {

        Debug::parseDebug($param);

        $servers = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($servers as $id_mysql_server => $databases) {

            $account = Mysql::createSelectAccount(array($id_mysql_server, 'spider'));

            $info_server = Mysql::getInfoServer(array($id_mysql_server));

            foreach ($databases as $database => $one) {

                $backend = $this->getBackend(array($id_mysql_server, $database));

                $sql = "CREATE OR REPLACE SERVER ".$backend." FOREIGN DATA WRAPPER mysql OPTIONS "
                    ."(host '".$info_server['ip']."', database '".$database."', user '".$account['user']."', password '".$account['password']."', port ".$info_server['port'].");";
                Debug::debug($sql);
                $db->sql_query($sql);
            }
        }
    }

/**
 * Handle b i state through `rapport`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for rapport.
 * @phpstan-return void
 * @psalm-return void
 * @see self::rapport()
 * @example /fr/bi/rapport
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function rapport($param)
    {
        Debug::parseDebug($param);

        $data = $this->searchField(array("pad"));

        $this->createServer(array($data['server']));

        $this->createTableSpider(array($data['field']));
    }

/**
 * Create b i state through `createTableSpider`.
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
 * @example /fr/bi/createTableSpider
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

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "CREATE DATABASE IF NOT EXISTS `".self::DATABASE."`";

        $db->sql_query($sql);

        $fields = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($fields as $database => $tables) {
            foreach ($tables as $table => $servers) {
                foreach ($servers as $id_mysql_server) {

                    $backend = $this->getBackend(array($id_mysql_server, $database));

                    $create_table = $this->getCreateTable(array($id_mysql_server, $database, $table));

                    $table_spider = $this->changeToSpider(array($create_table, $backend, $table));

                    Debug::debug($table_spider, "TABLE SPIDER");
                }
            }
        }
    }

/**
 * Retrieve b i state through `getBackend`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getBackend.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getBackend()
 * @example /fr/bi/getBackend
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getBackend($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $server_name = "backend_".$id_mysql_server."_".$database;

        return $server_name;
    }

/**
 * Retrieve b i state through `getCreateTable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getCreateTable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getCreateTable()
 * @example /fr/bi/getCreateTable
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getCreateTable($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];
        $table           = $param[2];

        $link = Mysql::getDbLink($id_mysql_server);

        $sql = "SHOW CREATE TABLE `".$database."`.`".$table."`;";

        $res = $link->sql_query($sql);

        while ($arr = $link->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $create_table = $arr['Create Table'];
        }

        Debug::debug($create_table, "CREATE TABLE");

        return $create_table;
    }

/**
 * Handle b i state through `changeToSpider`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for changeToSpider.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::changeToSpider()
 * @example /fr/bi/changeToSpider
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function changeToSpider($param)
    {
        Debug::parseDebug($param);

        $create_table = $param[0];
        $backend      = $param[1];
        $table        = $param[2];

        strstr($create_table, "ENGINE=", true);

        $engine = 'ENGINE=SPIDER COMMENT=\'wrapper "mysql", srv "'.$backend.'", table "'.$table.'"\'';

        $table_spider = $create_table.$engine;

        Debug::debug($table_spider, "TABLE SPIDER");

        return $table_spider;
    }
}
/*
 *
 *
CREATE OR REPLACE TABLE spider.sbtest1 ENGINE=Spider COMMENT='wrapper "mysql", srv "backend_15_sbtest", table "sbtest1"';
 */
