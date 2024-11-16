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

        $name = '';
        $logo = true;
        
        if ($is_proxysql === "1")
        {
            $logo = false;
            $name .= '<img title="ProxySQL" alt="ProxySQL" height="14" width="14" src="'.IMG.'/icon/proxysql.png"/>';
        }

        

        switch (strtolower($fork)) {
            case 'mariadb':
                if ($logo)
                {
                    $name .= '<span class="geek">&#xF130;</span>';
                }
		        $name .=  ' MariaDB';
                break;

            case 'percona':
                if ($logo)
                {
                	$name .= '<img title="Percona Server" alt="Galera Cluster" height="16" width="16" src="'.IMG.'/icon/percona.svg"/>';
                }
		$name .= ' Percona Server';
                //$name = 'percona';
                break;

            case 'proxysql':
                $name .= ' ProxySQL';
                break;

            default:
                if ($logo)
		{
			$name .= '<span class="geek">&#xF137;</span>';
		}
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
                $logo = '<img title="Percona Server" alt="Galera Cluster" height="16" width="16" src="'.IMG.'/icon/percona.svg"/>';
                break;

            case 'proxysql':
                $logo = '<img title="ProxySQL" alt="ProxySQL" height="14" width="14" src="'.IMG.'/icon/proxysql.png"/>';
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
        if (strpos($version, "-")) {
            $number = explode("-", $version)[0];
            $fork   = explode("-", $version)[1];
        } else {
            $number = $version;
            $fork = 'mysql';
        }

        $pos = strpos(strtolower($comment), "percona");
        if ($pos !== false) {
            $fork = "percona";
        }

        $pos = strpos(strtolower($comment), "proxysql");
        if ($pos !== false) {
            $fork = "proxysql";
        }

        return array('number'=>$number, 'fork'=> $fork);
    }
}
