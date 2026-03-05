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
use App\Library\Extraction2;
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
        $this->addAliasFromSshIps($param);

        if (!IS_CLI) {
            header("location: ".LINK."alias/index");
        }
    }

    public function delete($param)
    {
        $this->view = false;

        $id_alias_dns = (int)($param[0] ?? 0);

        if ($id_alias_dns > 0) {
            $db = Sgbd::sql(DB_DEFAULT);
            $sql = "DELETE FROM alias_dns WHERE id = ".$id_alias_dns." LIMIT 1;";
            $db->sql_query($sql);
        }

        header("location: ".LINK."alias/index");
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

        return [];
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

        return [];
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
        // $param = [dns, port, id_mysql_server, is_from_ssh]
        $dns = $param[0] ?? null;
        $port = $param[1] ?? null;
        $id_mysql_server = $param[2] ?? null;
        $is_from_ssh = isset($param[3]) ? (int)$param[3] : 0;

        if (!in_array($is_from_ssh, [0, 1], true)) {
            $is_from_ssh = 0;
        }

        if (!$dns || !$port || !$id_mysql_server) {
            return; // paramètre manquant
        }

        $db = Sgbd::sql(DB_DEFAULT);

        // clé pour le cache
        $key = "$dns:$port";

        // si déjà en cache et identique, on n'update pas
        if (isset(self::$alias_dns_cache[$key])) {
            $cached = self::$alias_dns_cache[$key];
            $cached_server = (int)($cached['id_mysql_server'] ?? 0);
            $cached_is_from_ssh = (int)($cached['is_from_ssh'] ?? 0);

            if (
                $cached_server === (int)$id_mysql_server
                && $cached_is_from_ssh === $is_from_ssh
            ) {
                return;
            }
        }

        // éviter les doublons avec INSERT ... ON DUPLICATE KEY UPDATE (assurant que dns et port sont uniques)
        $sqlUpsert = sprintf(
            "INSERT INTO alias_dns (dns, port, id_mysql_server, is_from_ssh) VALUES ('%s', %d, %d, %d) ON DUPLICATE KEY UPDATE id_mysql_server = VALUES(id_mysql_server), is_from_ssh = VALUES(is_from_ssh)",
            $db->sql_real_escape_string($dns),
            $port,
            $id_mysql_server,
            $is_from_ssh
        );
        $db->sql_query($sqlUpsert);
        self::$alias_dns_cache[$key] = [
            'id_mysql_server' => (int)$id_mysql_server,
            'is_from_ssh' => $is_from_ssh,
        ];
    }

    public function addAliasFromSshIps($param)
    {
        $this->view = false;

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        // id_mysql_server => port
        $port_by_server = [];
        $sql = "SELECT id, port FROM mysql_server WHERE is_deleted = 0";
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $port_by_server[(int)$ob['id']] = (int)$ob['port'];
        }

        $ssh_ips = Extraction2::display(array("ssh_hardware::ips"));

        // clé = dns:port
        $current_aliases_from_ssh = [];

        foreach ($ssh_ips as $id_mysql_server => $row) {
            $id_mysql_server = (int)$id_mysql_server;

            if (empty($port_by_server[$id_mysql_server])) {
                continue;
            }

            $port = (int)$port_by_server[$id_mysql_server];
            $ips = self::extractIpList($row['ips'] ?? []);

            foreach ($ips as $ip) {
                $key = strtolower($ip).":".$port;
                $current_aliases_from_ssh[$key] = [
                    'dns' => $ip,
                    'port' => $port,
                    'id_mysql_server' => $id_mysql_server,
                ];
            }
        }

        foreach ($current_aliases_from_ssh as $alias) {
            self::upsertAliasDns([
                $alias['dns'],
                $alias['port'],
                $alias['id_mysql_server'],
                1,
            ]);
        }

        // purge les entrées ssh obsolètes (plus présentes dans ssh_hardware::ips)
        $sql = "SELECT id, dns, port FROM alias_dns WHERE is_from_ssh = 1";
        $res = $db->sql_query($sql);

        $to_delete = [];
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $key = strtolower($ob['dns']).":".(int)$ob['port'];

            if (!isset($current_aliases_from_ssh[$key])) {
                $to_delete[] = (int)$ob['id'];
            }
        }

        if (!empty($to_delete)) {
            $sql = "DELETE FROM alias_dns WHERE id IN (".implode(',', $to_delete).")";
            $db->sql_query($sql);
        }
    }

    private static function extractIpList($raw): array
    {
        $list = [];

        $flatten = function ($value) use (&$flatten, &$list): void {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $flatten($item);
                }
                return;
            }

            $value = trim((string)$value);
            if ($value === '') {
                return;
            }

            foreach (preg_split('/[\s,;|]+/', $value) as $candidate) {
                $candidate = trim($candidate);

                if ($candidate === '') {
                    continue;
                }

                if (str_contains($candidate, '/')) {
                    $candidate = explode('/', $candidate)[0];
                }

                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    $list[$candidate] = $candidate;
                }
            }
        };

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            }
        }

        $flatten($raw);

        return array_values($list);
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
