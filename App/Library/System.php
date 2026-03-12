<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Class responsible for system workflows.
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
class System
{
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @since pmacontrol 1.5.7 updated with /proc/pid
     * @description test if daemon is launched or not according with pid saved in table daemon_main
     * @access public
     *
     */

    static public function isRunningPid($param)
    {
        if (is_array($param)) {
            $pid = $param[0];
        } else {
            $pid = $param;
        }

        if (empty($pid)) {
            return false;
        }

        $pid = intval($pid);

        $res = shell_exec("ps -p $pid | tail -n +2");
        if (!empty($res)) {
            //process with a pid = $pid is running


            $elems = explode(" ", $res);

            $cmd = end($elems);

            //test si un process à été récupérer par autre chose que php
            if (substr($cmd, 0, 3) === "php") {
                //echo $cmd;
                return true;
            } else {
                return false;
            }
        }


        return false;
    }
    /*
     *
     * deprecated
     */

    static public function deleteFiles($file = "")
    {
        //to do add list from pmacontrol.ts_files
        $to_delete = array("server" => "/dev/shm/server_*", "answer" => "/dev/shm/answer_*",
            "variable" => "/dev/shm/variable_*", "worker" => "/dev/shm/worker");

        $files_to_delete = array();

        if (!empty($file)) {
            if (!empty($to_delete[$file])) {
                $files_to_delete[] = $to_delete[$file];
            }
        } else {
            foreach ($to_delete as $ff) {
                $files_to_delete[] = $ff;
            }
        }

        foreach ($files_to_delete as $file_to_delete) {
            $files = glob($file_to_delete);

            if (count($files) > 0) {
                shell_exec("rm ".$file_to_delete);
            }
        }
    }

/**
 * Retrieve system state through `getIp`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hostname Input value for `hostname`.
 * @phpstan-param mixed $hostname
 * @psalm-param mixed $hostname
 * @return mixed Returned value for getIp.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getIp()
 * @example /fr/system/getIp
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getIp($hostname)
    {

        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            //return trim($hostname);
            return false;
        }

        $ip = shell_exec("dig +short ".$hostname);
        Debug::debug($ip, "getIp");

        if (empty($ip)) {
            $ip =  $hostname; 
        }
        return trim($ip);
    }

/**
 * Handle system state through `scanPort`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $ip Input value for `ip`.
 * @phpstan-param mixed $ip
 * @psalm-param mixed $ip
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $timeOut Input value for `timeOut`.
 * @phpstan-param mixed $timeOut
 * @psalm-param mixed $timeOut
 * @return mixed Returned value for scanPort.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::scanPort()
 * @example /fr/system/scanPort
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function scanPort($ip, $port, $timeOut = 1)
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeOut);

        if (is_resource($connection)) {

            fclose($connection);
            return true;
        }

        return false;
    }
}
