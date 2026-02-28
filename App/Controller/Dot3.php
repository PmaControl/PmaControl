<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\Graphviz;
use Exception;
use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Extraction2;
use \App\Library\Debug;
use App\Library\Country;
use App\Library\Color;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


use \Glial\Sgbd\Sgbd;

// #01a31c green

// ""	&#9635;   ▣
// "□"	&#9633;	&#x25A1;
// ""	&#9679;  ●
//"○"	&#9675;
//"◇"	&#9671;	&#x25C7;
//"◈"	&#9672;	&#x25C8;
// Joining: receiving State Transfer   => IST change color
//GRANT SELECT, RELOAD, PROCESS, SUPER ON *.* TO 'xxxx'@'192.168.1.150';
//add virtual_ip
// ha proxy
// https://renenyffenegger.ch/notes/tools/Graphviz/examples/index  <= to check for GTID (nice idea)
class Dot3 extends Controller
{

    /*
    * récupére toutes les infomations du serveur à un date t   
    */
    use \App\Library\Filter;
    use \App\Library\Dot;

    const TARGET = 'target';
    const VIP_ACTIVE_PORT = 'vip_active';
    const VIP_PREVIOUS_PORT = 'vip_previous';


    static $id_dot3_information;

    static $information = array();

    // build link MasterSlave
    static $build_ms = array();

    // build server
    static $build_server = array();

    static $build_galera = array();

    static $config = array();

    static $galera = array();

    static $rank_same = array();

    var $logger;

    public function before($param)
    {
        $this->loadConfigColor();
        $monolog       = new Logger("Dot3");
        $handler      = new StreamHandler(LOG_FILE, Logger::WARNING);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function generateInformation($param)
    {
        

	    Debug::parseDebug($param);

        //Debug::$debug=true;

        $date_request = $param[0] ?? "";
        $versioning = " WHERE 1=1 ";
        $versioning2 = " WHERE 1=1 ";
        $versioning3 = " WHERE 1=1 ";

        //to prevent id of daemon in comment
        if (! is_a($date_request, 'DateTime')) {
            $date_request ="";
        }

        $remove_not_monitored = false;

        if ( ! empty($date_request))
        {
            $versioning = "WHERE '".$date_request."' between a.row_start and a.row_end ";
            $versioning2 = "WHERE '".$date_request."' between b.row_start and b.row_end AND '".$date_request."' between c.row_start and c.row_end ";
            $versioning3 = "WHERE '".$date_request."' between d.row_start and d.row_end AND '".$date_request."' between e.row_start and e.row_end ";
            $date_request = array($date_request);
        }
        else{
            $remove_not_monitored = true;
        }

        //Debug::debug($date_request, "Date");

        $db  = Sgbd::sql(DB_DEFAULT);

        $sql2 = "SELECT a.id, a.is_proxy, a.is_vip
        FROM mysql_server a
        INNER JOIN client x ON x.id = a.id_client
        ".$versioning."
        AND x.is_monitored = 1";

        $id_mysql_servers = [];
        $id_mysql_servers__proxy = [];
        $id_mysql_servers__vip = [];
        $id_mysql_servers__real = [];

        $res2 = $db->sql_query($sql2);
        while ($arr = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $id_mysql_servers[] = $arr['id'];

            if ($arr['is_proxy'] === "1")
            {
                $id_mysql_servers__proxy[] = $arr['id'];
            }
            else if ($arr['is_vip'] === "1")
            {
                $id_mysql_servers__vip[] = $arr['id'];
            }
            else{
                $id_mysql_servers__real[] = $arr['id'];
            }
        }

        Debug::debug($id_mysql_servers__real, "id_mysql_servers__real");

        //$id_mysql_servers = [87,88,116];
        // "status::wsrep_cluster_status"  => not exist anymore ?

        $all = [];

        // to split en 3 morceau
        $all = Extraction2::display(array("variables::hostname", "variables::binlog_format", "variables::time_zone", "variables::version",
                "variables::system_time_zone", "variables::port", "variables::is_proxysql", "variables::is_proxy", "variables::is_maxscale",
                "variables::wsrep_cluster_address","slave::connection_name",
                "variables::wsrep_node_address", "variables::wsrep_cluster_name", "variables::wsrep_provider_options", "variables::wsrep_on", "variables::wsrep_sst_method",
                "variables::wsrep_desync", "status::wsrep_local_state", "status::wsrep_local_state_comment", "status::wsrep_cluster_status",
                "status::wsrep_incoming_addresses", "variables::wsrep_patch_version", "mysql_ping", "mysql_server::error",
                "status::wsrep_cluster_size", "status::wsrep_cluster_state_uuid", "status::wsrep_gcomm_uuid", "status::wsrep_local_state_uuid",
                "slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running","variables::wsrep_slave_threads",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "mysql_available", "mysql_error","variables::version_comment","is_proxy", "variables::server_id","read_only",
                "slave::last_sql_error", "slave::last_sql_errno", "slave::using_gtid", "variables::binlog_row_image",
                "proxysql_runtime::global_variables","proxysql_runtime::mysql_servers", "proxysql_runtime::mysql_galera_hostgroups", 
                "proxysql_connect_error::proxysql_connect_error", "proxysql_runtime::proxysql_servers",
                "proxysql_runtime::runtime_mysql_query_rules", "proxysql_runtime::mysql_replication_hostgroups",
                "proxysql_runtime::mysql_group_replication_hostgroups", "master_ssl_allowed",
                "maxscale::maxscale_listeners", "maxscale::maxscale_servers","maxscale::maxscale_services", "maxscale::maxscale_monitors", 
                "auto_increment_increment", "auto_increment_offset", "log_slave_updates", "variables::system_time_zone", "status::wsrep_provider_version",
                "ssh_stats::mysql_datadir_path", "ssh_stats::mysql_datadir_total_size", "ssh_stats::mysql_datadir_clean_size",
                "ssh_stats::mysql_sst_elapsed_sec", "ssh_stats::mysql_sst_in_progress", 
                "vip::destination_id", "vip::destination_date","vip::destination_previous_id", "vip::destination_previous_date",
            ),$id_mysql_servers , $date_request);
/***/

        $this->mergeVipDnsDataInInformation($all, $id_mysql_servers__vip, $date_request);



        // only valid server
        $sql = "SELECT a.id as id_mysql_server, ip, port, display_name, is_proxy, is_vip ,ip as ip_real, port as port_real
                FROM mysql_server a
                INNER JOIN client x ON x.id = a.id_client
                ".$versioning."
                AND x.is_monitored = 1 AND a.is_deleted=0
                UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy,c.is_vip, c.ip as ip_real, c.port as port_real
                FROM alias_dns b 
                INNER JOIN mysql_server c ON b.id_mysql_server =c.id 
                INNER JOIN client y ON y.id = c.id_client ".$versioning2." AND y.is_monitored = 1 
                UNION select d.id_mysql_server, d.hostname, d.port,d.display_name ,  1, 0, d.hostname, d.port 
                FROM proxysql_server d
                INNER JOIN mysql_server e ON e.id = d.id_mysql_server
                INNER JOIN client z ON z.id = e.id_client
                ".$versioning3.";";

        Debug::sql($sql, "GET SERVER LIST");

        $res = $db->sql_query($sql);

        $server_mysql = array();
        //$mapping_master = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_mysql[$arr['id_mysql_server']] = $arr;

            //pas d'alias pour les VIP
            if (in_array($arr['id_mysql_server'], $id_mysql_servers__vip))
            {
                continue;
            }

            $data['mapping'][$arr['ip'].':'.$arr['port']] = $arr['id_mysql_server'];
            // add tunnel 
        }

        $data['tunnel'] =  Tunnel::getTunnelsMapping([$date_request]);

        //Debug::debug($data['tunnel'], "TUNNEL");
        ksort($data['mapping']);

        //Debug::debug(MaxScale::removeArraysDeeperThan($all['125'], 4), "YYYYYYYYYYYYYYYYY");
        $data['servers'] = array_replace_recursive($all, $server_mysql);
        //Debug::debug($data['servers'][125]);

        //Debug::debug(MaxScale::removeArraysDeeperThan($data['servers']['125'], 5), "XXXXXXXXXXXXXXXXXXXXXXXXXXX");

        // ca sert a rien en fait car on recupère les élements du serveur mysql_server associé
        // just interessant pour faire des traitements spécifique
        $sql = "select `id`, `id_mysql_server`, `hostname`, `port` from proxysql_server a $versioning AND a.id_mysql_server IS NOT NULL;";
        Debug::debug($sql);

        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'][$arr['id_mysql_server']] = array_merge($arr, $data['servers'][$arr['id_mysql_server']]);

            if (! empty($data['servers'][$arr['id_mysql_server']]['global_variables']))
            {
                //data['servers'][$arr['id_mysql_server']]['global_variables'] = 
                //$this->reOrderVariable($data['servers'][$arr['id_mysql_server']]['global_variables']);
                
                $data['servers'][$arr['id_mysql_server']]['version'] = $data['servers'][$arr['id_mysql_server']]['global_variables']['admin-version'];
                $data['servers'][$arr['id_mysql_server']]['version_comment'] = "ProxySQL"; 

            }
            //json decode in same time
            //Debug::debug($data['servers'][$arr['id_mysql_server']], "JSON");
            //exit;
            //$data['servers'][$arr['id_mysql_server']]['mysql_servers'] = json_decode($data['servers'][$arr['id_mysql_server']]['mysql_servers'], true);
        }

        //TO REMOVE just for TEST proxysql

        /*
        $servers = ProxySQL::getErrorConnect(array(1));
        $ret = array();
        foreach($servers as $server)
        {
            $ret[$server['hostname'].':'.$server['port']] = $server['error'];
        }
        $data['servers'][65]['proxy_connect_error'] = $ret;
        */
        
        //end
        //to remove
        //stats_mysql_processlist
        //end

        $sql = "select * from mysql_database WHERE schema_name not in ('performance_schema','information_schema')";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            if (empty($data['servers'][$ob->id_mysql_server]['is_proxy']))
            {
                $data['servers'][$ob->id_mysql_server]['mysql_database'][] = $ob->schema_name;
            }
        }

        //Debug::debug($data, "SERVER");

        //insert to DB
        $data_for_md5 = $data;


        //Debug::debug(MaxScale::removeArraysDeeperThan($data['servers']['124'], 5), "DATA");

        //remove all date to be able to compare data with date
        array_walk_recursive($data_for_md5, function(&$value, $key) use (&$array) {
            if ($key == 'date') {
                $value = null; // ou utilisez unset si vous pouvez obtenir une référence au parent.
            }
        });

        $json = json_encode($data);
        $md5 = md5(json_encode($data_for_md5));


        $this->logger->notice("MD5 : $md5");

        $previous_md5 = '';
        $dot3_information = self::getInformation('');

        //Debug::debug($dot3_information, 'Dot_information');
        
        if (!empty($dot3_information['md5'])) {
            $previous_md5 = $dot3_information['md5'];
            $id_dot3_information = $dot3_information['id'];
            self::$id_dot3_information = $dot3_information['id'];
        }

        if ($previous_md5 != $md5)
        {
            $dot3 = array();
            $dot3['dot3_information']['date_generated'] = date('Y-m-d H:i:s');
            $dot3['dot3_information']['information'] = $json;
            $dot3['dot3_information']['md5'] = $md5;
            $id_dot3_information =  $db->sql_save($dot3);
            self::$id_dot3_information = $id_dot3_information;
        }

        $this->logger->notice("id_dot3_information : $id_dot3_information");

        return $id_dot3_information;
    }

    public function generateGroupMasterSlave($information)
    {
        $id_group = 1;
        $tmp_group = array();

        foreach($information['servers'] as $server)
        {
            //Debug::debug($server, "SERVER");

            if (empty($server['@slave'])) {
                continue;
            }

            foreach($server['@slave']  as $slave) {

                $tmp_group[$id_group] = array();
                //Debug::debug($slave, "SLAVE");
                
                $tmp_group[$id_group][] = $server['id_mysql_server'];
                $master = $slave['master_host'].":" .$slave['master_port'];

                //Debug::debug($master, "master");

                if (! empty($information['mapping'][$master])) {
                    $tmp_group[$id_group][] = $information['mapping'][$master];
                }
                else {
                    echo "SCRIPT KILLED [ERROR] This master was not found : ".$master."\n";
                    Debug::debug($information['mapping'], "MAPPING");
                    
                }
                $id_group++;
                
            }   
        }

        //Debug::debug($tmp_group, "MASTER SLAVE");
        //die();
        return $tmp_group;
    }

    public function generateGroupProxySQL($information)
    {
        $tmp_group = array();
        //Debug::debug($information['servers'][65]);
        
        foreach($information['servers'] as $id_mysql_server => $server)
        {
            //Debug::debug($server, "SERVER is_proxy_SQL");

            if ( ! empty($server['is_proxysql']) && $server['is_proxysql'] != "1") {
                continue;
            }

            $tmp_group[$id_mysql_server][] = $id_mysql_server;

            if (! empty($server['mysql_servers']))
            {
                foreach($server['mysql_servers'] as $backend) {
                    $server = $backend['hostname'].':'.$backend['port'];
                    if (!empty($information['mapping'][$server]))
                    {
                        $tmp_group[$id_mysql_server][] = $information['mapping'][$server];
                    }
                }
            }
        }

        return $tmp_group;
    }

    public function generateGroupVip($information)
    {
        $tmp_group = array();

        if (empty($information['servers']) || !is_array($information['servers'])) {
            return $tmp_group;
        }

        foreach ($information['servers'] as $id_mysql_server => $server)
        {
            $id_source = (int) $id_mysql_server;

            $vip_links = array(
                $server['destination_id'] ?? null,
                $server['destination_previous_id'] ?? null,
            );

            foreach ($vip_links as $id_destination)
            {
                if ($id_destination === null || $id_destination === '' || $id_destination === '0') {
                    continue;
                }

                if (!is_numeric($id_destination)) {
                    continue;
                }

                $id_destination = (int) $id_destination;

                if ($id_destination <= 0) {
                    continue;
                }

                // Keep only links pointing to known/monitored servers.
                if (empty($information['servers'][$id_destination])) {
                    continue;
                }

                $tmp_group[$id_source][] = $id_source;
                $tmp_group[$id_source][] = $id_destination;
            }

            if (!empty($tmp_group[$id_source])) {
                $tmp_group[$id_source] = array_values(array_unique($tmp_group[$id_source]));
            }
        }

        return $tmp_group;
    }


    public function generateGroupGalera($information)
    {
        $tmp_group = array();

        //$id_group = 0;  // replaced by $server['id_mysql_server'] for test

        //Debug::debug($information['servers'], "SFGTHSFHGFG");

        foreach($information['servers'] as $id_mysql_server => $server)
        {
            $server['id_mysql_server'] = $id_mysql_server;

            //Debug::debug($server, "GOOD");
            //$id_group++;
            //$tmp_group[$idproxy] = array();
            if (!empty($server['wsrep_on']) && strtolower($server['wsrep_on']) === "on") {

                //Debug::debug($server, "CLUSTER");

                $servers = self::getIdMysqlServerFromGalera($server['wsrep_cluster_address']);
                $servers2 = self::getIdMysqlServerFromGalera($server['wsrep_incoming_addresses']);

                //Debug::debug($servers, "SERVERS");

                foreach($servers as $ip_port)
                {
                    if (!empty($information['mapping'][$ip_port])) {
                        
                        $id_mysql_server_galera = $information['mapping'][$ip_port];
                        //Debug::debug($id_mysql_server_galera, "ID MYSQL SERVER GALERA ({$server['id_mysql_server']})");
                        //Debug::debug($information['servers'][$id_mysql_server_galera]['wsrep_on'], "WSREP ON");

                        if ($information['servers'][$id_mysql_server_galera]['wsrep_on'] === 'ON') {

                            $tmp_group[$server['id_mysql_server']][] = $information['mapping'][$ip_port];
                            //generate Alert 
                            Debug::debug("WARNING: The server $ip_port (ID: $id_mysql_server_galera) 
                            is part of the Galera cluster but has wsrep_on set to OFF.", "GALERA CONFIGURATION WARNING");
                        }

                        Debug::debug($information['mapping'][$ip_port], "MAPPING");

                        
                    }
                    else {
                        //autodetect autoadd Mysql::autodetect($server['id_mysql_server'], $ip_port);
                    }
                }
                //$id_group++;
                //Debug::debug($servers2, "SERVERS2");


                foreach($servers2 as $ip_port)
                {
                    //Debug::debug($ip_port, "IP:PORT");
                    if ($ip_port[0] === ':') {
                        //detection arbitre
                        $id_garb = $this->createGarb($information, $id_mysql_server);
                        $tmp_group[$server['id_mysql_server']][] = $id_garb;
                    } 

                    if (!empty($information['mapping'][$ip_port])) {
                        $tmp_group[$server['id_mysql_server']][] = $information['mapping'][$ip_port];
                    }
                    else {
                        //autodetect autoadd Mysql::autodetect($server['id_mysql_server'], $ip_port);
                    }
                }

                //self::$galera[$id_mysql_server] 
            }

            if (! empty($tmp_group[$server['id_mysql_server']])) {
                $tmp_group[$server['id_mysql_server']] = array_unique($tmp_group[$server['id_mysql_server']]);
                self::$galera[$server['id_mysql_server']] = $tmp_group[$server['id_mysql_server']];
                //Debug::debug(self::$galera);
            }
        }
        return $tmp_group;
    }

    /*
        prend en paramètre wsrep_cluster_address ou wsrep_incoming_addresses
        a bouger dans App\Lib\Galera
    */

    static function getIdMysqlServerFromGalera($cluster_address)
    {
        $addresses = str_replace('gcomm://', '', $cluster_address);
        $addressList = explode(',', $addresses);

        // Initialiser le tableau de résultat
        $resultArray = array();

        // Parcourir chaque élément du tableau des adresses
        foreach ($addressList as $key => $value) {
            // Séparer l'adresse IP du port
            $parts = explode(':', $value);
            $ip = $parts[0];
            $port = isset($parts[1]) ? $parts[1] : 3306; // Définir le port à 3306 si non spécifié ou 0

            // Remplacer le port par 3306 si c'est 0
            if ($port == 0) {
                $port = 3306;
            }

            // Ajouter au tableau de résultat
            $resultArray[$key + 1] = "$ip:$port";
        }
        //Debug::debug($resultArray);

        return $resultArray;
    }



    public function generateGroupMaxScale($information)
    {
        $tmp_group = array();
        //Debug::debug($information['servers'][65]);
        
        foreach($information['servers'] as $id_mysql_server => $server)
        {
            
            if ( empty($server['is_maxscale']) ) {
                continue;
            }

            //Debug::debug("#####################################");


            $tmp_group[$id_mysql_server][] = $id_mysql_server;
            $maxcale_ip_port = trim($server['ip_real']).":".trim($server['port_real']);

            Debug::debug($maxcale_ip_port, "IP:PORT");

            $maxscale = MaxScale::rewriteJson($server);


            if (count($maxscale) != 0)
            {
                

                $maxscale = self::resolveMaxScaleConnection($maxscale,  $maxcale_ip_port);

                if (empty($maxscale[$maxcale_ip_port]['servers']))
                {
                    //Debug::debug(maxScale::removeArraysDeeperThan($maxscale,3), "MAXSCALE");
                    //Debug::debug(maxScale::removeArraysDeeperThan($server,2), "SERVER");
                    //Debug::debug($maxscale, "maxscale");
                    Debug::debug($maxcale_ip_port, "IP REAL");

                    throw new Exception(
                    "[PMACONTROL-4001] No 'servers' section found for listener '$maxcale_ip_port' in the MaxScale response. "
                    . "This usually indicates an incomplete service configuration or an inconsistency in the data returned by the REST API."
                    );
                }

                foreach($maxscale[$maxcale_ip_port]['servers'] as $server => $srv) {

                    if (!empty($information['mapping'][$server]))
                    {
                        $tmp_group[$id_mysql_server][] = $information['mapping'][$server];
                    }
                    else{
                        // insert to alias

                        $elems = explode(":", $server);

                        $db = Sgbd::sql(DB_DEFAULT);

                        $sql = " INSERT INTO alias_dns (id_mysql_server, dns, port) VALUES (NULL, '".$elems[0]."', ".$elems[1].")";
                        $db->sql_query($sql);
                    }
                }
            }
        }

        return $tmp_group;
    }


    public function test2($param)
    {
        Debug::parseDebug($param);

        self::getIdMysqlServerFromGalera("gcomm://PIXID-MDB-MASTER1,PIXID-MDB-MASTER2,PIXID-MDB-MASTER3,PIXID-MDB-MASTER4");
    }


    public function run($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_dot3_information = $this->generateInformation($param);
        
        //$id_dot3_information = 2356819;
        $info = self::getInformation($id_dot3_information);

        //TODO : add if date > now => return true to not was time to regenerate dot for nothing

        $groups = $this->getGroup(array($id_dot3_information));

        //Debug::debug($groups, "List of group ");

        foreach($groups as $group)
        {
            //echo "##########################################################\n";

            if (! in_array(149, $group))
            {
                //continue;
            }


            self::$rank_same = array();
            self::$build_galera = array();
            self::$build_ms = array();
            self::$build_server = array();

            //Debug::debug($group, "GROUP");
            
            //Debug::debug(self::$build_galera);

            $this->buildServer(array($id_dot3_information, $group));

            // il faut builder les serveur avant Galera => Galera va surcharger le noeud en cas de desync / donor / non-primary
            $this->buildGaleraCluster(array($id_dot3_information, $group));

            // Edge informative pour SST (joiner offline vu dans incoming_addresses d'un noeud actif)
            // constraint=false pour ne pas déformer le layout du cluster.
            $this->buildGaleraSstHintLink(array($id_dot3_information, $group));

            $this->buildLink(array($id_dot3_information, $group));
            $this->buildLinkVIP(array($id_dot3_information, $group));
            //Debug::debug($group, "GROUP");

            $this->buildLinkBetweenProxySQL(array($id_dot3_information, $group));

            //$this->linkProxySQLAdmin(array($id_dot3_information, $group));
            $this->linkHostGroup(array($id_dot3_information, $group));

            $this->linkMaxScale(array($id_dot3_information, $group));

            $dot = $this->writeDot();

            //Debug::debug($dot, "DOT");

            $reference = md5(json_encode($group));
            $file_name = Graphviz::generateDot($reference, $dot);

            $this->saveGraph($id_dot3_information, $file_name, $dot, $group);
        }
    }

    public function saveGraph($id_dot3_information, $file_name, $dot, $group)
    {
        $db = Sgbd::sql(DB_DEFAULT, "RUN");

        $md5 = md5($dot);
        $dot3_graph = array();

        $sql = "SET AUTOCOMMIT=0;";
        $res = $db->sql_query($sql);
        $sql = "BEGIN";
        $res = $db->sql_query($sql);

        $this->logger->notice("MD5 (DOT) : $md5");

        $sql = "SELECT id FROM dot3_graph WHERE md5 = '".$md5."'";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $id_dot3_graph = $ob->id;
            
            $dot3_graph['dot3_graph']['id'] = $id_dot3_graph;
            $this->logger->notice("ID : ".$id_dot3_graph);
        }

        if (empty($id_dot3_graph))
        {
            $images = getimagesize(str_replace(".svg",".png",$file_name));

            $width= $images[0];
            $height= $images[1];

            $dot3_graph['dot3_graph']['filename'] = $file_name;
            $dot3_graph['dot3_graph']['dot'] = $dot;
            $dot3_graph['dot3_graph']['svg'] = file_get_contents($file_name);
            $dot3_graph['dot3_graph']['md5'] = $md5;
            $dot3_graph['dot3_graph']['width'] = $width;
            $dot3_graph['dot3_graph']['height'] = $height;
            $id_dot3_graph = $db->sql_save($dot3_graph);
        }

        $dot3_cluster = array();
        $sql = "SELECT id FROM dot3_cluster WHERE id_dot3_graph = ".$id_dot3_graph." AND id_dot3_information = ".$id_dot3_information."";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $id_dot3_graph = $ob->id;
            //Debug::debug($id_dot3_graph, "id_dot3_graph");
            $dot3_cluster['dot3_cluster']['id'] = $id_dot3_graph;
        }

        $dot3_cluster['dot3_cluster']['id_dot3_graph'] = $id_dot3_graph;
        $dot3_cluster['dot3_cluster']['id_dot3_information'] = $id_dot3_information;

        $id_dot3_cluster = $db->sql_save($dot3_cluster);

        foreach($group as $id_mysql_server)
        {
            $dot3_cluster__mysql_server = array();
            $dot3_cluster__mysql_server['dot3_cluster__mysql_server']['id_mysql_server'] = $id_mysql_server;
            $dot3_cluster__mysql_server['dot3_cluster__mysql_server']['id_dot3_cluster'] = $id_dot3_cluster;
            
            $db->sql_save($dot3_cluster__mysql_server);
        }

        $sql = "COMMIT";
       //$sql ="ROLLBACK";
        $res = $db->sql_query($sql);
    }

    public function writeDot()
    {
        $dot = '';
        $dot .= Graphviz::generateStart();


        //Debug::debug(self::$build_server, "BUILD_SERVER");

        foreach(self::$build_server as $server) {

            //Debug::debug($server);
            $dot .= Graphviz::generateServer($server);
        }

        $dot .= Graphviz::generateGalera(self::$build_galera);
    
        foreach(self::$build_ms as $edge) {
            $dot .= Graphviz::generateEdge($edge);
        }  

        foreach(self::$rank_same as $same)
        {
            $dot .= $same;
        }

        //$dot .= Graphviz::buildApp();
        $dot .= Graphviz::generateEnd();

        return $dot;

    }

    private function array_merge_group($array)
    {
        $all_values  = $this->array_values_recursive($array);
        $group_merge = [];
        foreach ($all_values as $value) {
            $tmp = [];
            foreach ($array as $key => $sub_group) {
                if (in_array($value, $sub_group)) {
                    $tmp = array_merge($sub_group, $tmp);
                    unset($array[$key]);
                }
            }
            $array[] = array_unique($tmp);
        }
        //@TODO : Improvement because we parse all_value and we delete all array from orgin no need to continue;
        return $array;
    }

    private function array_values_recursive($ary)
    {
        $lst = array();
        foreach (array_keys($ary) as $k) {
            $v = $ary[$k];
            if (is_scalar($v)) {
                $lst[] = $v;
            } elseif (is_array($v)) {
                $lst = array_merge($lst, $this->array_values_recursive($v));
            }
        }
        return $lst;
    }

    public function getGroup($param)
    {
        //Debug::parseDebug($param);

        $id_dot3_information = $param[0];
        $dot3_information = self::getInformation($id_dot3_information);

        $galera = $this->generateGroupGalera($dot3_information['information']);
        //Debug::debug($galera, "GALERA");

        $master_slave = $this->generateGroupMasterSlave($dot3_information['information']);
        $proxysql = $this->generateGroupProxySQL($dot3_information['information']);

        $maxscale = $this->generateGroupMaxScale($dot3_information['information']);

        $vip = $this->generateGroupVip($dot3_information['information']);
        

        $group = $this->array_merge_group(array_merge($galera, $master_slave, $proxysql, $maxscale, $vip));

        Debug::debug($group, "GROUP");
        //die();
        return $group;
    }

    public function buildLink($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];

        $dot3_information = self::getInformation($id_dot3_information);

        foreach($group as $id_mysql_server)
        {
            if (! empty($dot3_information['information']['servers'][$id_mysql_server]['@slave']))
            {

                //Debug::debug($dot3_information['information']['servers'][$id_mysql_server]['@slave'], "@@SLAVE");
                foreach($dot3_information['information']['servers'][$id_mysql_server]['@slave'] as $key => $slave)
                {
                    //Debug::debug($slave, "SLAVE");
                    //Debug::debug($key, "KEY");

                  
                    $host = $slave['master_host'].':'.$slave['master_port'];
                    $id_master = self::findIdMysqlServer($host, $id_dot3_information);

                    $tmp = array();


                    //ALL OK
                    if (strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'yes' 
                    && $slave['seconds_behind_master'] == "0")
                    {
                        if (!empty($slave['connection_name'] ))
                        {
                            $tmp['tooltip'] = $slave['connection_name'];
                        }
                        else
                        {
                            $tmp['tooltip'] = "[default]";
                        }


                        $tmp = self::$config['REPLICATION_OK'];
                        
                        

                        if (!empty($slave['master_ssl_allowed']) && $slave['master_ssl_allowed'] === "Yes")
                        {
                            $tmp['options']['label'] = "SSL 🔒";
                            $tmp['tooltip'] = "SSL 🔒";
                        }


                    }
                    //replication STOPED
                    elseif(strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'yes' 
                    && $slave['seconds_behind_master'] == "NULL")
                    {
                        if (!empty($slave['master_ssl_allowed']) && $slave['master_ssl_allowed'] === "Yes")
                        {
                            $tmp['options']['label'] = "SSL 🔒";
                        }
                        

                        $tmp = self::$config['REPLICATION_STOPPED'];
                        $tmp['tooltip'] = "STOPPED";
                    }
                    //replication with DELAY
                    elseif(strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'yes' 
                    && $slave['seconds_behind_master'] != "0")
                    {
                        $tmp = self::$config['REPLICATION_DELAY'];
                        $tmp['tooltip'] = "DELAY : ".$slave['seconds_behind_master'].' '.__("seconds");
                        $tmp['options']['label'] = $slave['seconds_behind_master'].' '.__("seconds");
                    }
                    //replication ERROR SQL
                    elseif(strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'no' )
                    {
                        $tmp = self::$config['REPLICATION_ERROR_SQL'];
                        $tmp['tooltip'] = "ERROR : ".$slave['last_sql_errno'].":".$slave['last_sql_error'];
                    }
                    elseif(strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'no' )
                    {
                        $tmp = self::$config['REPLICATION_ERROR_IO'];
                        $tmp['tooltip'] = "ERROR : ".$slave['last_io_errno'].":".$slave['last_io_error'];
                        $tmp['options']['style'] = $tmp['style'];
                    }
                    elseif(strtolower($slave['slave_io_running']) == 'no' 
                    && strtolower($slave['slave_sql_running']) == 'no' )
                    {
                        $tmp = self::$config['REPLICATION_STOPPED'];
                        $tmp['tooltip'] = __("Replication stopped");
                        $tmp['options']['style'] = $tmp['style'];
                    }
                    elseif(strtolower($slave['slave_io_running']) == 'no' 
                    && strtolower($slave['slave_sql_running']) == 'no' )
                    {
                        $tmp = self::$config['REPLICATION_ERROR_BOTH'];
                        $tmp['tooltip'] = "ERROR : ".$slave['last_sql_errno'].":".$slave['last_sql_error']
                        ." - ERROR : ".$slave['last_io_errno'].":".$slave['last_io_error'];
                        $tmp['options']['style'] = $tmp['style'];
                    }
                    elseif(strtolower($slave['slave_io_running']) == 'connecting')
                    {
                        $tmp = self::$config['REPLICATION_ERROR_CONNECT'];
                        $tmp['tooltip'] = "ERROR : "
                        ." - ERROR : ".$slave['last_io_errno'].":".self::escapeTooltip($slave['last_io_error']);
                        $tmp['options']['style'] = $tmp['style'];
                    }
                    else{
                        $tmp = self::$config['REPLICATION_BUG'];
                        $tmp['tooltip'] = __("Case unknow !");
                    }

                    //override ALL is slave is offline
                    if (empty($dot3_information['information']['servers'][$id_mysql_server]['mysql_available']))
                    {
                        $tmp = self::$config['REPLICATION_BLACKOUT'];
                        $tmp['options']['style'] = $tmp['style'];
                        $tmp['tooltip'] = __("Server is offline");
                    }
                    
                    $tmp['options']['penwidth'] = "3";
                    if (!empty($slave['using_gtid'])) {
                        if (strtolower($slave['using_gtid']) != "no") {
                            $tmp['color'] = $tmp['color'].":#FFFFFF:".$tmp['color'];
                            $tmp['options']['penwidth'] = "2";
                            $tmp['options']['style'] = $tmp['style'];
                        }
                    }

                    $tmp['options']['arrowsize'] = "1.5";

                    $connection_name = '';
                    if (!empty($slave['connection_name'] ))
                    {
                        $connection_name = $slave['connection_name'];
                    }

                    $tmp['options']['edgeURL'] = LINK."slave/show/".$id_mysql_server."/".$connection_name."/";

                    $tmp['arrow'] = $id_master.":".self::TARGET." -> ".$id_mysql_server.":".self::TARGET."";

                    self::$build_ms[] = $tmp;
                }
                
            }
        }


        //Debug::debug(self::$build_ms , "LINK MASTER SLAVE");

    }

    public function buildLinkVIP($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];

        $dot3_information = self::getInformation($id_dot3_information);

        foreach ($group as $id_mysql_server)
        {
            if (empty($dot3_information['information']['servers'][$id_mysql_server])) {
                continue;
            }

            $server = $dot3_information['information']['servers'][$id_mysql_server];
            $vip_destinations = $this->getVipRenderDestinations($server);

            $vip_links = array(
                'active' => array(
                    'id_destination' => $vip_destinations['active_id'],
                    'theme' => 'VIP_LINK_ACTIVE',
                    'tooltip' => 'VIP active destination',
                    'default_style' => 'solid',
                    'source_port' => self::VIP_ACTIVE_PORT,
                ),
                'previous' => array(
                    'id_destination' => $vip_destinations['previous_id'],
                    'theme' => 'VIP_LINK_PREVIOUS',
                    'tooltip' => 'VIP previous destination',
                    'default_style' => 'dashed',
                    'source_port' => self::VIP_PREVIOUS_PORT,
                ),
            );

            foreach ($vip_links as $field => $settings)
            {
                $id_destination = (int)($settings['id_destination'] ?? 0);
                if ($id_destination <= 0) {
                    continue;
                }

                // destination_previous_id must be ignored when equal to 0
                if ($field === 'previous' && $id_destination === 0) {
                    continue;
                }

                if (empty($dot3_information['information']['servers'][$id_destination])) {
                    continue;
                }

                $destination_server = $dot3_information['information']['servers'][$id_destination];
                $destination_name = trim((string)($destination_server['display_name'] ?? ''));
                if ($destination_name === '') {
                    $destination_name = '#'.$id_destination;
                }

                $destination_port = $this->getServerPort($destination_server);
                $destination_label = $destination_name;
                if ($destination_port !== '') {
                    $destination_label .= ':'.$destination_port;
                }

                $theme = $settings['theme'];
                $tmp = self::$config[$theme] ?? array(
                    'color' => '#008000',
                    'style' => $settings['default_style'],
                    'options' => array(),
                );

                if (empty($tmp['options']) || !is_array($tmp['options'])) {
                    $tmp['options'] = array();
                }

                $tmp['tooltip'] = $settings['tooltip'].' : '.$destination_label;
                $tmp['options']['style'] = $tmp['style'] ?? $settings['default_style'];
                $tmp['options']['arrowsize'] = '1.5';
                $tmp['arrow'] = $id_mysql_server . ':' . $settings['source_port'] . ' -> ' . $id_destination . ':' . self::TARGET;

                self::$build_ms[] = $tmp;
            }
        }
    }

    /**
     * Ajoute une flèche SST "hint" sans impacter la mise en page Graphviz.
     *
     * Règle métier demandée:
     * - Si un noeud Galera actif A voit B dans wsrep_incoming_addresses
     * - et que B existe bien côté inventaire, wsrep_on=ON mais mysql_available=0
     * => on affiche une flèche supplémentaire A -> B (donor -> joiner)
     *
     * NB: l'edge est purement visuelle (constraint=false + weight=0).
     */
    public function buildGaleraSstHintLink($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);

        if (empty($dot3_information['information']['servers']) || empty($dot3_information['information']['mapping'])) {
            return;
        }

        $servers = $dot3_information['information']['servers'];
        $mapping = $dot3_information['information']['mapping'];
        $groupLookup = array_fill_keys(array_map('intval', $group), true);

        // Candidats donor par joiner (on choisira ensuite UN seul donor par joiner)
        $candidateByJoiner = array();

        foreach ($group as $viewerId) {
            $viewerId = (int)$viewerId;
            if (empty($servers[$viewerId])) {
                continue;
            }

            $viewer = $servers[$viewerId];
            if (empty($viewer['wsrep_on']) || strtolower((string)$viewer['wsrep_on']) !== 'on') {
                continue;
            }

            if (empty($viewer['mysql_available']) || (string)$viewer['mysql_available'] !== '1') {
                continue;
            }

            if (empty($viewer['wsrep_incoming_addresses'])) {
                continue;
            }

            $viewerClusterName = trim((string)($viewer['wsrep_cluster_name'] ?? ''));
            $viewerSegment = $this->getGaleraSegmentFromNode($viewer);
            $incoming = self::getIdMysqlServerFromGalera((string)$viewer['wsrep_incoming_addresses']);

            foreach ($incoming as $ipPort) {
                if (empty($mapping[$ipPort])) {
                    continue;
                }

                $joinerId = (int)$mapping[$ipPort];
                if ($joinerId === $viewerId) {
                    continue;
                }

                if (empty($groupLookup[$joinerId]) || empty($servers[$joinerId])) {
                    continue;
                }

                $joiner = $servers[$joinerId];
                if (empty($joiner['wsrep_on']) || strtolower((string)$joiner['wsrep_on']) !== 'on') {
                    continue;
                }

                // Joiner attendu: noeud Galera offline
                if (!isset($joiner['mysql_available']) || (string)$joiner['mysql_available'] !== '0') {
                    continue;
                }

                $joinerClusterName = trim((string)($joiner['wsrep_cluster_name'] ?? ''));
                if ($viewerClusterName !== '' && $joinerClusterName !== ''
                    && strcasecmp($viewerClusterName, $joinerClusterName) !== 0) {
                    continue;
                }

                $joinerSegment = $this->getGaleraSegmentFromNode($joiner);

                // Règle métier demandée : le donor doit être dans le même segment que le joiner
                if ($viewerSegment !== $joinerSegment) {
                    continue;
                }

                $score = $this->scoreSstDonorCandidate($viewer, $joinerSegment);

                $candidateByJoiner[$joinerId][] = array(
                    'donor_id' => $viewerId,
                    'joiner_id' => $joinerId,
                    'joiner_cluster_name' => $joinerClusterName,
                    'score' => $score,
                );
            }
        }

        foreach ($candidateByJoiner as $joinerId => $candidates) {
            if (empty($candidates)) {
                continue;
            }

            usort($candidates, function ($a, $b) {
                if ($a['score'] === $b['score']) {
                    return $a['donor_id'] <=> $b['donor_id'];
                }
                return $b['score'] <=> $a['score'];
            });

            $winner = $candidates[0];
            $donorId = (int)$winner['donor_id'];
            $joinerId = (int)$winner['joiner_id'];

            // Le noeud offline est considéré comme receveur SST (joiner)
            if (!empty(self::$build_server[$joinerId])) {
                if (!empty(self::$config['NODE_WAITING'])) {
                    self::setThemeToServer('NODE_WAITING', $joinerId);
                }

                self::$build_server[$joinerId]['galera_status_override'] = 'Joiner';
                self::$build_server[$joinerId]['wsrep_local_state_comment'] = 'Joiner';
                self::$build_server[$joinerId]['is_sst_receiver'] = '1';

                // Compléter les valeurs auto_increment manquantes du joiner
                // en se basant sur les autres noeuds Galera du même cluster.
                [$suggestedOffset, $suggestedIncrement] = $this->guessGaleraAutoIncrement(
                    $servers,
                    $group,
                    $joinerId,
                    $winner['joiner_cluster_name']
                );

                // Règle demandée: en mode joiner, on recalcule systématiquement
                // les paramètres auto_increment pour rester cohérent avec le cluster.
                if ($suggestedOffset > 0) {
                    self::$build_server[$joinerId]['auto_increment_offset'] = (string) $suggestedOffset;
                }

                if ($suggestedIncrement > 0) {
                    self::$build_server[$joinerId]['auto_increment_increment'] = (string) $suggestedIncrement;
                }
            }

            $tmp = self::$config['REPLICATION_SST'] ?? array(
                'color' => '#e3ea12',
                'style' => 'dashed',
                'options' => array(),
            );

            if (empty($tmp['options']) || !is_array($tmp['options'])) {
                $tmp['options'] = array();
            }

            $sstLabel = $this->buildSstEdgeLabel($servers[$donorId] ?? array(), $servers[$joinerId] ?? array());

            $tmp['arrow'] = $donorId . ':' . self::TARGET . ' -> ' . $joinerId . ':' . self::TARGET;
            $tmp['tooltip'] = 'SST probable : donor -> joiner';
            if ($sstLabel !== 'SST') {
                $tmp['tooltip'] .= ' (' . $sstLabel . ')';
            }
            //$tmp['options']['constraint'] = 'false';
            //$tmp['options']['weight'] = '0';
            //$tmp['options']['penwidth'] = '2';
            $tmp['options']['arrowsize'] = '1.5';
            $tmp['options']['style'] = $tmp['style'] ?? 'dashed';
            $tmp['options']['label'] = $sstLabel;

            self::$build_ms[] = $tmp;
        }
    }

    private function getGaleraSegmentFromNode(array $node): int
    {
        $providerOptions = (string)($node['wsrep_provider_options'] ?? '');
        if ($providerOptions === '') {
            return 0;
        }

        $segment = self::extractProviderOption($providerOptions, 'gmcast.segment');
        return (int)$segment;
    }

    private function scoreSstDonorCandidate(array $donorNode, int $joinerSegment): int
    {
        $score = 0;

        $donorSegment = $this->getGaleraSegmentFromNode($donorNode);
        if ($donorSegment === $joinerSegment) {
            $score += 100;
        }

        $comment = strtolower(trim((string)($donorNode['wsrep_local_state_comment'] ?? '')));
        if (strpos($comment, 'donor') !== false) {
            $score += 40;
        }

        if ((string)($donorNode['wsrep_local_state'] ?? '') === '2') {
            $score += 20;
        }

        if (strtolower((string)($donorNode['wsrep_desync'] ?? '')) === 'on') {
            $score += 10;
        }

        return $score;
    }

    private function buildSstEdgeLabel(array $donorNode, array $joinerNode): string
    {
        $elapsedSec = $this->estimateSstElapsedSeconds($donorNode, $joinerNode);
        $progress = $this->estimateSstProgressPercent($donorNode, $joinerNode, $elapsedSec);
        $elapsedLabel = $elapsedSec !== null ? $this->formatSstElapsedLabel($elapsedSec) : null;

        if ($progress === null && $elapsedLabel === null) {
            return 'SST';
        }

        if ($progress !== null && $elapsedLabel !== null) {
            return 'SST ' . $progress . '% (' . $elapsedLabel . ')';
        }

        if ($progress !== null) {
            return 'SST ' . $progress . '%';
        }

        return 'SST (' . $elapsedLabel . ')';
    }

    private function estimateSstProgressPercent(array $donorNode, array $joinerNode, ?int $elapsedSec = null): ?int
    {
        $expectedSize = $this->getPositiveIntMetric($donorNode, 'mysql_datadir_clean_size');
        if ($expectedSize <= 0) {
            $expectedSize = $this->getPositiveIntMetric($donorNode, 'mysql_datadir_total_size');
        }

        $receivedSize = $this->getPositiveIntMetric($joinerNode, 'mysql_datadir_clean_size');
        if ($receivedSize <= 0) {
            $receivedSize = $this->getPositiveIntMetric($joinerNode, 'mysql_datadir_total_size');
        }

        if ($expectedSize <= 0 || $receivedSize < 0) {
            return null;
        }

        $pct = (int) round(($receivedSize / $expectedSize) * 100);

        if ($pct < 0) {
            $pct = 0;
        } elseif ($pct > 100) {
            $pct = 100;
        }

        // Règle métier demandée : ne pas afficher 100% si on n'a pas encore de temps SST.
        if (($elapsedSec === null || $elapsedSec <= 0) && $pct >= 100) {
            return null;
        }

        return $pct;
    }

    private function estimateSstElapsedSeconds(array $donorNode, array $joinerNode): ?int
    {
        $joinerElapsed = $this->getPositiveIntMetric($joinerNode, 'mysql_sst_elapsed_sec');
        $donorElapsed = $this->getPositiveIntMetric($donorNode, 'mysql_sst_elapsed_sec');
        $elapsedSec = max($joinerElapsed, $donorElapsed);

        if ($elapsedSec <= 0) {
            return null;
        }

        return $elapsedSec;
    }

    private function formatSstElapsedLabel(int $elapsedSec): string
    {
        if ($elapsedSec < 60) {
            return $elapsedSec . 'sec';
        }

        if ($elapsedSec < 3600) {
            $minutes = (int) floor($elapsedSec / 60);
            if ($minutes <= 0) {
                $minutes = 1;
            }
            return $minutes . 'min';
        }

        $hours = intdiv($elapsedSec, 3600);
        $minutes = intdiv($elapsedSec % 3600, 60);
        return $hours . ' h ' . $minutes . ' min';
    }

    private function getPositiveIntMetric(array $node, string $key): int
    {
        if (!isset($node[$key])) {
            return 0;
        }

        $value = $node[$key];
        if (is_array($value) && isset($value['count'])) {
            $value = $value['count'];
        }

        if (!is_numeric($value)) {
            return 0;
        }

        $intValue = (int)$value;
        if ($intValue <= 0) {
            return 0;
        }

        return $intValue;
    }

    private function guessGaleraAutoIncrement(array $servers, array $group, int $joinerId, string $clusterName): array
    {
        $increments = array();
        $usedOffsets = array();
        $onlinePeerCount = 0;

        foreach ($group as $peerId) {
            $peerId = (int)$peerId;
            if ($peerId === $joinerId || empty($servers[$peerId])) {
                continue;
            }

            $peer = $servers[$peerId];
            if (empty($peer['wsrep_on']) || strtolower((string)$peer['wsrep_on']) !== 'on') {
                continue;
            }

            // Règle métier: on se base uniquement sur les autres noeuds ONLINE
            // pour éviter de réutiliser un offset stale d'un noeud offline.
            if (empty($peer['mysql_available']) || (string)$peer['mysql_available'] !== '1') {
                continue;
            }

            $peerClusterName = trim((string)($peer['wsrep_cluster_name'] ?? ''));
            if ($clusterName !== '' && $peerClusterName !== '' && strcasecmp($clusterName, $peerClusterName) !== 0) {
                continue;
            }

            $onlinePeerCount++;

            $inc = (int)($peer['auto_increment_increment'] ?? 0);
            if ($inc > 0) {
                if (!isset($increments[$inc])) {
                    $increments[$inc] = 0;
                }
                $increments[$inc]++;
            }

            $offset = (int)($peer['auto_increment_offset'] ?? 0);
            if ($offset > 0) {
                $usedOffsets[$offset] = true;
            }
        }

        $increment = 0;
        if (!empty($increments)) {
            arsort($increments);
            $increment = (int)array_key_first($increments);
        } elseif ($onlinePeerCount > 0) {
            // fallback: cluster courant (peers + joiner)
            $increment = max(1, $onlinePeerCount + 1);
        }

        if ($increment <= 0) {
            $increment = 1;
        }

        $offset = 0;
        if ($increment > 0) {
            for ($i = 1; $i <= $increment; $i++) {
                if (empty($usedOffsets[$i])) {
                    $offset = $i;
                    break;
                }
            }

            if ($offset === 0 && !empty($usedOffsets)) {
                $knownOffsets = array_keys($usedOffsets);
                sort($knownOffsets);
                $offset = (int)$knownOffsets[0];
            }
        }

        if ($offset <= 0) {
            $offset = 1;
        }

        return array($offset, $increment);
    }

    public function buildServer($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);
        

        foreach($group as $id_mysql_server)
        {
            // to remove old server with empty data
            if (empty($dot3_information['information']['servers'][$id_mysql_server]))
            {
                continue;
            }

            $server = $dot3_information['information']['servers'][$id_mysql_server];
            $is_vip_server = $this->isVipServer($server);

            //consideringg if we don't have the version of server, this server is too old and we don't have fresh data to display.
            if (empty($server['version']) && ! $is_vip_server)
            {
                continue;
            }

            if ($is_vip_server)
            {
                $server = $this->enrichVipServerForGraph($server, $dot3_information['information']['servers']);
            }

            //Debug::debug($dot3_information['information']['servers'][$id_mysql_server],"INFO_SERVER");
            $tmp = array();

            // to remove server with organization not monitored
            if (! isset($server['mysql_available']))
            {
                continue;
            }
            
            if ($server['mysql_available'] == "1")
            {
                $tmp = self::$config['NODE_OK'];
            }
            elseif($server['mysql_available'] == "0"){
                $tmp = self::$config['NODE_ERROR'];
                $tmp['error'] = $server['mysql_error'];
            }
            else
            { // il faudrait ajouter si ok et +1 minute sans monitoring (avec le serveur le récent)
                $tmp = self::$config['NODE_BUSY'];
            }

            // ADD there color for Galera Cluster


            // Add there color for INNODB Clsuter

            $tmp = array_merge($tmp, $server);

            self::$build_server[$id_mysql_server] = $tmp;
        }
    }

    private function mergeVipDnsDataInInformation(array &$all, array $vipServerIds, $date_request): void
    {
        if (empty($vipServerIds)) {
            return;
        }

        $vip_data = Extraction2::display(array('vip::ip', 'vip::port'), $vipServerIds, $date_request);

        if (empty($vip_data) || !is_array($vip_data)) {
            return;
        }

        foreach ($vipServerIds as $id_mysql_server) {
            if (empty($vip_data[$id_mysql_server]) || !is_array($vip_data[$id_mysql_server])) {
                continue;
            }

            if (empty($all[$id_mysql_server]) || !is_array($all[$id_mysql_server])) {
                $all[$id_mysql_server] = array();
            }

            $vip_ip = trim((string)($vip_data[$id_mysql_server]['ip'] ?? ''));
            if ($vip_ip !== '') {
                $all[$id_mysql_server]['vip_dns_ip'] = $vip_ip;
            }

            $vip_port = trim((string)($vip_data[$id_mysql_server]['port'] ?? ''));
            if ($vip_port !== '') {
                $all[$id_mysql_server]['vip_dns_port'] = $vip_port;
            }
        }
    }

    private function isVipServer(array $server): bool
    {
        return !empty($server['is_vip']) && (string)$server['is_vip'] === '1';
    }

    private function enrichVipServerForGraph(array $server, array $allServers): array
    {
        $server['version'] = 'VIP';
        $server['version_comment'] = 'VIP';

        $vip_destinations = $this->getVipRenderDestinations($server);

        if (empty($server['vip_dns_ip'])) {
            $vip_ip = trim((string)($server['ip'] ?? ''));
            if ($vip_ip === '') {
                $vip_ip = trim((string)($server['ip_real'] ?? ''));
            }

            if ($vip_ip !== '') {
                $server['vip_dns_ip'] = $vip_ip;
            }
        }

        if (empty($server['vip_dns_port'])) {
            $vip_port = trim((string)($server['port'] ?? ''));
            if ($vip_port === '') {
                $vip_port = trim((string)($server['port_real'] ?? ''));
            }

            if ($vip_port !== '') {
                $server['vip_dns_port'] = $vip_port;
            }
        }

        $active = $this->buildVipDestinationLabel($allServers, $vip_destinations['active_id']);
        $previous_id = $vip_destinations['previous_id'];
        $previous = $this->buildVipDestinationLabel($allServers, $previous_id);

        $server['vip_active_label'] = $active['label'];
        $server['vip_previous_label'] = $previous['label'];

        if ($previous_id <= 0) {
            $server['vip_previous_label'] = 'N/A';
            $server['vip_last_switch'] = 'N/A';
        } else {
            $last_switch = trim((string)($server['destination_previous_date'] ?? ''));
            $server['vip_last_switch'] = $last_switch !== '' ? $last_switch : 'N/A';
        }

        return $server;
    }

    private function getVipRenderDestinations(array $server): array
    {
        $active_id = (int)($server['destination_id'] ?? 0);
        $previous_id = (int)($server['destination_previous_id'] ?? 0);

        // Cas observé en production : destination_id peut repasser à 0 alors que
        // destination_previous_id contient toujours la destination actuellement active.
        // Dans ce cas, on promeut previous -> active pour l'affichage et le point
        // de départ de la flèche.
        if ($active_id <= 0 && $previous_id > 0) {
            $active_id = $previous_id;
            $previous_id = 0;
        }

        return array(
            'active_id' => $active_id,
            'previous_id' => $previous_id,
        );
    }

    private function buildVipDestinationLabel(array $allServers, int $idDestination): array
    {
        if ($idDestination <= 0 || empty($allServers[$idDestination])) {
            return array('label' => 'N/A');
        }

        $destination_server = $allServers[$idDestination];
        $destination_name = trim((string)($destination_server['display_name'] ?? ''));

        if ($destination_name === '') {
            $destination_name = '#'.$idDestination;
        }

        $destination_port = $this->getServerPort($destination_server);
        //$label = $idDestination.' / '.$destination_name;
        $label = $destination_name;

        if ($destination_port !== '') {
            $label .= ':'.$destination_port;
        }

        return array('label' => $label);
    }

    private function getServerPort(array $server): string
    {
        $port = trim((string)($server['port_real'] ?? ''));
        if ($port !== '') {
            return $port;
        }

        return trim((string)($server['port'] ?? ''));
    }

    /*
     *  CALL after BuildServer
     */

    public function linkProxySQLAdmin($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);

        foreach($group as $id_mysql_server){
            //Debug::debug($id_mysql_server,"id_mysql_server");
            $server = $dot3_information['information']['servers'][$id_mysql_server];
            if (!empty($server['is_proxysql'])){
                foreach($dot3_information['information']['proxysql'] as $id_proxysql => $proxysql)
                {
                    //Debug::debug($proxysql['id_mysql_server']." == ". $id_mysql_server, "TEST OK ?");
                    if ($proxysql['id_mysql_server'] == $id_mysql_server){
                        self::$build_server[$id_mysql_server]['proxysql'] = $proxysql;
                        //Debug::debug(self::$build_server);
                    }
                }
            }
        }
    }

    public function linkHostGroup($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);

        foreach(self::$build_server as $id_mysql_server => $server)
        {
            if (empty($server['is_proxysql']))
            {
                continue;
            }

            if (empty($server['is_proxysql']) && $server['is_proxysql'] != "1") {
                continue;
            }

            $i = 0;

            if (empty($server['mysql_servers']))
            {
                //throw new Exception("Impossible to find server");
                Debug::debug($server, "PROBLEM PROCYSQL");
                Debug::debug($id_mysql_server, "PROBLEM PROCYSQL");
            }

            //Debug::debug($server,"PROXYSQL -------------");


            foreach($server['mysql_servers'] as $hostgroup) {
                $i++;

                $host = $hostgroup['hostname'].':'.$hostgroup['port'];

                $tmp = array();
                
                $tmp = self::$config['PROXYSQL_'.$hostgroup['status']];
                $tmp['tooltip'] = "STATUS : ".$hostgroup['status'];

                if (! empty($server['proxy_connect_error'])){
                    foreach($server['proxy_connect_error'] as $hostname => $error){
                        $extra = '';
                        if ($hostname == $host)
                        {
                            
                            if ($hostgroup['status'] == "ONLINE") {
                                $tmp['tooltip'] = $tmp['tooltip'].' -  ERROR CONFIG DETECTED : '.$error;
                            }
                            else {
                                $tmp['tooltip'] = $tmp['tooltip'].' -  ERROR : '.$error;
                            }
                            
                            //
                            break;
                        }
                    }
                }
                
                $id_mysql_server_target = self::findIdMysqlServer($host, $id_dot3_information);
                //headlabel="*", taillabel="1"
                
                $port = crc32($hostgroup['hostgroup_id'].':'.$host);
                
                //$tmp['arrow'] = '"'.$id_mysql_server.':hg'.$i.'" -> "'.
                $id_mysql_server_target.':'.self::TARGET.'"';
                $tmp['arrow'] = $id_mysql_server.':'.$port.' -> '.$id_mysql_server_target.':'.self::TARGET.'';
                $tmp['options']['dir'] = 'both';
                $tmp['options']['style'] = $tmp['style'];
                $tmp['options']['arrowtail']= 'crow';
                $tmp['options']['arrowhead']= 'none';

                if ($hostgroup['status'] != "ONLINE")
                {
                    $tmp['options']['label'] = ucfirst(strtolower($hostgroup['status']));
                    //$tmp['options']['tooltip'] = ucfirst(strtolower($hostgroup['status']));
                    $tmp['options']['labeltooltip'] = __('The server was removed from the host group (id:'.$hostgroup['hostgroup_id'].') because he is offline or the delay of replication is more than 10 seconds as specified on mysql_servers.max_replication_lag in ProxYSQL');
                    
                    //$tmp['options']['headlabel'] = "sfhg";
                    //$tmp['options']['taillabel'] = "sfhg";

                    if (in_array($hostgroup['hostgroup_id'], array(1)))
                    {
                        continue;
                    }
                    
                }

                $writer = $server['mysql_replication_hostgroups'][0]['writer_hostgroup'] ?? null;
                $reader = $server['mysql_replication_hostgroups'][0]['reader_hostgroup'] ?? null;


                if (in_array($hostgroup['hostgroup_id'], [$reader]))
                {
                    $tmp['options']['style'] = "filled";
                    $tmp['options']['color'] = "#32CD32";
                }


                if (in_array($hostgroup['hostgroup_id'], array(1,2,100)))
                {
                    if (in_array($hostgroup['hostgroup_id'], array(2)))
                    {
                        $tmp['options']['style'] = "filled";
                        $tmp['options']['color'] = "#32CD32";
                    }


                    if (in_array($hostgroup['hostgroup_id'], array(100)))
                    {
                        $tmp['options']['style'] = "filled";
                        $tmp['options']['color'] = "#17a2b8";
                    }


                    
                }
                self::$build_ms[] = $tmp;
            }
        }

        //Debug::debug(self::$build_ms);
    }


    public function linkMaxScale($param)
    {
        Debug::parseDebug($param);

        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);

        foreach(self::$build_server as $id_mysql_server => $server)
        {
            if (empty($server['is_maxscale']))
            {
                continue;
            }

            if (empty($server['is_maxscale']) && $server['is_maxscale'] != "1") {
                continue;
            }

            $i = 0;
            $maxscale = MaxScale::rewriteJson($server);

            $listener_maxscale = $server['ip_real'].':'.$server['port_real'];

            $ret_max = Dot3::resolveMaxScaleConnection($maxscale,  $listener_maxscale);

            // faire le match

            if (empty($ret_max[$listener_maxscale]))
            {
                Debug::debug($ret_max, "MAXSCALE");
                $this->logger->warning($server['display_name']. "[$listener_maxscale] Impossible to find informations from Maxscale Admin (empty)");

                continue;
            }

            foreach($ret_max[$listener_maxscale]['servers'] as $elem)
            {
                //Debug::debug($elem['parameters'], "SERVER");

                $id_mysql_server_target = self::findIdMysqlServer($elem['parameters']['address'].":".$elem['parameters']['port'], $id_dot3_information);

                $tmp = [];



                if (in_array("Master",explode(", ",$elem['state']) )){
                    $tmp['options']['style'] = "filled";
                }
                else{
                    $tmp['options']['style'] = "dashed";
                    $tmp['options']['style'] = "filled";
                }

                $port = crc32($server['ip_real'].':'.$server['port_real'].':'.$elem['parameters']['address'].":".$elem['parameters']['port']);
                $tmp['arrow'] = $id_mysql_server.':'.$port.' -> '.$id_mysql_server_target.':'.self::TARGET.'';
                
                if ($server['mysql_available'] == "1")
                {
                    if (in_array("Master",explode(", ",$elem['state']) )){
                        $tmp['options']['color'] = "#008000";
                    }
                    else{
                        $tmp['options']['color'] = "#00B33C";
                    }

                    if (in_array("Donor/Desynced",explode(", ",$elem['state']) )){
                        $tmp['options']['color'] = "#337ab7";
                    }
                    

                }
                else{
                    $tmp['options']['color'] = "#cc5500";
                }

                self::$build_ms[] = $tmp;
            }
            


        }
    }

    public function loadConfigColor()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `dot3_legend` order by `order`;";
        $res = $db->sql_query($sql);

        //$to_test = ['font','color','background'];

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            unset($arr['id']);
            /*
            foreach ($arr as $key => $value) {
                // Si la clé est dans le tableau $to_test
                if (in_array($key, $to_test)) {

                    $value = strtolower($value);
                    // Si la valeur existe dans la colorMap, on remplace
                    if (isset(self::$colorMap[$value])) {
                        $arr[$key] = self::$colorMap[$value];
                    }
                }
            }*/

            
            self::$config[$arr['const']] = $arr;
        }

        //Debug::$debug = true;
        //Debug::debug(self::$config, "DOT3_LEGEND");
        //die('wdfgdf');
    }

    private static function findIdMysqlServer($host, $id_dot3_information)
    {        
        $dot_information = self::getInformation($id_dot3_information);

        if (empty($dot_information['information']['mapping']))
        {
            throw new Exception('Impossible to acess to item Mapping');
        }

        if (! empty($dot_information['information']['mapping'][$host])) {
            return  $dot_information['information']['mapping'][$host];
        }
        else {
            //Debug::debug($dot_information, "mapping");
            // create box => autodetect
            echo "This master was not found : ".$host."\n";
            //die();
        }
    }

    private static function getInformation($id_dot3_information = '')
    {
        //Debug::debug($id_dot3_information, "id_dot3_information");
        
        if (! empty(self::$information[$id_dot3_information])){
            return self::$information[$id_dot3_information];
        }
        
        $db = Sgbd::sql(DB_DEFAULT);

        if (empty($id_dot3_information)) {
            $id_dot3_information = "SELECT max(`id`) FROM `dot3_information`";
        }

        $sql = "SELECT * FROM `dot3_information` where `id` in (".$id_dot3_information.");";
        
        Debug::sql($sql);
        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $arr['information'] = json_decode($arr['information'], true);
            self::$information[$arr['id']] = $arr;
            return $arr; 
        }

        return array();
    }


    /*
    TO MOVE
    */
    static function replaceKey(&$array, $oldKey, $newKey) {
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::replaceKey($value, $oldKey, $newKey);  // Appel récursif pour les sous-tableaux
            } else {
                if (isset($array[$oldKey])) {
                    $array[$newKey] = $array[$oldKey];  // Assignation de la valeur à la nouvelle clé
                    unset($array[$oldKey]);             // Suppression de l'ancienne clé
                }
            }
        }
    }

    /*
    TO MOVE
    */



    public function legend()
    {
        $sql = "SELECT * FROM `dot3_legend` WHERE `type`= 'REPLICATION' order by `order`;";
        $db  = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query($sql);

        $edges = array();
        while ($arr   = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $edges[] = $arr;
        }

        $legend = 'digraph {
	    rankdir=LR
	    graph [fontname = "arial"];
	    node [fontname = "arial"];
	    edge [fontname = "arial"];
	    node [shape=plaintext fontsize=8];
	    subgraph cluster_01 {
        bgcolor="#ffffff"
	    label = "Replication : Legend";
	    key [label=<<table border="0" cellpadding="2" cellspacing="0" cellborder="0">';

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= '<tr><td align="right" port="i' . $i . '">' . $edge['name'] . '&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>' . "\n";
            $i++;
        }

        // GTID
        $legend .= '<tr><td align="right" port="j' . $i . '"> ' . " " . ' </td></tr>' . "\n";
        $legend .= '<tr><td align="right" port="i' . $i . '"> ' . "GTID" . ' </td></tr>' . "\n";
        $i++;
        $legend .= '<tr><td align="right" port="i' . $i . '"> ' . "Standard" . ' </td></tr>' . "\n";
        $legend .= '</table>>]
		    key2 [label=<<table border="0" cellpadding="2" cellspacing="0" cellborder="0">' . "\n";

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= '<tr><td port="i' . $i . '">&nbsp;</td></tr>' . "\n";
            $i++;
        }

        $legend .= '<tr><td port="j' . $i . '">&nbsp;</td></tr>' . "\n";
        $legend .= '<tr><td port="i' . $i . '">&nbsp;</td></tr>' . "\n";
        $i++;
        $legend .= '<tr><td port="i' . $i . '">&nbsp;</td></tr>' . "\n";
        $legend .= '</table>>]' . "\n";

        $i = 1;
        foreach ($edges as $edge) {
            $legend .= 'key:i' . $i . ':e -> key2:i' . $i . ':w [color="' . $edge['color'] . '" arrowsize="1.5" style=' . $edge['style'] . ',penwidth="2"]' . "\n";
            $i++;
        }

        $edge['color'] = "grey";
        $edge['style'] = "filed";

        $legend .= 'key:i' . $i . ':e -> key2:i' . $i . ':w [color="' . $edge['color'] . ':#ffffff:' . $edge['color'] . '" arrowsize="1.5" style=' . $edge['style'] . ',penwidth="2"]' . "\n";
        $i++;
        $legend .= 'key:i' . $i . ':e -> key2:i' . $i . ':w [color="' . $edge['color'] . '" arrowsize="1.5" style=' . $edge['style'] . ',penwidth="2"]' . "\n";
        $i++;


        $legend .= '
  }
}';


        $file_name = Graphviz::generateDot("legend", $legend);
        $data['legend'] = file_get_contents($file_name);

        $this->set('data', $data);

        //https://dreampuf.github.io/GraphvizOnline/
    }

    public function download($param)
    {
        $info = self::getInformation();
        $data = json_encode($info);

        $date = explode(' ',$info['date_inserted'])[0];

        $filename = '/tmp/'.$date.'-'.$info['md5'].'.json';
        file_put_contents($filename, $data);

        
        if (file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            exit;
        }

    }



    /*****
     * case for Last_IO_Error, double quote not supported by graphviz, and connect escape to put in tooltip
     * error connecting to master 'replic...... '127.0.0.1' (111 "Connection refused")
     * 
     */
    static public function escapeTooltip($string)
    {
        $string = str_replace('"',"‘", $string);
        return $string;
    }


    public static function purgeAll($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql ="SET FOREIGN_KEY_CHECKS=0;";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE dot3_cluster__mysql_server";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE dot3_information_extra";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE dot3_cluster;";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE dot3_graph";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="TRUNCATE TABLE dot3_information";
        Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="SET FOREIGN_KEY_CHECKS=1;";
        Debug::sql($sql);
        $db->sql_query($sql);
    }

    // for DEBUG ONLY

    public function show($param)
    {
        $id_dot3_information = 2356819;

        self::$id_dot3_information;

        $this->run($id_dot3_information);
    }

    public function buildGaleraCluster($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);

        //Debug::debug($dot3_information);

        //c'est dégeu mais j'ai pas d'autres idées sur le moment
        $galera = $this->array_merge_group(array_merge(self::$galera));
        
        
        //Debug::debug($group);

        $filteredClusters = array();

        foreach ($galera as $id => $cluster) {
            // Si tous les éléments du cluster sont dans le groupe (différence vide)
            if (empty(array_diff($cluster, $group))) {
                // Pour éliminer les doublons, on trie le cluster



                
                // if we have exact same clsuter with same IP / and Cluster_name we use wsrep_cluster_state_uuid if available
                $cluster_uuid =  array(); 
                $offline_or_unknown_nodes = array();
                foreach($cluster as $id_mysql_server)
                {
                    $serverInfo = $dot3_information['information']['servers'][$id_mysql_server] ?? array();
                    $isAvailable = isset($serverInfo['mysql_available']) && (string)$serverInfo['mysql_available'] === '1';
                    $server_uuid = trim((string)($serverInfo['wsrep_cluster_state_uuid'] ?? ''));

                    // Règle métier: pour un serveur offline, on ignore le UUID de cluster (souvent faux/stale)
                    // et on le rattache ensuite à un cluster basé sur le nom.
                    if ($isAvailable && $server_uuid !== '') {
                        $cluster_uuid[$server_uuid][] = $id_mysql_server;
                    } else {
                        $offline_or_unknown_nodes[] = $id_mysql_server;
                    }
                }

                switch(count($cluster_uuid))
                {
                    case 0: //for old version of Galera Cluster
                        //need imporvement to fix in case of 2 old cluster and iterate from node list and remove each node until the list will be empty
                        //now i don't think we will go new technology with docker and k8s associated with MariaDB 5.5 or 10.0 but it's can happen

                        //il faudrait extraire les id_mysql__server 
                        $sorted = $cluster;
                        sort($sorted);
                        // Crée une clé unique basée sur les membres triés
                        $key = implode('-', $sorted);

                        $filteredClusters[$key] = $sorted;
                    break;

                    default:

                        foreach($cluster_uuid as $server_uuid => $sub_cluster)  {
                            $filteredClusters[$server_uuid] = $sub_cluster;
                        }

                        // Les noeuds offline/uuid inconnu sont rattachés au premier sous-cluster online
                        // pour éviter la séparation artificielle sur UUID invalide.
                        if (!empty($offline_or_unknown_nodes)) {
                            $target_uuid = null;
                            foreach ($cluster_uuid as $server_uuid => $sub_cluster) {
                                $target_uuid = $server_uuid;
                                break;
                            }

                            if ($target_uuid !== null) {
                                if (empty($filteredClusters[$target_uuid])) {
                                    $filteredClusters[$target_uuid] = array();
                                }

                                $filteredClusters[$target_uuid] = array_values(array_unique(array_merge(
                                    $filteredClusters[$target_uuid],
                                    $offline_or_unknown_nodes
                                )));
                            }
                        }
                    break;
                }
                //Debug::debug($filteredClusters);
            }
        }

        //Debug::debug($filteredClusters);
        foreach($filteredClusters as $id_cluster => $cluster)
        {
            $server = $dot3_information['information']['servers'];

            self::$build_galera[$id_cluster]["name"] = $server[$cluster[0]]['wsrep_cluster_name'];;
            self::$build_galera[$id_cluster]["id_cluster"] = $id_cluster;

            $available = 0;
            $total_node = 0;
            $sst_method = array();
            $version = array();
            $build = array();
            $wsrep_slave_threads = array();

            foreach($cluster as $id_mysql_server)
            {
                $elems = $server[$id_mysql_server];
                $segment = self::extractProviderOption($elems['wsrep_provider_options'], "gmcast.segment" );

                self::$build_galera[$id_cluster]["node"][$segment][$id_mysql_server]['wsrep_cluster_status'] = $elems['wsrep_cluster_status'];
                self::$build_galera[$id_cluster]["node"][$segment][$id_mysql_server]['wsrep_local_state_comment'] = $elems['wsrep_local_state_comment'];
                self::$build_galera[$id_cluster]["node"][$segment][$id_mysql_server]['wsrep_desync'] = $elems['wsrep_desync'];
                self::$build_galera[$id_cluster]["node"][$segment][$id_mysql_server]['available'] = $elems['mysql_available'];
                self::$build_galera[$id_cluster]["node"][$segment][$id_mysql_server]['wsrep_sst_method'] = $elems['wsrep_sst_method'];
                self::$build_galera[$id_cluster]["node"][$segment][$id_mysql_server]['wsrep_provider_version'] = $elems['wsrep_provider_version'];
                
                if (! isset(self::$build_galera[$id_cluster]["segment"][$segment]['nb_available'])) {
                    self::$build_galera[$id_cluster]["segment"][$segment]['nb_available'] = 0;
                }

                if ($elems['mysql_available'] == "1" && !empty($elems['wsrep_desync']) && strtolower($elems['wsrep_desync']) === "on") {

                    self::setThemeToServer('NODE_DONOR_DESYNCED', $id_mysql_server);
                }

                self::$build_galera[$id_cluster]["segment"][$segment]['nb_available'] += $elems['mysql_available'];

                $wsrep_slave_threads[] = $elems['wsrep_slave_threads'];
                $sst_method[] = $elems['wsrep_sst_method'];

                $output_array = array();
                preg_match('/\d+\.(\d+)\./', $elems['wsrep_provider_version'], $output_array);
                if (!empty($output_array[1])) {
                    $version[] = $output_array[1];
                    
                    
                }
                else {
                    //throw exception and log
                }

                $output_array = array();
                preg_match('/\((\w+)\)/', $elems['wsrep_provider_version'], $output_array);
                if (!empty($output_array[1])) {
                    
                    $build[] = $output_array[1];
                    
                }
                else {
                    //throw exception and log
                }

                

                
                

                


                // test case
                if ($elems['mysql_available'] === "1"){
                    $available++;
                }
                $total_node++;
            }

            foreach(self::$build_galera[$id_cluster]["segment"] as $id_segment => $segment)
            {
                $total_node_insegment = count(self::$build_galera[$id_cluster]["node"][$id_segment]);

                //Debug::debug($total_node_insegment,"SDSHSRTHSRTHTRHSRTH");

                switch ($segment['nb_available']) {

                    case 0:
                        self::$build_galera[$id_cluster]["segment"][$id_segment]['theme'] = 'SEGMENT_KO';
                        break;
                    case $total_node_insegment:
                        self::$build_galera[$id_cluster]["segment"][$id_segment]['theme'] = 'SEGMENT_OK';
                        break;
                    default:
                        self::$build_galera[$id_cluster]["segment"][$id_segment]['theme'] = 'SEGMENT_PARTIAL';
                        break;
                }
            }

            self::$build_galera[$id_cluster]["members"] = $total_node;
            self::$build_galera[$id_cluster]["sst_method"] = implode(",",array_unique($sst_method));
            self::$build_galera[$id_cluster]["wsrep_provider_version"] = implode(",",array_unique($build));
            self::$build_galera[$id_cluster]["galera_version"] = implode(",",array_unique($version));
            self::$build_galera[$id_cluster]["wsrep_slave_threads"] = implode(",",array_unique($wsrep_slave_threads));


            self::$build_galera[$id_cluster]["node_available"] = $available;

            if ($available === 0) {
                self::$build_galera[$id_cluster]['config'] = 'GALERA_OUTOFORDER';
            }else if ($available === 1) {
                self::$build_galera[$id_cluster]['config'] = 'GALERA_EMERGENCY';
            }else if($available === 2) {
                self::$build_galera[$id_cluster]['config'] = 'GALERA_CRITICAL';
            }else if($available % 2 === 0) {
                self::$build_galera[$id_cluster]['config'] = 'GALERA_NOTICE';
            }else if ($available % 2 === 1){
                self::$build_galera[$id_cluster]['config'] = 'GALERA_AVAILABLE';
            }

            //Debug::debug($available);

        }

        //Debug::debug(self::$build_galera);
        //Debug::debug(($dot3_information));
    }

    //move to lib/Galera.php
    static public function extractProviderOption($wsrep_provider_options, $variable)
    {
        preg_match("/".preg_quote($variable)."\s*=[\s]+([\S]+);/", $wsrep_provider_options, $output_array);

        if (isset($output_array[1])) {
            return $output_array[1];
        } else {
            // il faudrait prevoir un mode stric afin de catch tous les problemes
            //throw new Exception("Impossible to find : ".$variable." in (".$wsrep_provider_options.")");
            return 0;
            
        }
    }

    static function setThemeToServer($theme, $id_mysql_server)
    {
        if (empty(self::$config[$theme])) {
            // error
            THROW new Exception("Impossible to find theme : $theme");
        }

        if (empty(self::$build_server[$id_mysql_server])) {
            THROW new Exception("Impossible to find id_mysql_server : $id_mysql_server");
        }

        $tmp = self::$config[$theme];

        Debug::debug($tmp, "COLOR");

        $tmp2 = array_merge( self::$build_server[$id_mysql_server], $tmp);
        self::$build_server[$id_mysql_server] = $tmp2;

        Debug::debug($tmp2, "COLOR_GOOD");

    }

    static public function reOrderVariable($variables, $filter = true)
    {
        $var_to_keep = array("mysql-interfaces", "admin-version");

        Debug::debug($variables, "VARIABLE PROXY");

        $data = array();
        foreach($variables as $variable)
        {
            if (count($variable) == 2)
            {
                $key = current($variable);
                $value = next($variable);
                
                if ($filter ===  true )
                {
                    if (! in_array($key, $var_to_keep))
                    {
                        continue;
                    }
                }
                $data[$key] = $value;
            }
        }

        Debug::debug($data, "NEW VERSION");
        
        return $data;
    }


    static public function getHostGroup($hostgroups)
    {
        $data = array();

        foreach($hostgroups as $hostgroup) {
            foreach($hostgroup as $key => $id_hostgroup) {
                if (strpos($key, "_hostgroup") !== false) {
                    $data[$id_hostgroup] = str_replace('_hostgroup', '', $key);
                }
            }
        }
        $data[100] = "mirroring";
        Debug::debug($data, "HOSTGROUP FLIP");

        return $data;
    }


    public function buildLinkBetweenProxySQL($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];

        $dot3_information = self::getInformation($id_dot3_information);

        foreach($group as $id_mysql_server)
        {
            if (! empty($dot3_information['information']['servers'][$id_mysql_server]['proxysql_servers']) && !empty($dot3_information['information']['servers'][$id_mysql_server]['is_proxy']))
            {
                $same = array();

                foreach($dot3_information['information']['servers'][$id_mysql_server]['proxysql_servers'] as $proxysql_servers)
                {
                    $host = $proxysql_servers['hostname'].':'.$proxysql_servers['port'];
                    $id_master = self::findIdMysqlServer($host, $id_dot3_information);

                    $same[] = $id_master.":".self::TARGET."";
                    if ($id_mysql_server == $id_master) {
                        continue;
                    }
                    
                    $tmp = self::$config['REPLICATION_OK'];
                    
                    //TO DO understand why only with proySQL it's generate a warning :  'Warning: Arrow type "117 -> 116" unknown - ignoring'
                    $tmp['tooltip'] = "$id_master -> $id_mysql_server";  
                    // tooltip in conflict with rank same

                    $tmp['options']['arrowsize'] = "1.5";
                    
                    $tmp['arrow'] = $id_master.":".self::TARGET." -> ".$id_mysql_server.":".self::TARGET."";

                    self::$build_ms[] = $tmp;

                    //Debug::debug($id_master ,"ID PROXYSQL");
                }
                
                if (count($same) >= 2){
                    self::$rank_same[] = "{ rank=same;".implode("; ", $same).";}\n";
                }
                
            }
        }
    }




    public static function resolveMaxScaleConnection(array $maxscale, string $maxscale_ip_port): array
    {
        //Debug::debug(self::$id_dot3_information, "id_dot3_information");

        $dot3_information = self::getInformation(self::$id_dot3_information);

        $tunnel = $dot3_information['information']['tunnel'];

        //Debug::debug(maxScale::removeArraysDeeperThan($dot3_information, 3), "TUNNEL");

        // Copie locale modifiable
        $resolved = $maxscale;

        //Debug::debug($resolved, "LISTENER");

        [$listenerAddress, $listenerPort] = self::splitAddressPort($maxscale_ip_port);

        // Si listener sur 0.0.0.0:port ou [::]:port
        $wildcard_candidates = [];
        if ($listenerPort !== null && $listenerPort !== '') {
            foreach (['0.0.0.0', '::'] as $wildcard) {
                $wildcard_candidates[] = $wildcard . ":" . trim($listenerPort);
            }
        }

        foreach ($wildcard_candidates as $candidate) {
            if (!empty($resolved[$candidate]['listener'])) {
                //Debug::debug("USE " . $candidate);
                $resolved[$maxscale_ip_port] = $resolved[$candidate];
                return $resolved;
            }
        }

        //Debug::debug($maxscale_ip_port, "maxscale_ip_port");
        //Debug::debug($wildcard_candidates, "maxscale_ip_port_fallbacks");

        // Si un tunnel existe pour cette IP:port
        if (!empty($tunnel[$maxscale_ip_port])) {
            $tunnel_ip_port = $tunnel[$maxscale_ip_port];
            //Debug::debug($tunnel_ip_port, "TUNNEL_FOUND");

            // Cas 1 : le tunnel mène directement à une entrée connue
            if (!empty($resolved[$tunnel_ip_port]['servers'])) {
                $resolved[$maxscale_ip_port] = $resolved[$tunnel_ip_port];
                return $resolved;
            }

            // Cas 2 : correspondance via 0.0.0.0 + port du tunnel
            [, $tunnelPort] = self::splitAddressPort($tunnel_ip_port);
            if ($tunnelPort !== null && $tunnelPort !== '') {
                foreach (['0.0.0.0', '::'] as $wildcard) {
                    $candidate = $wildcard . ":" . trim($tunnelPort);
                    //Debug::debug($candidate, "TEST {$wildcard} + Port originie before tunnel");
                    if (!empty($resolved[$candidate]['servers'])) {
                        $resolved[$maxscale_ip_port] = $resolved[$candidate];
                        break;
                    }
                }
            }
        }

        return $resolved;
    }

    private static function splitAddressPort(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [null, null];
        }

        if ($value[0] === '[') {
            $closing_bracket = strpos($value, ']');
            if ($closing_bracket !== false) {
                $host = substr($value, 1, $closing_bracket - 1);
                $port = ltrim(substr($value, $closing_bracket + 1), ':');
                return [trim($host), $port === '' ? null : trim($port)];
            }
        }

        $last_colon = strrpos($value, ':');

        if ($last_colon === false) {
            return [trim($value, '[]'), null];
        }

        $host = substr($value, 0, $last_colon);
        $port = substr($value, $last_colon + 1);

        return [trim($host, '[]'), $port === '' ? null : trim($port)];
    }


    public static function getTunnel($param)
    {
        // Vérifie que l’identifiant d’information Dot3 est défini
        if (empty(self::$id_dot3_information)) {
            throw new Exception(
                "[PMACONTROL-1000] Missing dot3 information ID — self::\$id_dot3_information cannot be empty in dot3::getTunnel().",
                1000 // Paramètre manquant (plage 1000–1999)
            );
        }

        // Vérifie le type de paramètre
        if (empty($param) || !is_array($param)) {
            throw new Exception(
                "[PMACONTROL-1001] Invalid parameter passed to dot3::getTunnel() — the first array key must be a string.",
                1001
            );
        }

        // Récupère les informations du tunnel
        $dot3_information = self::getInformation(self::$id_dot3_information);
        if (empty($dot3_information['information']['tunnel'])) {
            throw new Exception(
                "[PMACONTROL-4001] No tunnel information found in dot3::getTunnel() — application logic error.",
                4001
            );
        }

        $tunnel = $dot3_information['information']['tunnel'];

        // Si on a un paramètre de type ip:port, on valide le format
        if (!empty($param[0])) {
            $ip_port = $param[0];

            // Vérifie le format IPv4:port
            if (!preg_match('/^(\d{1,3}\.){3}\d{1,3}:\d{1,5}$/', $ip_port)) {
                throw new Exception(
                    "[PMACONTROL-1002] Invalid ip_port format in dot3::getTunnel() — expected IPv4:port, got '{$ip_port}'.",
                    1002
                );
            }

            // Vérifie que ce tunnel existe dans la liste
            return !empty($tunnel[$ip_port]) ? $tunnel[$ip_port] : false;
        }

        // Si aucun paramètre, renvoie la liste complète des tunnels
        return $tunnel;
    }


    public function after($param)
    {
        if (!function_exists('posix_geteuid')) {
            function posix_geteuid(): int { return 0; }
        }

        if (posix_geteuid() === 0) {
            usleep(5000);
            shell_exec("chown www-data:www-data -R ".TMP."dot");
        }
    }

    public function generateGroupByServerId($information)
    {
        $tmp_group = array();
        
        foreach($information['servers'] as $id_mysql_server => $server)
        {
            $server['id_mysql_server'] = $id_mysql_server;
        }


        return $tmp_group;
    }

    public function createGarb($information, $id_mysql_server)
    {
        $server = $information['servers'][$id_mysql_server] ?? null;
        if (!$server) {
            throw new Exception("Server not found", 404);
        }

        //generate next id in $information['servers']
        $next_id = max(array_keys($information['servers'])) + 1;

        $information['servers'][$next_id] = $information['servers'][$id_mysql_server];

        $information['servers'][$next_id]['display_name'] = 'garb';
        $information['servers'][$next_id]['hostname'] = 'garb';
        $information['servers'][$next_id]['id_mysql_server'] = $next_id;

        //remplace moi le dernier xxx par *
        //$information['servers'][$next_id]['ip_real'] = preg_replace('/\d+\.\d+\.\d+\.\d+$/', '*', $server['ip_real']);

        self::$information[self::$id_dot3_information]['information'] = $information;
        
        //Debug::debug($information, "Information after creating garb");  
        
        return $next_id;


    
        // Logique de création de l'arbitre
    }
}
