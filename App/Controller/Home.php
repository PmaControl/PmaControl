<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


class Home extends Controller {

    function before($param) {
        $this->di['js']->addJavascript(array("jquery-latest.min.js", "bootstrap.min.js", "http://getbootstrap.com/assets/js/docs.min.js"));
    }

    function index() {
        $this->title = __("Home");
        $this->ariane = " > " . __("Welcome to PmaControl !");


        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `home_box` ORDER BY `order`;";

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['item'][] = $arr;
        }

        $this->set('data', $data);


        //$this->javascript = array("");
    }

    function list_server($param) {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server ORDER BY ip";
        $data['server'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

}
