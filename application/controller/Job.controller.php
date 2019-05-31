<?php

use \Glial\Synapse\Controller;
use App\Library\Mydumper;
use App\Library\System;

class Job extends Controller
{

    public function index()
    {
        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * from `job`;";
        $res = $db->sql_query($sql);

        $data['jobs'] = array();
        while ($ob           = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!System::isRunningPid($ob['pid']) && $ob['status'] === "RUNNING") {
                $gg                    = array();
                $gg['job']['id']       = $ob['id'];
                $gg['job']['status']   = "INTERRUPTED";
                $gg['job']['date_end'] = date("Y-m-d H:i:s");

                $res = $db->sql_save($gg);

                if ($res) {
                    $ob['status']   = $gg['job']['status'];
                    $ob['date_end'] = $gg['job']['date_end'];
                }
            }

            $ob['log_msg']  = Mydumper::ParseLog($ob['log']);
            $data['jobs'][] = $ob;
        }

        $this->set('data', $data);
    }

    public function callback($param)
    {
        Debug::parseDebug($param);
        $uuid = $param[0];
        $db   = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * from `job` where `uuid`='".$uuid."';";
        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $log = file_get_contents($ob->log);

            if (empty($log)) {
                $status = "SUCCESS";
            } else {
                $status = "ERROR";
            }
            
            $upt['job']['id']       = $ob->id;
            $upt['job']['status']   = $status;
            $upt['job']['date_end'] = date('Y-m-d H:i:s');

            $db->sql_save($upt);
        }
    }
}