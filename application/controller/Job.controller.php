<?php

use \Glial\Synapse\Controller;
use App\Library\Mydumper;
use App\Library\System;
use App\Library\Debug;

class Job extends Controller
{

    public function index()
    {
        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * from `job` ORDER BY date_start DESC;";
        $res = $db->sql_query($sql);

        $data['jobs'] = array();
        while ($ob           = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!System::isRunningPid($ob['pid']) && $ob['status'] === "RUNNING") {
                $gg                    = array();
                $gg['job']['id']       = $ob['id'];
                $gg['job']['status']   = "INTERRUPTED";
                $gg['job']['date_end'] = date("Y-m-d H:i:s");

                $res2 = $db->sql_save($gg);

                if ($res2) {
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

    public function add($param)
    {

        Debug::parseDebug($param);
        
        $uuid = $param[0];
        $parametre = $param[1];
        $pid = $param[2];
        $pid = $param[3];

        $db = $this->di['db']->sql(DB_DEFAULT);

        $called_from = debug_backtrace();

        $job                      = array();
        $job['job']['class']      = $called_from[1]['class'];
        $job['job']['method']     = $called_from[1]['function'];
        $job['job']['param']      = json_encode($param);
        $job['job']['date_start'] = date("Y-m-d H:i:s");
        $job['job']['pid']        = $pid;
        $job['job']['log']        = $log;
        $job['job']['status']     = "RUNNING";


/***/
  //      Debug::debug($job);

        //$db->save($job);
    }


    public function gg($param)
    {
      
        $this->add($param);
    }
}