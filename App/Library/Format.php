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

    static public function mysqlVersion($version, $comment, $is_proxysql=0)
    {
        $format = self::getMySQLNumVersion($version, $comment);

        $fork = $format['fork'];
        $number = $format['number'];
        $enter = $format['enterprise'];

        $name = '';

        switch (strtolower($fork)) {
            case 'mariadb':
                $name .= '<span class="geek">&#xF130;</span>';
		        $name .=  ' MariaDB';
                if ($enter === true)
                {
                    $name .= ' (Enterprise)';
                }
                break;

            case 'percona':
                $name .= '<img title="Percona Server" alt="Percona Server" height="16" width="16" src="'.IMG.'/icon/percona.svg"/>';
		        $name .= ' Percona Server';
                //$name = 'percona';
                break;

            case 'proxysql':
                $name .= '<img title="ProxySQL" alt="ProxySQL" height="14" width="14" src="'.IMG.'/icon/proxysql.png"/>';
                $name .= ' ProxySQL';
                break;

            case 'maxscale':
                $name .= '<img title="MaxScale Server" alt="MaxScale Server" height="16" width="16" src="'.IMG.'/icon/maxscale.svg"/>';
                $name .= ' MaxScale';
                break; 

            case 'singlestore':
                $name .= '<img title="SingleStore" alt="SingleStore" height="16" width="16" src="'.IMG.'/icon/singlestore.svg"/>';
                $name .= ' SingleStore';
                break;

            default:
                $name .= '<span class="geek">&#xF137;</span>';
                $name .= ' MySQL';
        }

        return $name." ".$number;
    }


    static function getLogo($fork)
    {

        switch($fork)
        {

            case 'mysql':
                $logo = '<span class="geek">&#xF137;</span>';
                break;

            case 'mariadb':
                $logo = '<span class="geek">&#xF130;</span>';
                break;
            case 'percona':
                $logo = '<img title="Percona Server" alt="Percona Server" height="16" width="16" src="'.IMG.'/icon/percona.svg"/>';
                break;

            case 'proxysql':
                $logo = '<img title="ProxySQL" alt="ProxySQL" height="14" width="14" src="'.IMG.'/icon/proxysql.png"/>';
                break;

            case 'maxscale':
                $logo = '<img title="MaxScale" alt="MaxScale" height="16" width="16" src="'.IMG.'/icon/maxscale.png"/>';
                break;

            case 'singlestore':
                $logo = '<img title="SingleStore" alt="SingleStore" height="16" width="16" src="'.IMG.'/icon/singlestore.svg"/>';
                break;
        }

        return $logo;
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

    static public function getMySQLNumVersion($version, $comment)
    {
        //10.6.19-15-MariaDB-enterprise-log
        // need make test with that
        $enterprise = false;

        if (strpos($version, "-")) {
            $number = explode("-", $version)[0];
            $fork   = explode("-", $version)[1];
            
            if (preg_match('/^-?\d+$/', $fork)) {
                $fork   = explode("-", $version)[2];
                $enterprise = true;
            }
        } else {
            $number = $version;
            $fork = 'MySQL';
        }

        $pos = strpos(strtolower($comment), "percona");
        if ($pos !== false) {
            $fork = "Percona";
        }

        $pos = strpos(strtolower($comment), "proxysql");
        if ($pos !== false) {
            $fork = "ProxySQL";
        }

        $pos = strpos(strtolower($comment), "maxscale");
        if ($pos !== false) {
            $fork = "MaxScale";
        }

        $pos = strpos(strtolower($comment), "singlestore");
        if ($pos !== false) {
            $fork = "SingleStore";
        }

        return array('number'=>$number, 'fork'=> $fork, 'enterprise'=> $enterprise);
    }
}
