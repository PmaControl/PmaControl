<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Extraction;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use App\Library\Extraction2;

/**
 * Class responsible for alert workflows.
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
class Alert extends Controller
{
/**
 * Stores `$to_check` for to check.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $to_check = array("wsrep_cluster_size", "wsrep_cluster_name", "wsrep_on");

/**
 * Handle alert state through `check`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $date Input value for `date`.
 * @phpstan-param mixed $date
 * @psalm-param mixed $date
 * @param int $id_servers Input value for `id_servers`.
 * @phpstan-param int $id_servers
 * @psalm-param int $id_servers
 * @return void Returned value for check.
 * @phpstan-return void
 * @psalm-return void
 * @see self::check()
 * @example /fr/alert/check
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function check($date, $id_servers)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $res = Extraction($this->to_check, $id_servers, $date);

        while ($ob = $db->sql_fetch_object($res)) {

        }
    }

/**
 * Handle alert state through `reboot`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for reboot.
 * @phpstan-return void
 * @psalm-return void
 * @see self::reboot()
 * @example /fr/alert/reboot
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function reboot($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0];

        $sql = "SELECT b.date, b.date_p1, b.date_p2, b.date_p3, b.date_p4 FROM `ts_variable` a
            INNER JOIN `ts_max_date` b ON a.`id_ts_file` = b.`id_ts_file`
            WHERE a.name='uptime' and a.`from` = 'status' and b.id_mysql_server=".$id_mysql_server.";
            ";

        $res = $db->sql_query($sql);

        $list_dates = array();
        while ($arr        = $db->sql_fetch_array($res, MYSQLI_NUM)) {

            Debug::debug($arr);
            foreach ($arr as $date) {
                $list_dates[] = $date;
            }
        }

        Debug::debug($list_dates, "DATES");
        Debug::sql($sql);

        $uptime = Extraction2::extract(array('status::Uptime'), array($id_mysql_server), $list_dates);

        Debug::debug($uptime, "UPTIME");

        //$sql = "SELECT * FROM ts_max_date WHERE id_mysql_server"=.$id_mysql_server;
        //while ($)
        //extract($var = array(), $server = array(), $date = "", $range = false, $graph = false) {
        //display($var = array(), $server = array(), array("")) {
    }

    /**
     * Summary of test
     * @return void
     */
    public function test()
    {
        $fghsfgh = 0;

        if ($fghsfgh == "q<sgf")        {
            echo "dqwfgqdfg";
        }
    }
}
