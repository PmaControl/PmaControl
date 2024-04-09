<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

use \Glial\Sgbd\Sgbd;

class Log
{

    static $logger = array();


    static public function get()
    {
        $ret = self::from();
        $class_name = $ret['class_name'];
        $function = $ret['function'];

        if (empty(self::$logger[$class_name][$function]))
        {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT * FROM log WHERE class='".$class_name."' AND function = '".$function."'";
            $res = $db->sql_query($sql);

            $level = '';
            while ($ob = $db->sql_fetch_object($res)) {
                $level = $ob->level;
            }

            if (empty($level)) {
                $level = 'NOTICE';
                $tab = array();
                $tab['log']['class'] = $class_name;
                $tab['log']['function'] = $function;
                $tab['log']['level'] = $level;

                $db->sql_save($tab);
            }

            $monolog = new Logger($class_name);
            $handler = new StreamHandler(LOG_FILE, Logger::NOTICE);
            $handler->setFormatter(new LineFormatter(null, null, false, true));
            $monolog->pushHandler($handler);

            self::$logger[$class_name][$function] = $monolog;
        }
        
        return self::$logger[$class_name][$function];
    }

    static public function from()
    {
        $calledFrom = debug_backtrace();

        //debug($calledFrom);
        $var        = explode(DS, substr(str_replace(ROOT, '', $calledFrom[1]['file']), 1));
        
        debug($calledFrom[2]['function']);
        $source = end($var);

        $ret = array();
        $ret['class_name'] = pathinfo($source)['filename'];
        $ret['function'] = $calledFrom[2]['function'];
        

        return($ret);
    }

}