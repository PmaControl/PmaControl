<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class System {
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description test if daemon is launched or not according with pid saved in table daemon_main
     * @access public
     * 
     */

    static public function isRunningPid($param) {

        if (is_array($param)) {
            $pid = $param[0];
        } else {
            $pid = $param;
        }

        if (empty($pid)) {
            return false;
        }

        $cmd = "ps -p " . $pid;
        $alive = shell_exec($cmd);

        if (strpos($alive, $pid) !== false) {
            return true;
        }

        return false;
    }

    static public function deleteFiles($file = "") {

        $to_delete = array("server" => "/dev/shm/server_*", "answer" => "/dev/shm/answer_*", 
            "variable" => "/dev/shm/variable_*","worker" => "/dev/shm/worker" );

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
                shell_exec("rm " . $file_to_delete);
            }
        }
    }

}
