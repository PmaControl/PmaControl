<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Mydumper;
use App\Library\System;
use App\Library\Debug;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Sgbd\Sgbd;


class Job extends Controller {

    public function index() {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from `job` ORDER BY date_start DESC LIMIT 20;";
        $res = $db->sql_query($sql);

        $converter = new AnsiToHtmlConverter();

        $data['jobs'] = array();
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!System::isRunningPid($ob['pid']) && $ob['status'] === "RUNNING") {
                $gg = array();
                $gg['job']['id'] = $ob['id'];
                $gg['job']['status'] = "INTERRUPTED";
                $gg['job']['date_end'] = date("Y-m-d H:i:s");

                $res2 = $db->sql_save($gg);

                if ($res2) {
                    $ob['status'] = $gg['job']['status'];
                    $ob['date_end'] = $gg['job']['date_end'];
                }
            }

            if (file_exists($ob['log'])) {
                $log = file_get_contents($ob['log']);
            } else {
                $log = "";
            }
            $log = Mydumper::ParseLog($converter->convert($log));
            $ob['log_msg'] = $log;

            if (file_exists($ob['error'])) {
                $error = file_get_contents($ob['error']);
            } else {
                $error = "";
            }
            $error = Mydumper::ParseLog($converter->convert($error));
            $ob['error_msg'] = $error;


            $data['jobs'][] = $ob;
        }

        $this->set('data', $data);
    }

    public function callback($param) {

        usleep(1000);
//au cas ou le script est ultra rapide et le callback vient avant la création de la ligne

        Debug::parseDebug($param);
        $uuid = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from `job` where `uuid`='" . $uuid . "';";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $log = file_get_contents($ob->error);

            if (empty($log)) {
                $status = "SUCCESS";
            } else {
                $status = "ERROR";
            }

            $upt['job']['id'] = $ob->id;
            $upt['job']['status'] = $status;
            $upt['job']['date_end'] = date('Y-m-d H:i:s');

            $db->sql_save($upt);
        }
    }

    public function add($param) {

        Debug::parseDebug($param);

        $uuid = $param[0];
        $parametre = $param[1];
        $pid = $param[2];
        $log = $param[3];
        $log_error = $param[4];



        $called_from = debug_backtrace();

        $job = array();
        $job['job']['uuid'] = $uuid;
        $job['job']['class'] = $called_from[3]['class'];
        $job['job']['method'] = $called_from[3]['function'];
        $job['job']['param'] = json_encode($parametre);
        $job['job']['date_start'] = date("Y-m-d H:i:s");
        $job['job']['pid'] = $pid;
        $job['job']['log'] = $log;
        $job['job']['error'] = $log_error;
        $job['job']['status'] = "RUNNING";


        Debug::debug($job);

        $db = Sgbd::sql(DB_DEFAULT);
        $id_job = $db->sql_save($job);


        return $id_job;
    }

    public function gg($param) {

        $this->add($param);
    }

    public function restart($param) {

        $this->view = false;

        $id_job = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM job WHERE id=" . $id_job;
        $res = $db->sql_query($sql);

        $debug = "";
        if (Debug::$debug === true) {
            $debug = "--debug";
        }

        if (!empty($param[1]) && $param[1] === "--debug") {
            $debug = "--debug";
        }

        while ($ob = $db->sql_fetch_object($res)) {

            if (System::isRunningPid($ob->pid) !== true) {


                $php = explode(" ", shell_exec("whereis php"))[1];

                $cmd = $php . " " . GLIAL_INDEX . " " . $ob->class . " " . $ob->method . " " . implode(" ", json_decode($ob->param, true)) . "  " . $debug . "";
                Debug::debug($cmd);

                $pid = trim(shell_exec($cmd));


                Debug::debug($pid, "PID");
            }
        }

        header("location: " . LINK .$this->getClass(). '/index');
    }

}
