<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Synapse\Basic;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
//use phpseclib\Crypt;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use \Glial\Synapse\Config;
use \App\Library\Debug;
use App\Library\Mysql;
use \App\Library\System;
use App\Library\Mydumper;

class Job extends Controller
{

    public function index()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * from `job`;";

        $res = $db->sql_query($sql);

        $data['jobs'] = array();
        while ($ob           = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


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