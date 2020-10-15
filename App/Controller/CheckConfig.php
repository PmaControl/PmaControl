<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

//version de 14 octobre 2020, comparaison de tableau multidimensions avec agregation des entetes
//ne servait a rien ici car chaque serveur au moins un element different

class CheckConfig extends Controller
{
    var $should_be_different = array("server_id", "report_host", "wsrep_node_name");
    var $not_important       = array("general_log_file", "gtid_binlog_state");
    var $master_master       = array("");
    var $human_readable      = array("aria_log_file_size", "aria_pagecache_buffer_size", "aria_max_sort_file_size", "binlog_cache_size", "innodb_buffer_pool_size", "innodb_log_file_size", "max_heap_table_size",
        "query_cache_size",
        "tmp_memory_table_size", "tmp_table_size");

    public function index($param)
    {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $this->di['js']->code_javascript('(function() {
            $( ".showdiff" ).click(function() {
            $(".to_hide").toggleClass("hide")
            });
        })();');


        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['mysql_cluster']['id'])) {

                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/mysql_cluster:id:'.$_POST['mysql_cluster']['id']);
            }

            if (!empty($_POST['mysql_server']['id'])) {

                header('location: '.LINK.$this->getClass().'/'.__FUNCTION__.'/mysql_server:id:'.implode(',', $_POST['mysql_server']['id']));
            }
        } else {

            if (!empty($_GET['mysql_cluster']['id']) || !empty($_GET['mysql_server']['id'])) {


                if (!empty($_GET['mysql_cluster']['id'])) {
                    $sql = "SELECT * FROM mysql_server WHERE id in (".$_GET['mysql_cluster']['id'].")";

                    $id_mysql_servers = explode(",", $_GET['mysql_cluster']['id']);
                }

                if (!empty($_GET['mysql_server']['id'])) {
                    $sql = "SELECT * FROM mysql_server WHERE id in (".$_GET['mysql_server']['id'].")";

                    $id_mysql_servers = explode(',', $_GET['mysql_server']['id']);
                }

                $res = $db->sql_query($sql);

                $server_note_available = array();
                while ($ob                    = $db->sql_fetch_object($res)) {

                    if ($ob->is_available !== "1") {
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

                        //debug("cache");

                        $res = $db->sql_query("SELECT variable as Variable_name, value as Value FROM mysql_variable WHERE id_mysql_server=".$id_mysql_server." ORDER BY 1;");
                        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

                            $tmp                  = array();
                            $tmp['Variable_name'] = strtolower($arr['Variable_name']);
                            $tmp['Value']         = $arr['Value'];
                            $elems[]              = $tmp;
                        }
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
                foreach ($resultat as $res) {
                    $index = array_merge($res);
                }

                $data['index'] = array_keys($index);
                sort($data['index']);

                $data['resultat'] = $resultat;
            }
        }




        $sql = "select group_concat(b.display_name) as name ,group_concat(a.id_mysql_server) as id_mysql_servers
            from link__architecture__mysql_server a
            INNER JOIN mysql_server b ON a.id_mysql_server= b.id
            group by id_architecture having count(1) > 1;";


        $res = $db->sql_query($sql);

        $data['grappe'] = array();
        while ($ob             = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id_mysql_servers;
            $tmp['libelle'] = $ob->name;


            $data['grappe'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public function getDatabasesByServers($param)
    {

        $this->layout_name = false;

        if (empty($param[0])) {
            $data['databases'] = array();
            $this->set("data", $data);
            return true;
        }
        $id_mysql_servers = explode(",", $param[0]);

        $max = count($id_mysql_servers);

        $data['db'] = array();
        foreach ($id_mysql_servers as $id_mysql_server) {
            $db_to_get_db = $this->getDbLinkFromId($id_mysql_server);

            $sql  = "SHOW DATABASES";
            $res2 = $db_to_get_db->sql_query($sql);


            while ($ob = $db_to_get_db->sql_fetch_object($res2)) {
                $data['db'][] = $ob->Database;
            }
        }

        $database = array_count_values($data['db']);

        foreach ($database as $db => $count) {
            $tmp                 = [];
            $tmp['id']           = $db;
            $tmp['libelle']      = "(".$count."/".$max.") ".$db;
            $data['databases'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }

    private function getDbLinkFromId($id_db)
    {

        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_db)."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_link = Sgbd::sql($ob->name);
        }

        return $db_link;
    }

    private function arrayDiffAssocRecursive($array1, $array2)
    {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff         = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if (!empty($new_diff)) $difference[$key] = $new_diff;
                }
            } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }
    /*
     * add doc
     * permet de testé 2 à 2 toutes les possibilitées
     */

    private function perm($nbrs)
    {
        $temp = $nbrs;
        $ret  = [];

        foreach ($nbrs as $server1) {
            foreach ($temp as $server2) {

                if (($key = array_search($server1, $temp)) !== false) {
                    unset($temp[$key]);
                }

                if ($server1 === $server2) {
                    continue;
                }

                $ret[] = array($server1, $server2);
            }
        }

        return $ret;
    }

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
    /*
     * traitement des données
     */

    private function fetchData($elems, $id_mysql_server, $id_number)
    {
        $resultat = array();

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
                if (strtolower($arr['Variable_name']) === "wsrep_provider_options") {

                    //$resultat[$id_mysql_server][$arr['Variable_name']] = $arr['Value'];

                    $vals = explode(";", trim($arr['Value'], ";"));
                    array_pop($vals); // remove last ; (empty)

                    foreach ($vals as $val) {
                        $varval = explode("=", $val);

                        $sous_variable = trim($varval[0]);
                        $sous_value    = trim($varval[1]);

                        if (!isset($varval[1])) {
                            debug($varval);
                        }

                        $resultat[$id_mysql_server][$arr['Variable_name']."<i>__".$sous_variable."</i>"] = $sous_value;
                    }
                } else {
                    $resultat[$id_mysql_server][$arr['Variable_name']] = $arr['Value'];
                    //$data['show']                                      = true;
                }
            } else {
                //debug($nb);
                $resultat[$id_mysql_server][] = $arr;
            }
        }

        return $resultat;
    }
}