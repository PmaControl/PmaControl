<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Html\Pagination\Pagination;

class Monitoring extends Controller {

    public $previous_data = array();
    public $actual_data = array();

    function arrays_are_similar($a, $b) {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }

    function compare($tab_from = array(), $tab_to) {
        $tab_update = array_intersect_key($tab_from, $tab_to);
        foreach ($tab_update as $key => $value) {
            if ($tab_from[$key] != $tab_to[$key]) {
                $update[$key] = $tab_to[$key];
                $update2[$key] = $tab_from[$key];
            }
        }
        foreach ($tab_to as $key => $value) {
            if (!isset($tab_update[$key])) {
                $add[$key] = $value;
            }
        }
        foreach ($tab_from as $key => $value) {
            if (!isset($tab_update[$key])) {
                $del[$key] = $value;
            }
        }

        $finale = array();
        empty($add) ? "" : $finale['add'] = $add;
        empty($delete) ? "" : $finale['delete'] = $del;
        empty($update) ? "" : $finale['update'] = $update;
        empty($update2) ? "" : $finale2['update'] = $update2;

        $param['up'] = $finale;
        empty($finale2) ? $param['down'] = array() : $param['down'] = $finale2;

        return serialize($param);
    }

    public function query($param) {
        $this->title = __("Query Analyzer");
        $this->ariane = " > " . __("Monitoring") . " > " . $this->title;

        if (empty($param[0])) {

            $default = $this->di['db']->sql(DB_DEFAULT);
            $sql = "SELECT * FROM mysql_server limit 1";
            $res = $default->sql_query($sql);

            $ob = $default->sql_fetch_object($res);

            $param[0] = $ob->id;
        }
        if (!empty($param[0])) {
            $data['id_server'] = $param[0];
            $_GET['mysql_server']['id'] = $data['id_server'];
        }

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['mysql_server']['id'])) {
                $data['id_server'] = $_POST['mysql_server']['id'];
            }


            if (empty($_GET['page'])) {
                $_GET['page'] = 1;
            }

            if (!empty($_POST['database']['id'])) {
                $_GET['database']['id'] = $_POST['database']['id'];
            } else {
                $_GET['database']['id'] = "";
            }

            if (!empty($_POST['database']['filter'])) {
                $_GET['database']['filter'] = $_POST['database']['filter'];
            } else {
                $_GET['database']['filter'] = "";
            }

            if (!empty($_POST['field']['id'])) {
                $_GET['field']['id'] = $_POST['field']['id'];
            } else {
                $_GET['field']['id'] = "";
            }


            if (!empty($_POST['orderby']['id'])) {
                $_GET['orderby']['id'] = $_POST['orderby']['id'];
            } else {
                $_GET['orderby']['id'] = "ASC";
            }


            header('location: ' . LINK . "monitoring/query/" . $data['id_server']
                    . "/database:id:" . $_GET['database']['id']
                    . "/field:id:" . $_GET['field']['id']
                    . "/database:filter:" . $_GET['database']['filter'] . "/orderby:id:" . $_GET['orderby']['id'] . "/page:" . $_GET['page']);
        } else {
            $_GET['database']['id'] = empty($_GET['database']['id']) ? "" : $_GET['database']['id'];
            $_GET['field']['id'] = empty($_GET['field']['id']) ? "" : $_GET['field']['id'];
            $_GET['database']['filter'] = empty($_GET['database']['filter']) ? "" : $_GET['database']['filter'];
            $_GET['orderby']['id'] = empty($_GET['orderby']['id']) ? "" : $_GET['orderby']['id'];
        }


        $default = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * FROM mysql_server order by name";
        $res = $default->sql_query($sql);


        $data['server_mysql'] = [];
        while ($ob = $default->sql_fetch_object($res)) {
            $tmp = [];

            $tmp['id'] = $ob->id;
            $tmp['libelle'] = str_replace('_', '-', $ob->name) . " (" . $ob->ip . ")";

            $data['server_mysql'][] = $tmp;


            if ($data['id_server'] === $ob->id) {
                $link = $ob->name;
            }
        }

        $db = $this->di['db']->sql(str_replace('-', '_', $link));


        $sql = "SHOW DATABASES";
        $res = $db->sql_query($sql);


        $data['databases'] = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];

            $tmp['id'] = $ob->Database;
            $tmp['libelle'] = $ob->Database;

            $data['databases'][] = $tmp;
        }




        $data['performance_schema'] = false;
        $sql = "SHOW VARIABLES LIKE 'performance_schema';";

        $res = $db->sql_query($sql);

        $data['error'] = false;

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->Value == "ON") {
                $data['performance_schema'] = true;
            } else {
                $data['error'] = true;
            }
        }


        $data['mysql_upgrade'] = true;

        if ($data['performance_schema']) {
            $sql = "select * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = 'performance_schema' AND `TABLE_NAME` = 'events_statements_summary_by_digest'";
            $res = $db->sql_query($sql);

            //in case of table doesn't exist (mysql_upgrade not made)



            if ($db->sql_num_rows($res) == 0) {
                $data['mysql_upgrade'] = false;
                $data['error'] = true;
            }
        }


        if ($data['error'] === false) {



            $data['fields'] = [];
            while ($ob = $db->sql_fetch_object($res)) {
                $tmp = [];

                $tmp['id'] = $ob->COLUMN_NAME;
                $tmp['libelle'] = $ob->COLUMN_NAME;

                $data['fields'][] = $tmp;
            }


            $data['orderby'][0]['id'] = 'ASC';
            $data['orderby'][0]['libelle'] = 'ASC';
            $data['orderby'][1]['id'] = 'DESC';
            $data['orderby'][1]['libelle'] = 'DESC';


            $sql1 = "SELECT * ";
            $sql2 = "SELECT count(1) as cpt ";


            $sql = " FROM performance_schema.events_statements_summary_by_digest a
            where 1=1 ";

            if (!empty($_GET['database']['id'])) {
                $sql .= " AND a.SCHEMA_NAME ='" . $_GET['database']['id'] . "' ";
            }

            if (!empty($_GET['database']['filter'])) {
                $sql .= " AND a.DIGEST_TEXT LIKE '%" . $_GET['database']['filter'] . "%' ";
            }

            $sql3 = " ";

            if (!empty($_GET['field']['id'])) {
                if (empty($_GET['orderby']['id'])) {
                    $_GET['orderby']['id'] = "ASC";
                }

                $sql3 = " ORDER BY a.`" . $_GET['field']['id'] . "` " . $_GET['orderby']['id'] . " ";
            }



            //$sql3 = " order by a.COUNT_STAR DESC "; //$sql3 = " order by a.date_validated desc";
            //$sql = ""



            $res = $db->sql_query($sql2 . $sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $data['count'] = $ob->cpt;
            }



            if ($data['count'] != 0) {


                //url, curent page, nb item max , nombre de lignes, nombres de pages
                if (empty($_GET['page'])) {
                    $_GET['page'] = 1;
                }

                $pagination = new Pagination(LINK .$this->getClass(). '/' . __FUNCTION__ . '/' . $param[0]
                        . "/database:id:" . $_GET['database']['id']
                        . "/field:id:" . $_GET['field']['id']
                        . "/database:filter:" . $_GET['database']['filter'] . "/orderby:id:" . $_GET['orderby']['id']
                        , $_GET['page'], $data['count'], 50, 30);

                $tab = $pagination->get_sql_limit();


                $pagination->set_alignment("left");
                $pagination->set_invalid_page_number_text(__("Please input a valid page number!"));
                $pagination->set_pages_number_text(__("pages of"));
                $pagination->set_go_button_text(__("Go"));
                $pagination->set_first_page_text("« " . __("First page"));
                $pagination->set_last_page_text(__("Last page") . " »");
                $pagination->set_next_page_text("»");
                $pagination->set_prev_page_text("«");

                $pagination->show_go_button(false);
                $data['pagination'] = $pagination->print_pagination();

                $limit = " LIMIT " . $tab[0] . "," . $tab[1] . " ";
                $data['i'] = $tab[0] + 1;
                //*****************************pagination end
            }

            empty($limit) ? $limit = "" : "";


            $sql = $sql1 . $sql . $sql3 . $limit;

            //debug($sql);
            $data['event_by_digest'] = $db->sql_fetch_yield($sql);
        }

        $this->set('data', $data);
    }

    public function search() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `mysql_server`";
        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];

            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name;

            $data['server'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public function explain() {

        // update setup_instruments SET ENABLED='YES', TIMED='YES';
        // UPDATE setup_consumers SET ENABLED = 'YES';

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server where id= " . $_GET['mysql_server']['id'] . "";
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $remote = $this->di['db']->sql($ob->name);
        }

        $sql = "select * from performance_schema.events_statements_history_long where DIGEST='" . $_GET['digest'] . "'";

        $data['table'] = $remote->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function getExplain($param) {
        
    }

    private function getServer() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server where id= " . $_GET['mysql_server']['id'] . "";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $remote = $this->di['db']->sql($ob->name);
        }

        return $remote;
    }

}

/*
 *
 * USE INFORMATION_SCHEMA;
SELECT
    TABLES.table_name
FROM TABLES
LEFT JOIN KEY_COLUMN_USAGE AS c
ON (
       TABLES.TABLE_NAME = c.TABLE_NAME
   AND c.CONSTRAINT_SCHEMA = TABLES.TABLE_SCHEMA
   AND c.constraint_name = 'PRIMARY'
)
WHERE
    TABLES.table_schema = 'amdapplication_FR_fr'
AND c.constraint_name IS NULL;
 *
 */