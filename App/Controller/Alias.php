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
use App\Library\Extraction;
use App\Library\System;
use App\Library\Mysql;
use App\Library\Color;

class Alias extends Controller
{
    static $hostname = array();

    public function index()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,ROW_START,ROW_END FROM alias_dns a
        ORDER BY dns, port";

        $res = $db->sql_query($sql);

        $data['alia_dns'] = array();

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['alia_dns'][] = $ob;
        }

        $this->set('data', $data);
    }

    /**
     * need to add some other way like display_name in mysql_server
     * need to add ipv6 match
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param void
     * @return void
     * @description try to find different way to match master_host and master_port that we cannot find in table mysql_server
     * @access public
     * @example pmacontrol alias updateAlias
     * @package Pmacontrol
     * @since 2.0.25
     * @version 1.0
     */
    public function updateAlias($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $host = $this->getExtraction(array("slave::master_host", "slave::master_port"));

        $alias_to_add = array();

        foreach ($host as $key => $elem) {

            if (!Mysql::getIdFromDns($key)) {
                Debug::debug($key, "TO find");

                $alias_to_add[] = $this->getIdfromDns(array($elem['_HOST'], $elem['_PORT']));
                $alias_to_add[] = $this->getIdfromHostname(array($elem['_HOST'], $elem['_PORT']));
            }
        }

        Debug::debug($alias_to_add, "Alias found");

        foreach ($alias_to_add as $tab) {
            if (!is_array($tab)) {
                continue;
            }

            foreach ($tab as $id_mysql_server => $server) {

                //debug($server);
                $alias_dns                                 = array();
                $alias_dns['alias_dns']['id_mysql_server'] = $id_mysql_server;
                $alias_dns['alias_dns']['dns']             = $server['_HOST'];
                $alias_dns['alias_dns']['port']            = $server['_PORT'];
                $ret                                       = $db->sql_save($alias_dns);

                if (!$ret) {
                    Debug::debug($db->sql_error());
                }
            }
        }

        if (!IS_CLI) {
            header("location: ".LINK."alias/index");
        }
    }

    public function getExtraction($param)
    {
        $var_host = $param[0];
        $var_port = $param[1];

        $list = Extraction::display(array($var_host, $var_port));

        $host = explode('::', $var_host)[1];
        $port = explode('::', $var_port)[1];

        $list_host = array();
        foreach ($list as $masters) {
            foreach ($masters as $master) {
                $list_host[$master[$host].':'.$master[$port]]          = $master;
                $list_host[$master[$host].':'.$master[$port]]['_HOST'] = $master[$host];
                $list_host[$master[$host].':'.$master[$port]]['_PORT'] = $master[$port];
            }
        }

        return $list_host;
    }

    /**
     * need improvement we need loop on all server not only the one we looking for
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param array
     * @return array or false if not found
     * @description match hostname and port from SHOW VARIABLES STATUS.
     * @access public
     * @example $this->getIdfromDns(array($HOST, $PORT));
     * @package Pmacontrol
     * @See Alias\updateAlias
     * @since 2.0.25
     * @version 1.0
     */
    public function getIdfromDns($param)
    {

        $host = $param[0];
        $port = $param[1];

//get IP from DNS
        $ip = System::getIp($host);
        if ($ip !== false) {
            $id = Mysql::getIdFromDns($ip.":".$port);
            if ($id != false) {

                $alias_found               = array();
                $alias_found[$id]['_HOST'] = $host;
                $alias_found[$id]['_PORT'] = $host;

                return $alias_found;
            }
        }

        return false;
    }

    /**
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param array
     * @return array or false if not found
     * @description match hostname and port from SHOW VARIABLES STATUS.
     * @access public
     * @example $this->getIdfromHostname(array(array($HOST, $PORT)));
     * @package Pmacontrol
     * @See Alias\updateAlias
     * @since 2.0.25
     * @version 1.0
     */
    public function getIdfromHostname($param)
    {

        $host = $param[0];
        $port = $param[1];

        if (count(self::$hostname) === 0) {
            self::$hostname = $this->getExtraction(array("variables::hostname", "variables::port"));
            Debug::debug(self::$hostname, "hostname and port from SHOW SLAVE STATUS");
        }

        if (!empty(self::$hostname[$host.":".$port])) {

            $var = self::$hostname[$host.":".$port];

            $tmp                                   = array();
            $tmp[$var['id_mysql_server']]['_HOST'] = $var['_HOST'];
            $tmp[$var['id_mysql_server']]['_PORT'] = $var['_PORT'];

            return $tmp;
        }

        return false;
    }
}