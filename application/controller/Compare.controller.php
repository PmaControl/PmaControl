<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use Glial\I18n\I18n;
use \Glial\Sgbd\Sql\Mysql\Parser;
use \Glial\Sgbd\Sql\Mysql\Comment;
use \Glial\Sgbd\Sql\Mysql\Compare as CompareTable;


//&lrarr;

class Compare extends Controller
{

    use \App\Library\Filter;
    //use \App\Library\

    var $db_origin;
    var $db_target;
    var $db_default;
    var $object = array("TABLE", "VIEW", "TRIGGER", "FUNCTION", "PROCEDURE", "EVENT");

    function index($params)
    {



        /*
         * SHOW TABLES
         * SHOW COLUMNS FROM table_name
         */
        $db                = $this->di['db']->sql(DB_DEFAULT);
        $this->db_default  = $db;
        $this->title       = __("Compare");
        //$this->ariane      = "> ".'<a href="'.LINK.'Plugins/index/">'.__('Plugins')."</a> > ".$this->title;

        $redirect = false;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {




            $id_server1 = empty($_POST['compare_main']['id_mysql_server__original'])
                    ? "" : $_POST['compare_main']['id_mysql_server__original'];
            $id_server2 = empty($_POST['compare_main']['id_mysql_server__compare'])
                    ? "" : $_POST['compare_main']['id_mysql_server__compare'];
            $db1        = empty($_POST['compare_main']['database__original']) ? ""
                    : $_POST['compare_main']['database__original'];
            $db2        = empty($_POST['compare_main']['database__compare']) ? ""
                    : $_POST['compare_main']['database__compare'];

            $out = $this->checkConfig($id_server1, $db1, $id_server2, $db2);

            if ($out !== true) {
                $extra = "";

                foreach ($out as $msg) {
                    $extra .= "<br />".__($msg);
                }

                $msg   = I18n::getTranslation(__("Please correct your paramaters !").$extra);
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                $redirect = true;
            }

            header('location: '.LINK.'compare/index/compare_main:id_mysql_server__original:'.$id_server1
                .'/compare_main:'.'id_mysql_server__compare:'.$id_server2
                .'/compare_main:'.'database__original:'.$db1
                .'/compare_main:'.'database__compare:'.$db2
            );
        }



        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "bootstrap-select.min.js", "compare/index.js"));

        $sql     = "SELECT * FROM mysql_server WHERE `error` = '' order by `name`";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp              = [];
            $tmp['id']        = $server['id'];
            $tmp['libelle']   = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $data['server'][] = $tmp;
        }

        $data['listdb1'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__original'])) {
            $select1         = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__original']));
            $data['listdb1'] = $select1['databases'];
        }

        $data['listdb2'] = array();
        if (!empty($_GET['compare_main']['id_mysql_server__compare'])) {
            $select1         = $this->getDatabaseByServer(array($_GET['compare_main']['id_mysql_server__compare']));
            $data['listdb2'] = $select1['databases'];
        }


        $data['display'] = false;

        if (count($data['listdb2']) != 0 && count($data['listdb1']) != 0) {
            if (!empty($_GET['compare_main']['database__original']) && !empty($_GET['compare_main']['database__compare'])) {


                $data['resultat'] = $this->analyse($_GET['compare_main']['id_mysql_server__original'],
                    $_GET['compare_main']['database__original'],
                    $_GET['compare_main']['id_mysql_server__compare'],
                    $_GET['compare_main']['database__compare']);

                $data['display'] = true;

                //log
                $this->di['log']->warning('[Compare] '.$_GET['compare_main']['id_mysql_server__original'].":".$_GET['compare_main']['database__original']." vs ".
                    $_GET['compare_main']['id_mysql_server__compare'].":".$_GET['compare_main']['database__compare']."(".$_SERVER["REMOTE_ADDR"].")");
            }
        }

        $this->set('data', $data);
    }

    private function checkConfig($id_server1, $db1, $id_server2, $db2)
    {
        $db    = $this->di['db']->sql(DB_DEFAULT);
        $error = array();

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server1)."';";
        $res = $db->sql_query($sql);
        if ($db->sql_num_rows($res) == 1) {
            while ($ob = $db->sql_fetch_object($res)) {
                $db_name_ori = $ob->name;
            }
        } else {
            $error[] = "The server original is unknow";
            unset($_GET['compare_main']['id_mysql_server__original']);
        }

        $sql  = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_server2)."';";
        $res2 = $db->sql_query($sql);

        if ($db->sql_num_rows($res2) == 1) {
            while ($ob = $db->sql_fetch_object($res2)) {
                $db_name_cmp = $ob->name;
            }
        } else {
            $error[] = "The server to compare is unknow";
            unset($_GET['compare_main']['id_mysql_server__compare']);
        }

        if (count($error) !== 0) {
            return $error;
        }

        $db_ori = $this->di['db']->sql($db_name_ori);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_ori->sql_real_escape_string($db1)."';";
        $res3   = $db_ori->sql_query($sql);
        $ob     = $db_ori->sql_fetch_object($res3);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db1."' original doesn't exist on server original : '".$db_name_ori."'";
        }

        $db_cmp = $this->di['db']->sql($db_name_cmp);
        $sql    = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '".$db_cmp->sql_real_escape_string($db2)."';";
        $res4   = $db_cmp->sql_query($sql);
        $ob     = $db_cmp->sql_fetch_object($res4);
        if ($ob->cpt != 1) {
            $error[] = "The database '".$db2."' original doesn't exist on server original : '".$db_name_cmp."'";
        }

        if ($id_server1 == $id_server2 && $db1 == $db2) {
            $error[] = "The databases to compare cannot be the same on same server";
        }

        if (count($error) === 0) {
            return true;
        } else {
            return $error;
        }
    }

    private function analyse($id_server1, $db1, $id_server2, $db2)
    {
        $db_original = $this->getDbLinkFromId($id_server1);
        $db_compare  = $this->getDbLinkFromId($id_server2);
        $db          = $this->di['db']->sql(DB_DEFAULT);

        $this->db_origin  = $db_original;
        $this->db_target  = $db_compare;
        $this->db_default = $db;

        foreach ($this->object as $object) {
            if ($object === "EVENT" && ($db1 === "performance_schema" || $db2 === "performance_schema")) {
                $data["EVENT"] = array();
                continue;
            }
            $data[$object] = $this->compareListObject($db1, $db2, $object);
        }
        return $data;
    }

    private function compareTable($original, $compare, $data)
    {
        //$dbs = [$this->db_origin, $this->db_target];

        $queries = array();

        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $queries[$table] = "SHOW CREATE TABLE `".$original."`.`".$table."`";
            }
        }
        $resultat = $this->execMulti($queries, $this->db_origin);

        $queries2 = array();
        foreach ($data as $table => $elem) {
            if (!empty($elem[1])) {
                $queries2[$table] = "SHOW CREATE TABLE `".$compare."`.`".$table."`";
            }
        }

        $resultat2 = $this->execMulti($queries2, $this->db_target);

        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $data[$table]['ori'] = $resultat[$table][0]['Create Table'].";";
            } else {
                $data[$table]['ori'] = "";
            }

            if (!empty($elem[1])) {
                $data[$table]['cmp'] = $resultat2[$table][0]['Create Table'].";";
            } else {
                $data[$table]['cmp'] = "";
            }

            /* fine optim else we compare every thing even when not required */
            if ($data[$table]['cmp'] === $data[$table]['ori']) {
                $data[$table]['script']  = array();
                $data[$table]['script2'] = array();
            } elseif (empty($data[$table]['cmp'])) {
                $data[$table]['script'][0]  = str_replace("CREATE TABLE",
                    "CREATE TABLE IF NOT EXISTS", $data[$table]['ori']);
                $data[$table]['script2'][0] = "DROP TABLE IF EXISTS `".$table."`;";
            } elseif (empty($data[$table]['ori'])) {
                $data[$table]['script2'][0] = str_replace("CREATE TABLE",
                    "CREATE TABLE IF NOT EXISTS", $data[$table]['cmp']);
                $data[$table]['script'][0]  = "DROP TABLE IF EXISTS `".$table."`;";
            } else {
                $updater                 = new CompareTable;
                $data[$table]['script']  = $updater->getUpdates($data[$table]['cmp'],
                    $data[$table]['ori']);
                $updater                 = new CompareTable;
                $data[$table]['script2'] = $updater->getUpdates($data[$table]['ori'],
                    $data[$table]['cmp']);
                unset($updater);
            }
        }

        return $data;
        //check table name
        //check collation
        //check charactere set
        //check engine
    }

    public function execMulti($queries, $db_link)
    {
        if (IS_CLI) {
            $this->view = false;
        }


        if (!is_array($queries)) {
            throw new Exception("PMACTRL-652 : first parameter should be an array !");
        }

        $query = implode(";", $queries);
        $ret   = [];
        $i     = 0;

        if ($db_link->sql_multi_query($query)) {
            foreach ($queries as $table => $elem) {
                $result = $db_link->sql_store_result();

                if (!$result) {
                    printf("Error: %s\n", mysqli_error($db_link->link));
                    debug($query);
                    exit();
                }

                while ($row = $db_link->sql_fetch_array($result, MYSQLI_ASSOC)) {
                    $ret[$table][] = $row;
                }
                if ($db_link->sql_more_results()) {
                    $db_link->sql_next_result();
                }
            }
        }
        return $ret;
    }

    function compareListObject($db1, $db2, $type_object)
    {
        $query['TRIGGER']['query']   = "select trigger_schema, trigger_name, action_statement from information_schema.triggers where trigger_schema ='{DB}'";
        $query['FUNCTION']['query']  = "show function status WHERE Db ='{DB}';";
        $query['PROCEDURE']['query'] = "show procedure status WHERE Db ='{DB}'";
        $query['TABLE']['query']     = "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '{DB}' AND TABLE_TYPE='BASE TABLE' order by TABLE_NAME;";
        $query['VIEW']['query']      = "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '{DB}' AND TABLE_TYPE='VIEW' order by TABLE_NAME;";
        $query['EVENT']['query']     = "SHOW EVENTS FROM `{DB}`";

        $query['TRIGGER']['field']   = "trigger_name";
        $query['FUNCTION']['field']  = "Name";
        $query['PROCEDURE']['field'] = "Name";
        $query['TABLE']['field']     = "TABLE_NAME";
        $query['VIEW']['field']      = "TABLE_NAME";
        $query['EVENT']['field']     = "Name";


        if (!in_array($type_object, array_keys($query))) {
            throw new \Exception("PMACTRL-095 : this type of object is not supported : '".$type_object."'",
            80);
        }

        // [] to prevent db with same name
        $dbs[][$db1] = $this->db_origin;
        $dbs[][$db2] = $this->db_target;

        $i = 0;

        //to prevent if a DB don't have a type of object
        $data = array();


        foreach ($dbs as $db_unique) {
            foreach ($db_unique as $db_name => $db_link) {
                $sql = str_replace('{DB}', $db_name,
                    $query[$type_object]['query']);
                $res = $db_link->sql_query($sql);

                while ($row = $db_link->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $data[$row[$query[$type_object]['field']]][$i] = 1;
                }
                $i++;
            }
        }
        ksort($data);
        return $data;
    }

    public function menu($param)
    {

        $data['menu']['TABLE']['name']  = __("Tables");
        $data['menu']['TABLE']['icone'] = '<i class="fa fa-table"></i>';
        $data['menu']['VIEW']['name']   = __("Views");
        $data['menu']['VIEW']['icone']  = '<i class="fa fa-eye"></i>';

        $data['menu']['TRIGGER']['name']  = __("Triggers");
        $data['menu']['TRIGGER']['icone'] = '<i class="fa fa-random"></i>';

        $data['menu']['FUNCTION']['name']  = __("Functions");
        $data['menu']['FUNCTION']['icone'] = '<i class="fa fa-cog"></i>';

        $data['menu']['PROCEDURE']['name']  = __("Routines");
        $data['menu']['PROCEDURE']['icone'] = '<i class="fa fa-cogs"></i>';

        $data['menu']['EVENT']['name']  = __("Events");
        $data['menu']['EVENT']['icone'] = '<i class="fa fa-calendar"></i>';

        if (empty($_GET['menu'])) {
            $_GET['menu'] = "TABLE";
        }

        //default choice if none selected
        $tmp_data = $param[0];

        // c'est laid !
        $tmp = json_decode(json_encode($tmp_data), true);

        foreach ($tmp['resultat'] as $key => $tab) {
            $data['menu'][$key]['count'] = count($tab);
            $data['menu'][$key]['url']   = LINK.'compare/index/'.$this->generateGet().'menu:'.$key;
        }

        $this->set('data', $data);
        return $data;
    }

    public function generateGet()
    {

        $url = array();
        foreach ($_GET['compare_main'] as $key => $val) {
            $url[] = 'compare_main:'.$key.':'.$val;
        }

        return implode('/', $url)."/";
    }

    public function getObjectDiff($param)
    {

        $this->db_origin = $this->getDbLinkFromId($_GET['compare_main']['id_mysql_server__original']);
        $this->db_target = $this->getDbLinkFromId($_GET['compare_main']['id_mysql_server__compare']);


        $data = json_decode(json_encode($param[0]), true);

        switch ($_GET['menu']) {
            case 'TABLE':
                $diff = $this->compareTable($_GET['compare_main']['database__original'],
                    $_GET['compare_main']['database__compare'],
                    $data['resultat'][$_GET['menu']]);

                $data['resultat'][$_GET['menu']] = $diff;
                break;

            default:
                $diff = $this->compareObject($_GET['compare_main']['database__original'],
                    $_GET['compare_main']['database__compare'],
                    $data['resultat'][$_GET['menu']]);

                $data['resultat'][$_GET['menu']] = $diff;
                break;
        }

        //$res = array_merge_recursive($diff, $data['resultat'][$_GET['menu']]);

        $this->set('data', $data);
        $this->set('menu', $param[1]);
    }

    public function compareObject($db1, $db2, $data)
    {
        $query['TRIGGER']['query']   = "SHOW CREATE TRIGGER `{DB}`.`{OBJECT}`";
        $query['FUNCTION']['query']  = "SHOW CREATE FUNCTION `{DB}`.`{OBJECT}`";
        $query['PROCEDURE']['query'] = "SHOW CREATE PROCEDURE `{DB}`.`{OBJECT}`";
        $query['TABLE']['query']     = "SHOW CREATE TABLE `{DB}`.`{OBJECT}`";
        $query['VIEW']['query']      = "SHOW CREATE VIEW `{DB}`.`{OBJECT}`";
        $query['EVENT']['query']     = "SHOW CREATE EVENT `{DB}`.`{OBJECT}`";

        $query['TRIGGER']['field']   = "SQL Original Statement";
        $query['FUNCTION']['field']  = "Create Function";
        $query['PROCEDURE']['field'] = "Create Procedure";
        $query['TABLE']['field']     = "Create Table";
        $query['VIEW']['field']      = "Create View";
        $query['EVENT']['field']     = "Create Event";


        $query['TRIGGER']['drop']   = "DROP TRIGGER `{OBJECT}`";
        $query['FUNCTION']['drop']  = "DROP FUNCTION `{OBJECT}`";
        $query['PROCEDURE']['drop'] = "DROP PROCEDURE `{OBJECT}`";
        $query['TABLE']['drop']     = "DROP TABLE `{OBJECT}`";
        $query['VIEW']['drop']      = "DROP VIEW `{OBJECT}`";
        $query['EVENT']['drop']     = "DROP EVENT `{OBJECT}`";


        //HACK
        if (!empty($data['diagnostics']) && $_GET['menu'] === 'PROCEDURE') {
            unset($data['diagnostics']);
        }


        if (!in_array($_GET['menu'], array_keys($query))) {
            throw new \Exception("PMACTRL-096 : this type of object is not supported : '".$_GET['menu']."'",
            80);
        }

        $queries = array();
        foreach ($data as $object => $elem) {
            if (!empty($elem[0])) {
                $tmp              = str_replace(array('{DB}', '{OBJECT}'),
                    array($db1, $object), $query[$_GET['menu']]['query']);
                $queries[$object] = $tmp;
            }
        }

        $resultat = $this->execMulti($queries, $this->db_origin);


        $queries2 = array();
        foreach ($data as $object => $elem) {
            if (!empty($elem[1])) {
                $queries2[$object] = str_replace(array('{DB}', '{OBJECT}'),
                    array($db2, $object), $query[$_GET['menu']]['query']);
            }
        }
        $resultat2 = $this->execMulti($queries2, $this->db_target);



        // UPDATE `mysql`.`proc` p SET definer = 'root@localhost' WHERE definer='root@foobar' AND db='whateverdbyouwant';

        foreach ($data as $object => $elem) {
            if (!empty($elem[0])) {
                $data[$object]['ori'] = $resultat[$object][0][$query[$_GET['menu']]['field']];
            } else {
                $data[$object]['ori'] = "";
            }

            if (!empty($elem[1])) {
                $data[$object]['cmp'] = $resultat2[$object][0][$query[$_GET['menu']]['field']];
            } else {
                $data[$object]['cmp'] = "";
            }

            /* fine optim else we compare every thing even when not required */
            if ($data[$object]['cmp'] === $data[$object]['ori']) {
                $data[$object]['script']  = array();
                $data[$object]['script2'] = array();
            } elseif (empty($data[$object]['cmp'])) {
                $data[$object]['script2'][0] = $data[$object]['ori'];
                $data[$object]['script'][0]  = str_replace('{OBJECT}', $object,
                    $query[$_GET['menu']]['drop']);
            } elseif (empty($data[$object]['ori'])) {
                $data[$object]['script'][0]  = $data[$object]['cmp'];
                $data[$object]['script2'][0] = str_replace('{OBJECT}', $object,
                    $query[$_GET['menu']]['drop']);
            } else {
                $data[$object]['script'][0]  = $data[$object]['cmp'];
                $data[$object]['script2'][0] = $data[$object]['ori'];
            }
        }

        return $data;
    }

    /*
     * used for load database from get have to delete it and find a better solution
     *
     *
     */
    function getDatabaseByServer($param)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $id_mysql_server = $param[0];

        $db_to_get_db = $this->getDbLinkFromId($id_mysql_server);

        $sql  = "SHOW DATABASES";
        $res2 = $db_to_get_db->sql_query($sql);

        $data['databases'] = [];
        while ($ob                = $db_to_get_db->sql_fetch_object($res2)) {
            $tmp                 = [];
            $tmp['id']           = $ob->Database;
            $tmp['libelle']      = $ob->Database;
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

        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($id_db)."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $db_link = $this->di['db']->sql($ob->name);
        }

        return $db_link;
    }
}