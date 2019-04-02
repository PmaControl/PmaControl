<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\I18n\I18n;

class Daemon extends Controller
{
    var $daemon = array(7, 10, 11, 12, 5);

    public function index()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js',  'Tree/index.js'));
        

        $sql = "SELECT * from daemon_main order by id";
        $res = $db->sql_query($sql);

        $data['daemon'] = [];
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if ($arr['queue_number'] !== "0") {
                $queue         = msg_get_queue($arr['queue_number']);
                $arr['nb_msg'] = msg_stat_queue($queue)['msg_qnum'];
            } else {
                $arr['nb_msg'] = "N/A";
            }

            $data['daemon'][] = $arr;
        }

        $this->set('data', $data);
    }

    public function startAll($param)
    {
        Debug::parseDebug($param);
        $this->manageDaemon("start");
    }

    public function stopAll($param)
    {
        Debug::parseDebug($param);
        $this->manageDaemon("stop");
    }

    private function manageDaemon($commande)
    {

        if ($commande == "stop") {
            $this->daemon = array_reverse($this->daemon);
        }

        foreach ($this->daemon as $id_daemon) {
            $php = explode(" ", shell_exec("whereis php"))[1];
            $cmd = $php." ".GLIAL_INDEX." Agent ".$commande." ".$id_daemon;
            Debug::debug($cmd);
            $pid = shell_exec($cmd);
        }

        if ($commande === "stop") {

            $php = explode(" ", shell_exec("whereis php"))[1];
            $cmd = $php." ".GLIAL_INDEX." Aspirateur killAllWorker";
            Debug::debug($cmd);
            $pid = shell_exec($cmd);



            //test all pid before

            $msg   = I18n::getTranslation(__("All the daemon was successfully stopped"));
            $title = I18n::getTranslation(__("Success"));
        } else {

            $msg   = I18n::getTranslation(__("All the daemon was successfully started"));
            $title = I18n::getTranslation(__("Success"));
        }

        if (!IS_CLI) {
            set_flash("success", $title, $msg);
            header("location: ".LINK.__CLASS__."/index");
            exit;
        }
    }

    public function update()
    {

        $this->view        = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "UPDATE daemon_main SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }
}