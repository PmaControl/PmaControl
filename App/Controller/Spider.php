<?php

namespace App\Controller;

use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;

/**
 * Class responsible for spider workflows.
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
class Spider extends Controller {

    //dba_source


    use \App\Library\Filter;

/**
 * Render spider state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/spider/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        $this->title = '<img src="/pmacontrol/image/main/spider-icon32.png" height="16" width="16px">' . "Spider";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > ' . $this->title;

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_database";
    }

    /*
     * add restriction for MySQL & MariaDB < 5.5 (Spider not supported)
     */

    public function Server($param) {

        $id_mysql_server = $param[0];

        $db = $this->getServerLink($id_mysql_server);
        $sql = "SELECT TABLE_NAME,TABLE_SCHEMA FROM information_schema.TABLES where ENGINE = 'Spider';";

        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);


        // test if a table with spider exit
        $data['no_spider'] = 0;
        if ($db->sql_num_rows($res) == 0) {
            $data['no_spider'] = 1;
        }

        if ($data['no_spider'] === 0) {
            while ($ob = $db->sql_fetch_object($res)) {
                $sql = "SHOW CREATE TABLE `" . $ob->TABLE_SCHEMA . "`.`" . $ob->TABLE_NAME . "`;";
                $res2 = $db->sql_query($sql);
                $tab = $db->sql_fetch_array($res2, MYSQLI_ASSOC);

                $data['spider']['server_id'][$ob->TABLE_SCHEMA][$ob->TABLE_NAME] = $this->extractSpiderInfoFromCreateTable($tab['Create Table']);
            }
        }
        $this->set('data', $data);
    }

    /*
      Test if engine Spider exit on MySQL server else give solution to install it
     */

    public function testIfSpiderExist($param) {
        $id_mysql_server = $param[0];
        $db = $this->getServerLink($id_mysql_server);
        $sql = 'SELECT count(1) as cpt FROM information_schema.engines where engine = "spider";';

        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $cpt = $ob->cpt;
        }

        $data['spider_activated'] = 0;
        
        if ($cpt === "1") {
            $data['spider_activated'] = 1;
        }

        $this->set('data', $data);
    }

    /*
      return MySQL link from id_mysql_server
     */

    private function getServerLink($id_mysql_server) {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server where id = '" . $id_mysql_server . "'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $name_id = $ob->name;
        }
        return Sgbd::sql($name_id);
    }

/**
 * Handle spider state through `extractSpiderInfoFromCreateTable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $createTable Input value for `createTable`.
 * @phpstan-param mixed $createTable
 * @psalm-param mixed $createTable
 * @return mixed Returned value for extractSpiderInfoFromCreateTable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::extractSpiderInfoFromCreateTable()
 * @example /fr/spider/extractSpiderInfoFromCreateTable
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function extractSpiderInfoFromCreateTable($createTable) {
        $comment = stristr($createTable, 'COMMENT=');
        $main = substr($comment, 8, 1);
        $tmp = [];
        preg_match("@COMMENT=\\" . $main . "(.*)\\" . $main . "@", $comment, $output_array);
        $resultats = $output_array[1];
        $results = explode(',', $resultats);

        foreach ($results as $result) {
            $result = trim($result);
            $elem = explode(' ', $result);

            $tmp['connection'][$elem[0]] = substr($elem[1], 1, -1);
        }

        return $tmp;
    }

/**
 * Create spider state through `create`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for create.
 * @phpstan-return void
 * @psalm-return void
 * @see self::create()
 * @example /fr/spider/create
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function create() {

        $db = Sgbd::sql(DB_DEFAULT);


        $this->di['js']->addJavascript(array("jquery-latest.min.js", "bootstrap-select.min.js", "spider/create.js"));


        $data['databases'] = array();
        if (!empty($_GET['spider']['database'])) {
            $select1 = $this->getDatabaseByServer(array($_GET['mysql_server']['id']));
            $data['databases'] = $select1['databases'];
        }



        $data['tables'] = array();
        if (!empty($_GET['spider']['tables'])) {
            $select1 = $this->getTableByServerAndDatabase(array($_GET['mysql_server']['id'], $_GET['spider']['database'],));
            $data['tables'] = $select1['tables'];
        }



        $this->set('data', $data);
    }

/**
 * Create spider state through `addLinkDb`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for addLinkDb.
 * @phpstan-return void
 * @psalm-return void
 * @see self::addLinkDb()
 * @example /fr/spider/addLinkDb
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function addLinkDb($param) {
        $id_mysql_source = $param[0];
        $database_source = $param[1];
        $id_mysql_destination = $param[2];
        $database_destination = $param[3];
        
        
        
        
    }

    /*
     * Ne pas savoir  n'a jamais servi personne.
     * 
     */



}
