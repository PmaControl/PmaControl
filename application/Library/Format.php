<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Format
{

    static public function bytes($bytes, $decimals = 2)
    {
        $sz     = array(' ', 'K', 'M', 'G', 'T', 'P');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
    }

    static public function mysqlVersion($version)
    {
        if (strpos($version, "-")) {
            $number = explode("-", $version)[0];
            $fork   = explode("-", $version)[1];
        } else {
            $number = $version;
        }

        switch (strtolower($fork)) {
            case 'mariadb':
                $name = '<span class="geek">&#xF130;</span> MariaDB';
                break;

            case 'percona':
                $name = 'percona';
                break;

            default:
                $name = '<span class="geek">&#xF137;</span> MySQL';
        }

        return $name." ".$number;
    }

    static public function ping($microtime, $precision = 2)
    {
        $units = array('ms', 's');

        $microtime = $microtime * 1000;

        if ($microtime > 1000) {
            $microtime = $microtime / 1000;
            $pow       = 1;
        } else {
            $pow = 0;
        }

        return round($microtime, $precision).' '.$units[$pow];
    }
}