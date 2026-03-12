<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Class responsible for mydumper workflows.
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
class Mydumper
{

    /**
     *
     * Liste des erreurs :
     *
     *
     * WARNING
     * CRITICAL
     *
     *
     */
    static public function parseLog($log)
    {
        $log = str_replace(array("\n\n", "\n"), array("\n", "<br>"), trim($log));
        //


        //preg_match_all('/\*\*\s\(mydumper\:[0-9]+\)\: ([A-Z]+)\s/', $input_line, $output_array);

        return self::colorStatus($log);
    }

/**
 * Handle mydumper state through `colorStatus`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $log Input value for `log`.
 * @phpstan-param mixed $log
 * @psalm-param mixed $log
 * @return mixed Returned value for colorStatus.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::colorStatus()
 * @example /fr/mydumper/colorStatus
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function colorStatus($log)
    {
        $log1 = str_replace("CRITICAL", '<big><span class="label label-danger">CRITICAL</span></big>', $log);
        $log2 = str_replace("WARNING", '<big><span class="label label-warning">WARNING</span></big>', $log1);
        return $log2;
    }

/**
 * Retrieve mydumper state through `getLevel`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file_log Input value for `file_log`.
 * @phpstan-param mixed $file_log
 * @psalm-param mixed $file_log
 * @return void Returned value for getLevel.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getLevel()
 * @example /fr/mydumper/getLevel
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getLevel($file_log)
    {
        preg_match_all('/\*\*\s\(mydumper\:[0-9]+\)\: ([A-Z]+)\s/', $input_line, $output_array);
    }
}
