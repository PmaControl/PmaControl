<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for check data on cluster workflows.
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
class CheckDataOnCluster extends Controller {

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
    var $not_important = array("general_log_file", "gtid_binlog_state");
/**
 * Stores `$master_master` for master master.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $master_master = array("");

/**
 * Render check data on cluster state through `index`.
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
 * @example /fr/checkdataoncluster/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param) {

        $this->di['js']->addJavascript(array('bootstrap-select.min.js', 'CheckDataOnCluster/index.js'));

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['mysql_cluster']['database']) && !empty($_POST['sql'])) {

                header('location: ' . LINK . $this->getClass() . '/' . __FUNCTION__ . '/mysql_cluster:id:' . $_POST['mysql_cluster']['id']
                        . '/mysql_cluster:database:' . $_POST['mysql_cluster']['database'] . '/sql:' . urlencode($_POST['sql']));
            }
        } else {

            if (!empty($_GET['mysql_cluster']['database']) && !empty($_GET['sql'])) {


                $sql = "SELECT * FROM mysql_server WHERE id in (" . $_GET['mysql_cluster']['id'] . ")";
                $res = $db->sql_query($sql);
                while ($ob = $db->sql_fetch_object($res)) {
                    $data['mysql_server'][$ob->id] = $ob->display_name . " (" . $ob->ip . ")";
                }

                $resultat = array();

                $id_mysql_servers = explode(",", $_GET['mysql_cluster']['id']);
                foreach ($id_mysql_servers as $id_mysql_server) {

                    $db_link = $this->getDbLinkFromId($id_mysql_server);
                    $db_link->sql_select_db($_GET['mysql_cluster']['database']);

                    $res = $db_link->sql_query($_GET['sql']);


                    while ($arr = $db_link->sql_fetch_array($res, MYSQLI_ASSOC)) {

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
                            $resultat[$id_mysql_server][$arr['Variable_name']] = $arr['Value'];
                            $data['show'] = true;
                        } else {

                            //debug($nb);
                            $resultat[$id_mysql_server][] = $arr;
                        }
                    }
                }

                if ($data['show']) {
                    foreach ($resultat as $res) {
                        $index = array_merge($res);
                    }

                    $data['index'] = array_keys($index);
                    sort($data['index']);

                    $data['resultat'] = $resultat;
                }

                $combinaisons = $this->perm($id_mysql_servers);

                $groups = array();

                foreach ($combinaisons as $combi) {
                    $diff1 = $this->array_diff_assoc_recursive($data['resultat'][$combi[0]], $data['resultat'][$combi[1]]);
                    $diff2 = $this->array_diff_assoc_recursive($data['resultat'][$combi[1]], $data['resultat'][$combi[0]]);

                    //debug($diff1);
                    //debug($diff2);

                    if (count($diff1) == 0 && count($diff2) == 0) {

                        if (empty($groups)) {
                            $groups[] = array($combi[0], $combi[1]);
                        } else {

                            foreach ($groups as $key => $group) {
                                if (in_array($combi[0], $group)) {
                                    $groups[$key][] = $combi[1];
                                }

                                if (in_array($combi[1], $group)) {
                                    $groups[$key][] = $combi[0];
                                }

                                $groups[$key] = array_unique($groups[$key]);
                            }
                        }
                    } else {

                        $found = false;
                        foreach ($groups as $group) {
                            if ($group[0] === $combi[0]) {
                                $found = true;
                            }
                        }

                        if (!$found) {
                            $groups[][] = $combi[0];
                        }



                        $found = false;
                        foreach ($groups as $group) {
                            if ($group[0] === $combi[1]) {
                                $found = true;
                            }
                        }

                        if (!$found) {
                            $groups[][] = $combi[1];
                        }
                    }
                }



                $data['groups'] = $groups;
            }
        }

        $sql = "select group_concat(b.name) as name ,group_concat(a.id_mysql_server) as id_mysql_servers
            from link__architecture__mysql_server a
            INNER JOIN mysql_server b ON a.id_mysql_server= b.id
            group by id_architecture having count(1) > 1;";

        $res = $db->sql_query($sql);

        $data['grappe'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = array();
            $tmp['id'] = $ob->id_mysql_servers;
            $tmp['libelle'] = $ob->name;


            $data['grappe'][] = $tmp;
        }

        $this->set('data', $data);
    }

/**
 * Retrieve check data on cluster state through `getDatabasesByServers`.
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
 * @example /fr/checkdataoncluster/getDatabasesByServers
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getDatabasesByServers($param) {

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

            $sql = "SHOW DATABASES";
            $res2 = $db_to_get_db->sql_query($sql);


            while ($ob = $db_to_get_db->sql_fetch_object($res2)) {
                $data['db'][] = $ob->Database;
            }
        }

        $database = array_count_values($data['db']);

        foreach ($database as $db => $count) {
            $tmp = [];
            $tmp['id'] = $db;
            $tmp['libelle'] = "(" . $count . "/" . $max . ") " . $db;
            $data['databases'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }

/**
 * Retrieve check data on cluster state through `getDbLinkFromId`.
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
 * @example /fr/checkdataoncluster/getDbLinkFromId
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getDbLinkFromId($id_db) {

        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($id_db) . "';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_link = Sgbd::sql($ob->name);
        }

        return $db_link;
    }

/**
 * Handle check data on cluster state through `array_diff_assoc_recursive`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $array1 Input value for `array1`.
 * @phpstan-param mixed $array1
 * @psalm-param mixed $array1
 * @param mixed $array2 Input value for `array2`.
 * @phpstan-param mixed $array2
 * @psalm-param mixed $array2
 * @return mixed Returned value for array_diff_assoc_recursive.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::array_diff_assoc_recursive()
 * @example /fr/checkdataoncluster/array_diff_assoc_recursive
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function array_diff_assoc_recursive($array1, $array2) {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }

/**
 * Retrieve check data on cluster state through `liste_combinaison`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $list Input value for `list`.
 * @phpstan-param array<int|string,mixed> $list
 * @psalm-param array<int|string,mixed> $list
 * @return void Returned value for liste_combinaison.
 * @phpstan-return void
 * @psalm-return void
 * @see self::liste_combinaison()
 * @example /fr/checkdataoncluster/liste_combinaison
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function liste_combinaison($list) {
        $count = count($list);

        $tmp = $list;

        foreach ($list as $elem) {
            
        }
    }

    /*
     * add doc
     * permet de testé 2 à 2 toutes les possibilitées
     */

    private function perm($nbrs) {
        $temp = $nbrs;
        $ret = [];

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

/**
 * Handle check data on cluster state through `see`.
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
 * @example /fr/checkdataoncluster/see
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function see($param) {
        $db = Sgbd::sql(DB_DEFAULT);
        $display_name = $param[0];
        $sql = "SELECT * FROM mysql_server WHERE display_name='" . $display_name . "'";
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

                echo $user . ";\n";
            }
        } else {
            echo "Server not found !! \n";
        }
    }

}

