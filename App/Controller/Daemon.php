<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;

class Daemon extends Controller
{

    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        
        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }
        else {
            $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));
    
            $this->di['js']->code_javascript('
            $(document).ready(function(){
                function refresh(){
                    var myURL = GLIAL_LINK+"worker/list"+"/ajax:true";
                    $("#worker-index").load(myURL);
                }

                var intervalId = window.setInterval(function(){
                    refresh()  
                  }, 300);
            });');
        }

        $sql = "SELECT * from daemon_main order by id";
        $res = $db->sql_query($sql);

        $data['daemon'] = [];
        while ($arr            = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
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

        if ($commande == "start") {
            $order = "ASC";
        }
        else{
            $order = "DESC";
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM daemon_main ORDER BY id $order";
        $res = $db->sql_query($sql);

        $daemon = array();
        while($ob = $db->sql_fetch_object($res)) {
            $daemon[] = $ob->id;
        }

        foreach ($daemon as $id_daemon) {
            $php = explode(" ", shell_exec("whereis php"))[1];
            $cmd = $php." ".GLIAL_INDEX." Agent ".$commande." ".$id_daemon;
            Debug::debug($cmd);
            $pid = shell_exec($cmd);
        }

        if ($commande === "stop") {
            $php = explode(" ", shell_exec("whereis php"))[1];
            $cmd = $php." ".GLIAL_INDEX." Worker killAll";
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
            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }
    }

    public function update()
    {

        $this->view        = false;
        $this->layout_name = false;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "UPDATE daemon_main SET `".$_POST['name']."` = '".$_POST['value']."' WHERE id = ".$db->sql_real_escape_string($_POST['pk'])."";
            $db->sql_query($sql);

            if ($db->sql_affected_rows() === 1) {
                echo "OK";
            } else {
                header("HTTP/1.0 503 Internal Server Error");
            }
        }
    }

    public function refresh($param)
    {

        $this->view = false;

        Debug::parseDebug($param);

        if (Debug::$debug === true) {
            $debug = " --debug";
        } else {
            $debug = "";
        }

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." ".$this->getClass()." stopAll".$debug;
        Debug::debug($cmd);
        $pid = shell_exec($cmd);

        //$this->purgeLock(array());

        $cmd = $php." ".GLIAL_INDEX." control service".$debug;
        Debug::debug($cmd);
        $pid = shell_exec($cmd);

        usleep(5000);

        $cmd = $php." ".GLIAL_INDEX." ".$this->getClass()." startAll".$debug;
        Debug::debug($cmd);
        $pid = shell_exec($cmd);

        if (!IS_CLI) {
            $msg   = I18n::getTranslation(__("All lock/pid/md5 has been deleted and partions has been updated"));
            $title = I18n::getTranslation(__("Success"));

            set_flash("success", $title, $msg);
            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }
    }

    public function getStatitics($param = array())
    {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "select * from ts_file order by id;";
        $res = $db->sql_query($sql);

        $file = array();
        while ($ob   = $db->sql_fetch_object($res)) {
            $file[$ob->id] = $ob->file_name;
        }

        $path = TMP."tmp_file/*";

        $all_files = glob($path);

        $total = array();
        foreach ($all_files as $fullpath) {

            $filename = pathinfo($fullpath)['filename'];

            $prefix = explode('_', $filename)[0];

            if (empty($total[$prefix])) {
                $total[$prefix] = 1;
            } else {
                $total[$prefix]++;
            }
        }

        foreach ($total as $key => $nb_file) {
            if ($nb_file > 10) {

                if (!in_array($key, $file)) {
                    $sql = "INSERT INTO ts_file (file_name) VALUES ('".$key."');";
                    $db->sql_query($sql);
                }
            }
        }

        $data['registered']   = $file;
        $data['to_integrate'] = $total;

        Debug::debug($data);
    }




}