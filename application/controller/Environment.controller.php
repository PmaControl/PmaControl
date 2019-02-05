<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Environment extends Controller
{

    public function index()
    {
        $this->title = '<i class="fa fa-th-large" aria-hidden="true"></i> '.__("Environment");


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM environment order by `libelle`";

        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['env'][] = $row;
        }


        $this->set('data', $data);
    }

    public function update()
    {

        $this->view        = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "UPDATE environment SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }


    public function up($param)
    {

        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $id_menu = $param[0];
        $id      = $param[1];


        header("location: ".LINK."environment/index/");
    }
}