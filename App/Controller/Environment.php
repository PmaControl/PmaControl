<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Post;
use \Glial\Sgbd\Sgbd;


class Environment extends Controller {

    public function index() {
        $this->title = '<i class="fa fa-th-large" aria-hidden="true"></i> ' . __("Environment");


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM environment order by `id`";

        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['env'][] = $row;
        }


        $this->set('data', $data);
    }

    public function update() {

        $this->view = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "UPDATE environment SET `" . $_POST['name'] . "` = '" . $_POST['value'] . "' WHERE id = " . $db->sql_real_escape_string($_POST['pk']) . "";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }


    public function add($param) {
        $this->di['js']->addJavascript(array("bootstrap-select.min.js"));
        $db = Sgbd::sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $variable['environment'] = $_POST['environment'];

            //if ((empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle']))||(empty($variable['environment']['libelle'])))

            $return = $db->sql_save($variable);
            if (!$return) {
                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $msg = "<ul><li>" . implode("</li><li>", $error['environment']) . "</li></ul>";

                set_flash("error", "Error", $msg);

                header("location: " . LINK . "environment/add/" . Post::getToPost());
            } else {
                debug($db->sql_error());
                //header("location: ".LINK."environment/index/");
            }
        }

        $colors = array("danger", "warning", "default", "info", "success", "primary");

        $data['colors'] = array();
        foreach ($colors as $color) {
            $temp = [];
            $temp['id'] = $color;
            $temp['libelle'] = $color;

            $temp['extra'] = array("data-content" => "<span title='" . $color . "' class='label label-" . $color . "'>" . strtoupper($color) . "</span>");

            $data['colors'][] = $temp;
        }

        $this->set('data', $data);
    }

}
