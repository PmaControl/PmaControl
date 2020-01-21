<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Post;
use \Glial\Sgbd\Sgbd;


class Log extends Controller {

    public function index() {

        // http://eonasdan.github.io/bootstrap-datetimepicker/  <= date time picker

        $this->di['js']->addJavascript(array('moment.js', 'bootstrap-datetimepicker.js'));

        $this->di['js']->code_javascript(
                "$(function () {
            $('#datetimepicker1').datetimepicker({sideBySide:true,format:'YYYY-MM-DD HH:mm:ss'});
        });

        $(function () {
            $('#datetimepicker2').datetimepicker({sideBySide:true,format:'YYYY-MM-DD HH:mm:ss'});
        });

        ");

        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();



        if ($_SERVER['REQUEST_METHOD'] == "POST") {



            if (!empty($_POST['mysql_server']['id'])) {
                $_POST['mysql_server']['id'] = "[" . implode(",", $_POST['mysql_server']['id']) . "]";
            }
            if (!empty($_POST['ts_variable']['id'])) {
                $_POST['ts_variable']['id'] = "[" . implode(",", $_POST['ts_variable']['id']) . "]";
            }

            header("location: " . LINK .$this->getClass(). "/" . __FUNCTION__ . "/" . Post::getToPost());
        }

        $data['log'] = array();

        if (!empty($_GET['mysql_server']['id']) && !empty($_GET['ts_variable']['id'])) {

            $id_mysql_servers = explode(',', substr($_GET['mysql_server']['id'], 1, -1));
            $id_ts_variables = explode(',', substr($_GET['ts_variable']['id'], 1, -1));


            $data['log'] = Extraction::display($id_ts_variables, $id_mysql_servers, array($_GET['ts']['date_start'], $_GET['ts']['date_end']), true);
        }


        $this->set('data', $data);
    }

}
