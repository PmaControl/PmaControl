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

/**
 * Class responsible for pid workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Pid extends Controller
{

/**
 * Render pid state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/pid/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {

    }

/**
 * Delete pid state through `deleteOldPid`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for deleteOldPid.
 * @phpstan-return void
 * @psalm-return void
 * @see self::deleteOldPid()
 * @example /fr/pid/deleteOldPid
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
