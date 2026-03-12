<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for display workflows.
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
class Display
{
/**
 * Stores `$server` for server.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $server      = array();
/**
 * Stores `$ts_variable` for ts variable.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $ts_variable = array();

/**
 * Handle display state through `server`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $arr Input value for `arr`.
 * @phpstan-param mixed $arr
 * @psalm-param mixed $arr
 * @return mixed Returned value for server.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::server()
 * @example /fr/display/server
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function server($arr)
    {
        return '<span title="'.$arr['libelle'].'" class="label label-'.$arr['class'].'">'.$arr['letter'].'</span>'
            .' <a href="">'.$arr['display_name'].'</a> <small class="text-muted">'.$arr['ip'].'</small>';
    }

/**
 * Handle display state through `srv`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $withip Input value for `withip`.
 * @phpstan-param mixed $withip
 * @psalm-param mixed $withip
 * @param mixed $url Input value for `url`.
 * @phpstan-param mixed $url
 * @psalm-param mixed $url
 * @return mixed Returned value for srv.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::srv()
 * @example /fr/display/srv
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function srv($id_mysql_server, $withip = true, $url = '')
    {
        if (empty(self::$server)) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server
            FROM mysql_server a            
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id";

            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$server[$arr['id_mysql_server']] = $arr;
            }
        }

        $url1 = '';
        $url2 = '';

        if (!empty($url)) {
            $url1 = '<a href="'.$url.'">';
            $url2 = '</a>';
        }

        $ret = '<span title="'.self::$server[$id_mysql_server]['libelle'].'" class="label label-'.self::$server[$id_mysql_server]['class'].'">'.self::$server[$id_mysql_server]['letter'].'</span>'
            ." ".$url1.self::$server[$id_mysql_server]['display_name'].$url2.' ';

        if ($withip) {
            $ret .= '<small class="text-muted">'.self::$server[$id_mysql_server]['ip'].':'.self::$server[$id_mysql_server]['port'].'</small> ';
        }

        return $ret;
    }

/**
 * Handle display state through `srvjs`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @return mixed Returned value for srvjs.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::srvjs()
 * @example /fr/display/srvjs
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function srvjs($id_mysql_server)
    {
        if (empty(self::$server)) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id";

            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$server[$arr['id_mysql_server']] = $arr;
            }
        }

        $ret = self::$server[$id_mysql_server]['display_name'];

        return $ret;
    }

/**
 * Handle display state through `icon`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $icon Input value for `icon`.
 * @phpstan-param mixed $icon
 * @psalm-param mixed $icon
 * @return mixed Returned value for icon.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::icon()
 * @example /fr/display/icon
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function icon($icon)
    {
        return str_replace(array('[IMG]', '{IMG}'), IMG, $icon);
    }

/**
 * Handle display state through `icon32`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $icon Input value for `icon`.
 * @phpstan-param mixed $icon
 * @psalm-param mixed $icon
 * @return mixed Returned value for icon32.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::icon32()
 * @example /fr/display/icon32
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function icon32($icon)
    {
        $icon = preg_replace('/height="(\d+)"/', 'height="32"', $icon);
        $icon = preg_replace('/width="(\d+)"/', 'width="32"', $icon);

        return str_replace(array('[IMG]', '{IMG}'), IMG, $icon);
    }

/**
 * Handle display state through `ts_variable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_ts_variable Input value for `id_ts_variable`.
 * @phpstan-param int $id_ts_variable
 * @psalm-param int $id_ts_variable
 * @return mixed Returned value for ts_variable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::ts_variable()
 * @example /fr/display/ts_variable
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function ts_variable($id_ts_variable)
    {
        if (empty(self::$ts_variable)) {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT id, name from ts_variable";

            $res = $db->sql_query($sql);

            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$ts_variable[$arr['id']] = $arr;
            }
        }

        $ret = self::$ts_variable[$id_ts_variable]['name'];

        return $ret;
    }
}
