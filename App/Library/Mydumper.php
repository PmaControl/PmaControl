<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

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

    static function colorStatus($log)
    {
        $log1 = str_replace("CRITICAL", '<big><span class="label label-danger">CRITICAL</span></big>', $log);
        $log2 = str_replace("WARNING", '<big><span class="label label-warning">WARNING</span></big>', $log1);
        return $log2;
    }

    static function getLevel($file_log)
    {
        preg_match_all('/\*\*\s\(mydumper\:[0-9]+\)\: ([A-Z]+)\s/', $input_line, $output_array);
    }
}