<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Mysql;
use App\Library\MysqlServer;
use App\Library\Security\ServerIdSelection;
use App\Library\SelectorOptions;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;

//version de 14 octobre 2020, comparaison de tableau multidimensions avec agregation des entetes
//ne servait a rien ici car chaque serveur au moins un element different

/**
 * Class responsible for check config workflows.
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
class CheckConfig extends Controller
{
/**
 * Stores `$should_be_different` for should be different.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $should_be_different = array("server_id", "report_host", "wsrep_node_name");
/**
 * Stores `$not_important` for not important.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $not_important       = array("general_log_file", "gtid_binlog_state");
/**
 * Stores `$master_master` for master master.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $master_master       = array("");
/**
 * Stores `$human_readable` for human readable.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $human_readable      = array("aria_log_file_size", "aria_pagecache_buffer_size", "aria_max_sort_file_size", "binlog_cache_size", "innodb_buffer_pool_size", "innodb_log_file_size", "max_heap_table_size",
        "query_cache_size",
        "tmp_memory_table_size", "tmp_table_size");

/**
 * Render check config state through `index`.
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
 * @example /fr/checkconfig/index
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

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $this->di['js']->code_javascript('
            $( ".showdiff" ).click(function() {
                $(".to_hide").toggleClass("hide")
            });
            $(\'[data-toggle="tooltip"]\').tooltip();
       ');

        Debug::parseDebug($param);

        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] !== 200) {
            $this->view = false;
            $this->layout_name = false;
            self::sendIndexError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
            return;
        }

        $selection = $indexRequest['selection'];
        self::applyIndexSelectionToGet($selection);

        $db = Sgbd::sql(DB_DEFAULT);

        if ($selection['ids'] !== []) {
                $sql = "SELECT * FROM mysql_server WHERE id in (".$selection['id_list'].")";
                $id_mysql_servers = $selection['ids'];

                $res = $db->sql_query($sql);

                $is_available = 0;
                
                //debug($_GET['mysql_cluster']);
                //debug($_GET['mysql_server']);

                $mysql_servers = $selection['ids'];

                
                $available = Extraction::display(array("mysql_available"), $mysql_servers);
                
                $is_available = end($available)['']['mysql_available'];

                $server_note_available = array();
                while ($ob                    = $db->sql_fetch_object($res)) {

                    if ($is_available !== "1") {
                        $server_note_available[] = $ob->id;
                        $cache                   = " <i>[cache]</i>";
                    } else {
                        $cache = "";
                    }

                    $data['mysql_server'][$ob->id] = $ob->display_name." (".$ob->ip.")".$cache;
                }

                $resultat = array();
                $alone = false;

                if (count($id_mysql_servers) === 1 && !in_array($id_mysql_servers[0], $server_note_available)) {
                    $alone                    = true;
                    $id_mysql_servers[]       = -1;
                    $data['mysql_server'][-1] = "(cache)";
                }

                //$id_mysql_servers = explode(",", $_GET['mysql_cluster']['id']);
                $data['show'] = false;

                //debug($id_mysql_servers);
                foreach ($id_mysql_servers as $id_server) {

                    $step1 = false;
                    $step2 = false;

                    $elems = array();

                    if ($id_server === -1) {
                        $id_mysql_server = max($id_mysql_servers);
                    } else {
                        $id_mysql_server = $id_server;
                    }

                    if (!in_array($id_mysql_server, $server_note_available) && $id_server !== -1) {

                        //debug("current");
                        $db_link = $this->getDbLinkFromId($id_mysql_server);
                        $res     = $db_link->sql_query("SHOW GLOBAL VARIABLES;");

                        while ($arr = $db_link->sql_fetch_array($res, MYSQLI_ASSOC)) {

                            $tmp                  = array();
                            $tmp['Variable_name'] = strtolower($arr['Variable_name']);
                            $tmp['Value']         = $arr['Value'];
                            $elems[]              = $tmp;
                        }

                        $step1 = true;
                    }

                    //in case server is not available we looking for in cache
                    if (in_array($id_mysql_server, $server_note_available) || ($alone === true && $step1 === false)) {

                        $sql = "SELECT variable_name as Variable_name, value as Value 
                        FROM global_variable WHERE id_mysql_server=".$id_mysql_server." ORDER BY 1;";

                        $res = $db->sql_query($sql);
                        //debug($sql);
                        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

                            $tmp                  = array();
                            $tmp['Variable_name'] = strtolower($arr['Variable_name']);
                            $tmp['Value']         = $arr['Value'];
                            $elems[]              = $tmp;
                        }

                        //debug($elems);
                    }

                    //$id_number = 1;
                    //$resultat =$this->fetchData($elems, $id_mysql_server, $id_number);

                    foreach ($elems as $arr) {
                        $nb = count($arr);
                        if ($nb == 2) {
                            if (!empty($arr['Variable_name']) && isset($arr['Value'])) {
                                $show = true;
                            } else {
                                $show = false;
                            }
                        } else {
                            $show = false;
                        }

                        if ($show) {
                            if (in_array($arr['Variable_name'], array("wsrep_provider_options", "optimizer_switch"))) {

                                //$resultat[$id_mysql_server][$arr['Variable_name']] = $arr['Value'];

                                switch ($arr['Variable_name']) {
                                    case 'wsrep_provider_options': $delimiter = ';';
                                        break;
                                    case 'optimizer_switch': $delimiter = ',';
                                        break;
                                }

                                $vals = explode($delimiter, trim($arr['Value'], ";"));
                                array_pop($vals); // remove last ; (empty)

                                foreach ($vals as $val) {
                                    $varval = explode("=", $val);

                                    $sous_variable = trim($varval[0]);
                                    $sous_value    = trim($varval[1]);

                                    if (!isset($varval[1])) {
                                        debug($varval);
                                    }

                                    $resultat[$id_server][$arr['Variable_name']."<i>__".$sous_variable."</i>"] = $sous_value;
                                }
                            } else {
                                $resultat[$id_server][$arr['Variable_name']] = $arr['Value'];
                                $data['show']                                = true;
                            }
                        } else {
                            //debug($nb);
                            $resultat[$id_server][] = $arr;
                        }
                    } /**/
                }

                //if ($data['show']) {

                $index = array();
                foreach ($resultat as $res) {
                    $index = array_merge($res, $index);
                }

                //debug($index);
                $data['index'] = array_keys($index);
                //sort($data['index']);

                $data['resultat'] = $resultat;

                //debug($data);
        }

        //generate liste of cluster (for select)
        /*
        $sql = "select group_concat(a.id_mysql_server) as id_mysql_servers, group_concat(b.display_name) as display_name
            from link__architecture__mysql_server a
            INNER JOIN mysql_server b ON a.id_mysql_server= b.id
            group by id_architecture having count(1) > 1;";
        */

        //le max(id)-1 c'est dégeu mais on peut se retrouver pile dans la génération d'un nouveau graphe et ne pas avoir l'ensembre des clsuter 
        $sql = "SELECT group_concat(b.id_mysql_server) as id_mysql_servers, group_concat(c.display_name) as display_name
        FROM dot3_cluster a 
        INNER JOIN dot3_cluster__mysql_server b ON b.id_dot3_cluster = a.id
        INNER JOIN mysql_server c ON c.id = b.id_mysql_server
        WHERE a.id_dot3_information 
        IN (SELECT id_dot3_information FROM dot3_cluster WHERE id in (select max(id)-1 from dot3_cluster))
        GROUP BY a.id_dot3_graph;";

        $res = $db->sql_query($sql);

        $data['grappe'] = array();
        while ($ob             = $db->sql_fetch_object($res)) {

            $id_mysql_server_splited = explode(",", $ob->id_mysql_servers);
            $libelle                 = array();

            foreach ($id_mysql_server_splited as $id_mysql_server) {
                $pretty_server = str_replace('"', "'", $id_mysql_server);
                
                $pretty_server = str_replace('"', "'", \App\Library\Display::srv($id_mysql_server));
                $libelle[]     = strip_tags($pretty_server, '<span><small>');
            }

            $item = implode(' , ', $libelle);

            $tmp            = array();
            $tmp['id']      = $ob->id_mysql_servers;
            $tmp['extra']   = array("data-content" => $item);
            $tmp['libelle'] = $ob->display_name;


            $data['grappe'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $get, array $server): array
    {
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'GET' && $method !== 'HEAD') {
            return self::buildIndexOutcome(405, 'Method Not Allowed', ['Allow' => 'GET, HEAD']);
        }

        $selection = self::normalizeIndexSelection($get);
        if ($selection === null) {
            return self::buildIndexOutcome(400, 'Invalid check config selection');
        }

        return self::buildIndexOutcome(200, '', [], $selection);
    }

    public static function normalizeIndexSelection(array $get): ?array
    {
        $hasCluster = array_key_exists('mysql_cluster', $get);
        $hasServer = array_key_exists('mysql_server', $get);

        if (!$hasCluster && !$hasServer) {
            return self::emptyIndexSelection();
        }

        if ($hasCluster && $hasServer) {
            return null;
        }

        $source = $hasCluster ? 'mysql_cluster' : 'mysql_server';
        if (!isset($get[$source]) || !is_array($get[$source]) || !array_key_exists('id', $get[$source])) {
            return null;
        }

        if ($get[$source]['id'] === '' || $get[$source]['id'] === []) {
            return self::emptyIndexSelection();
        }

        $ids = ServerIdSelection::normalizeList($get[$source]['id']);
        if ($ids === null) {
            return null;
        }

        return [
            'source' => $source,
            'ids' => $ids,
            'id_list' => ServerIdSelection::toCsv($ids),
        ];
    }

    private static function applyIndexSelectionToGet(array $selection): void
    {
        if ($selection['ids'] === []) {
            unset($_GET['mysql_cluster'], $_GET['mysql_server']);
            return;
        }

        if ($selection['source'] === 'mysql_cluster') {
            $_GET['mysql_cluster'] = ['id' => $selection['id_list']];
            unset($_GET['mysql_server']);
            return;
        }

        $_GET['mysql_server'] = ['id' => array_map('strval', $selection['ids'])];
        unset($_GET['mysql_cluster']);
    }

    private static function emptyIndexSelection(): array
    {
        return [
            'source' => null,
            'ids' => [],
            'id_list' => '',
        ];
    }

    private static function buildIndexOutcome(int $statusCode, string $message, array $headers = [], ?array $selection = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'selection' => $selection,
        ];
    }

    private static function sendIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');

        if ($message !== '') {
            echo $message;
        }
    }

/**
 * Retrieve check config state through `getDatabasesByServers`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getDatabasesByServers.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getDatabasesByServers()
 * @example /fr/checkconfig/getDatabasesByServers
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getDatabasesByServers($param)
    {

        $this->layout_name = false;

        $id_mysql_servers = ServerIdSelection::normalizeList($param[0] ?? null);
        if ($id_mysql_servers === null) {
            $data['databases'] = array();
            $this->set("data", $data);
            return true;
        }

        $data['databases'] = SelectorOptions::sharedDatabaseNamesByServerIds(
            $id_mysql_servers,
            function ($id_mysql_server) {
                return $this->getDbLinkFromId($id_mysql_server);
            }
        );

        $this->set("data", $data);
        return $data;
    }

/**
 * Retrieve check config state through `getDbLinkFromId`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_db Input value for `id_db`.
 * @phpstan-param int $id_db
 * @psalm-param int $id_db
 * @return mixed Returned value for getDbLinkFromId.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getDbLinkFromId()
 * @example /fr/checkconfig/getDbLinkFromId
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getDbLinkFromId($id_db)
    {

        if (IS_AJAX) {
            $this->layout_name = false;
        }

        // TODO #561: CheckConfig did not filter mysql_server.is_deleted=0
        // before the #559 mutualization. Preserve the legacy behavior with
        // excludeDeleted=false until the followup audits the workflow.
        return MysqlServer::getDbLinkFromId($id_db, false);
    }

/**
 * Handle check config state through `see`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for see.
 * @phpstan-return void
 * @psalm-return void
 * @see self::see()
 * @example /fr/checkconfig/see
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function see($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $display_name = $param[0];
        $sql          = "SELECT * FROM mysql_server WHERE display_name='".$display_name."'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $_db = Sgbd::sql($ob->name);
        }

        if (!empty($_db)) {

            $users = Mysql::exportAllUser($_db);

            foreach ($users as $user) {

                $pos = strpos($user, "debian-sys-maint");

// Notez notre utilisation de ===.  == ne fonctionnerait pas comme attendu
// car la position de 'a' est la 0-ième (premier) caractère.
                if ($pos !== false) {
                    continue;
                }

                echo $user.";\n";
            }
        } else {
            echo "Server not found !! \n";
        }
    }
}
