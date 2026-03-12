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

/**
 * Class responsible for log workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Log
{

/**
 * Stores `$logger` for logger.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $logger = array();


/**
 * Retrieve log state through `get`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for get.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::get()
 * @example /fr/log/get
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle log state through `from`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for from.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::from()
 * @example /fr/log/from
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
