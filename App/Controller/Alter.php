<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Debug;

/**
 * Class responsible for alter workflows.
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
class Alter extends Controller
{

/**
 * Handle alter state through `dropsp`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dropsp.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dropsp()
 * @example /fr/alter/dropsp
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dropsp($param)
    {
        Debug::parseDebug($param);
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'maria%'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);
            $remote->sql_query("DROP PROCEDURE IF EXISTS criteoadmin_db.mask_users;");
            $remote->sql_query("DROP PROCEDURE IF EXISTS criteoadmin_db.unmask_users;");
        }
    }

/**
 * Handle alter state through `slave`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for slave.
 * @phpstan-return void
 * @psalm-return void
 * @see self::slave()
 * @example /fr/alter/slave
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function slave($param)
    {
        Debug::parseDebug($param);

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'maria%'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);

            $gg = $remote->isSlave();

            if ($gg != false) {
                Debug::debug("slave");

                $remote->sql_query("STOP SLAVE;");
                $remote->sql_query("SET GLOBAL read_only = 1;");
                $remote->sql_query("SET GLOBAL replicate_wild_ignore_table = '';");
                $remote->sql_query("START SLAVE;");
            }
        }
    }

/**
 * Handle alter state through `user`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for user.
 * @phpstan-return void
 * @psalm-return void
 * @see self::user()
 * @example /fr/alter/user
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function user($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'maria37'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);
            $gg     = $remote->isSlave();

            if ($gg === false) {
                Debug::debug("isMAster");
                $sql2 = "SELECT user,host from mysql.user where user not in ('adminprod', 'root')";
                $res2 = $remote->sql_query($sql2);

                while ($ob2 = $remote->sql_fetch_object($res2)) {

                    $sql3 = "SHOW GRANTS FOR '".$ob2->user."'@'".$ob2->host."';\n";
                    $res3 = $remote->sql_query($sql3);

                    while ($ob3 = $remote->sql_fetch_array($res3, MYSQLI_NUM)) {

                        $remote->sql_query($ob3[0]);
                    }
                }
            }
        }
    }

/**
 * Handle alter state through `dropRoot`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dropRoot.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dropRoot()
 * @example /fr/alter/dropRoot
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dropRoot($param)
    {
        Debug::parseDebug($param);
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from mysql_server where id_client = 5 and display_name like 'maria%'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            Debug::debug($ob->display_name, 'Display name');
            $remote = Sgbd::sql($ob->name);
            $sql2   = "SELECT user,host from mysql.user where user = 'root' and host like 'ma%';";
            $res2   = $remote->sql_query($sql2);

            while ($ob2 = $remote->sql_fetch_object($res2)) {

                Debug::debug($ob2->host);
                $remote->sql_query("SET SQL_LOG_BIN=0;");
                $remote->sql_query("DROP USER 'root'@'".$ob2->host."';");
            }
        }
    }
}
