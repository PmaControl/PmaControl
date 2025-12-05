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

    private static array $alias_dns_cache = [];

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
        $this->view = false;

        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $host = $this->getExtraction(array("slave::master_host", "slave::master_port"));

        Debug::debug($host, "HOST");
        
        $alias_to_add = array();

        foreach ($host as $key => $elem) {

            if (!Mysql::getIdFromDns($key)) {
                Debug::debug($key, "Server to match");

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

        $this->addHostname($param);
        $this->addAliasFromHostname($param);
        $this->addAliasFromWsrepNodeAddress($param);

        if (!IS_CLI) {
            header("location: ".LINK."alias/index");
        }
    }

    public function getExtraction($param)
    {
        $var_host = $param[0];
        $var_port = $param[1];
        //variables::is_proxysql

        $list = Extraction::display($param);

        Debug::debug($list);

        $host = explode('::', $var_host)[1];
        $port = explode('::', $var_port)[1];

        $list_host = array();
        foreach ($list as $masters) {
            foreach ($masters as $master) {

                if (!empty($master['is_proxysql']) && $master['is_proxysql'] === "1") {
                    continue;
                }

                if (isset($master[$host]) && !empty($master[$host]) && isset($master[$port]) && !empty($master[$port])) {
                    $list_host[$master[$host].':'.$master[$port]]          = $master;
                    $list_host[$master[$host].':'.$master[$port]]['_HOST'] = $master[$host];
                    $list_host[$master[$host].':'.$master[$port]]['_PORT'] = $master[$port];
                }
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
                $alias_found[$id]['_PORT'] = $port;
                $alias_found[$id]['_FROM'] = __FUNCTION__;

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
            self::$hostname = $this->getExtraction(array("variables::hostname", "variables::port", "variables::is_proxysql"));
            Debug::debug(self::$hostname, "hostname and port from SHOW SLAVE STATUS");
        }


        if (!empty(self::$hostname[$host.":".$port])) {

            $var = self::$hostname[$host.":".$port];

            $tmp                                   = array();
            $tmp[$var['id_mysql_server']]['_HOST'] = $var['_HOST'];
            $tmp[$var['id_mysql_server']]['_PORT'] = $var['_PORT'];
            $tmp[$var['id_mysql_server']]['_FROM'] = __FUNCTION__;

            return $tmp;
        }

        return false;
    }


    public function addHostname($param)
    {
        $this->view = false;

        Debug::debug($param);
        
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "INSERT IGNORE INTO alias_dns (id_mysql_server, dns, port)
        SELECT ms.id, ms.hostname, ms.port
        FROM mysql_server ms
        LEFT JOIN alias_dns ad 
            ON ms.hostname = ad.dns AND ms.port = ad.port
        WHERE ad.dns IS NULL;";

        $db->sql_query($sql);

    }

    public function addAliasFromHostname($param)
    {
        $this->view = false;

        Debug::parseDebug($param);
        $hostnames = $this->getExtraction(array("variables::hostname","variables::port", "variables::is_proxysql" ));

        foreach($hostnames as $hostname)
        {
            if (!empty($hostname['is_proxysql']) && $hostname['is_proxysql'] === "1") {
                continue;
            }

            self::upsertAliasDns([$hostname['_HOST'], $hostname['_PORT'], $hostname['id_mysql_server']]);
        }
    }
    public static function upsertAliasDns(array $param): void
    {
        // $param = [dns, port, id_mysql_server]
        $dns = $param[0] ?? null;
        $port = $param[1] ?? null;
        $id_mysql_server = $param[2] ?? null;

        if (!$dns || !$port || !$id_mysql_server) {
            return; // paramètre manquant
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // clé pour le cache
        $key = "$dns:$port";

        // si déjà en cache et différent, update
        if (isset(self::$alias_dns_cache[$key])) {
            if (self::$alias_dns_cache[$key] !== $id_mysql_server) {
                $sqlUpsert = sprintf(
                    "INSERT INTO alias_dns (dns, port, id_mysql_server) VALUES ('%s', %d, %d) ON DUPLICATE KEY UPDATE id_mysql_server = %d",
                    $db->sql_real_escape_string($dns),
                    $port,
                    $id_mysql_server,
                    $id_mysql_server
                );
                $db->sql_query($sqlUpsert);
                self::$alias_dns_cache[$key] = $id_mysql_server;
            }
            return;
        }

        // éviter les doublons avec INSERT ... ON DUPLICATE KEY UPDATE (assurant que dns et port sont uniques)
        $sqlUpsert = sprintf(
            "INSERT INTO alias_dns (dns, port, id_mysql_server) VALUES ('%s', %d, %d) ON DUPLICATE KEY UPDATE id_mysql_server = %d",
            $db->sql_real_escape_string($dns),
            $port,
            $id_mysql_server,
            $id_mysql_server
        );
        $db->sql_query($sqlUpsert);
        self::$alias_dns_cache[$key] = $id_mysql_server;
    }

    public function addAliasFromWsrepNodeAddress($param)
    {
        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);

        Debug::parseDebug($param);
        $wsrep_addresses = $this->getExtraction(array("variables::wsrep_node_address","variables::port", "variables::is_proxysql"));

        foreach($wsrep_addresses as $wsrep)
        {
            if (!empty($wsrep['is_proxysql']) && $wsrep['is_proxysql'] === "1") {
                continue;
            }

            // vérifier si c'est différent du hostname dans mysql_server (cas NAT)
            $sql = "SELECT hostname FROM mysql_server WHERE id = ".$wsrep['id_mysql_server'];
            $res = $db->sql_query($sql);
            if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $hostname_db = $row['hostname'];
                if ($wsrep['_HOST'] !== $hostname_db) {
                    // ajouter l'alias si wsrep_node_address != hostname (scénario NAT)
                    self::upsertAliasDns([$wsrep['_HOST'], $wsrep['_PORT'], $wsrep['id_mysql_server']]);
                }
            }
        }
    }

    public static function clearAliasDnsCache(): void
    {
        self::$alias_dns_cache = [];
    }
}
