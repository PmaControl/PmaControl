<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\System;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;

class Pid extends Controller
{

    public function index()
    {

    }

    public function deleteOldPid($param)
    {
        Debug::parseDebug($param);

        $directories = glob(TMP."lock/worker*");

        foreach ($directories as $directory) {

            $pids = glob($directory.'/*.pid');

            foreach ($pids as $pid) {

                $pid_num = pathinfo(pid)['filename'];

                if (System::isRunningPid($pid_num)) {
                    //remove file
                    Debug::debug($pid, "removed old pid");
                    unlink($pid);
                }
            }
            Debug::debug($pid);
        }
    }
}