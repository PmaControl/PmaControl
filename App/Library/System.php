<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

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

    static public function getIp($hostname)
    {
        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            return trim($hostname);
        }

        $ip = shell_exec("dig +short ".$hostname);

        return trim($ip);
    }

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