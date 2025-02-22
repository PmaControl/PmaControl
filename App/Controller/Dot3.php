<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\Graphviz;
use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Extraction2;
use \App\Library\Debug;
use \App\Library\Git;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

use \Glial\Sgbd\Sgbd;

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

    static $information = array();

    // build link MasterSlave
    static $build_ms = array();

    // build server
    static $build_server = array();

    static $build_galera = array();

    static $config = array();

    static $galera = array();

    var $logger;

    public function before($param)
    {
        $this->loadConfigColor();
        $monolog       = new Logger("Dot3");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function generateInformation($param)
    {
	    Debug::parseDebug($param);

        $date_request = $param[0] ?? "";
        $versioning = "WHERE 1=1 ";
        $versioning2 = "WHERE 1=1 ";

        //to prevent id of daemon in comment
        if (! is_a($date_request, 'DateTime')) {
            $date_request ="";
        }

        if ( ! empty($date_request))
        {
            $versioning = "WHERE '".$date_request."' between a.row_start and a.row_end ";
            $versioning2 = "WHERE '".$date_request."' between b.row_start and b.row_end AND '".$date_request."' between c.row_start and c.row_end ";
            
            $date_request = array($date_request);
        }

        //Debug::debug($date_request, "Date");

        $db  = Sgbd::sql(DB_DEFAULT);

        // "status::wsrep_cluster_status"  => not exist anymore ?
        $all = Extraction2::display(array("variables::hostname", "variables::binlog_format", "variables::time_zone", "variables::version",
                "variables::system_time_zone", "variables::port", "variables::is_proxysql", "variables::wsrep_cluster_address",
                "variables::wsrep_cluster_name", "variables::wsrep_provider_options", "variables::wsrep_on", "variables::wsrep_sst_method",
                "variables::wsrep_desync", "status::wsrep_local_state", "status::wsrep_local_state_comment", "status::wsrep_cluster_status",
                "status::wsrep_incoming_addresses", "variables::wsrep_patch_version", "mysql_ping", "mysql_server::error",
                "status::wsrep_cluster_size", "status::wsrep_cluster_state_uuid", "status::wsrep_gcomm_uuid", "status::wsrep_local_state_uuid",
                "slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running","variables::wsrep_slave_threads",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "mysql_available", "mysql_error","variables::version_comment","is_proxy", "variables::server_id","read_only",
                "slave::last_sql_error", "slave::last_sql_errno", "slave::using_gtid", "variables::is_proxysql","variables::binlog_row_image",
                "proxysql_main_var::mysql-interfaces", "proxysql_main_var::admin-version", "proxysql_runtime_mysql_servers",
                "auto_increment_increment", "auto_increment_offset", "log_slave_updates", "variables::system_time_zone", "status::wsrep_provider_version"
            ),array() , $date_request);

        $sql = "SELECT id as id_mysql_server, ip, port, display_name, is_proxy, ip as ip_real, port as port_real
                FROM mysql_server a ".$versioning."
                UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy, c.ip as ip_real, c.port as port_real
                from alias_dns b INNER JOIN mysql_server c ON b.id_mysql_server =c.id
                ".$versioning2.";";

        $res = $db->sql_query($sql);

        $server_mysql = array();
        //$mapping_master = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_mysql[$arr['id_mysql_server']] = $arr;

            //TODO add alias_dns and virtual_ip
            $data['mapping'][$arr['ip'].':'.$arr['port']] = $arr['id_mysql_server'];
        }
        $data['servers'] = array_replace_recursive($all, $server_mysql);

        $sql = "select `id`, `id_mysql_server`, `hostname`, `port` from proxysql_server a $versioning AND a.id_mysql_server IS NOT NULL;";
        Debug::debug($sql);

        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'][$arr['id_mysql_server']] = array_merge($arr, $data['servers'][$arr['id_mysql_server']]);

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

        //remove all date to be able to compare data with date
        array_walk_recursive($data_for_md5, function(&$value, $key) use (&$array) {
            if ($key == 'date') {
                $value = null; // ou utilisez unset si vous pouvez obtenir une référence au parent.
            }
        });

        $json = json_encode($data);
        $md5 = md5(json_encode($data_for_md5));


        $this->logger->warning("MD5 : $md5");

        $previous_md5 = '';
        $dot3_information = self::getInformation('');

        //Debug::debug($dot3_information, 'Dot_information');
        
        if (!empty($dot3_information['md5'])) {
            $previous_md5 = $dot3_information['md5'];
            $id_dot3_information = $dot3_information['id'];
        }

        if ($previous_md5 != $md5)
        {
            $commit = Git::getCurrentCommit();

            $dot3 = array();
            $dot3['dot3_information']['date_generated'] = date('Y-m-d H:i:s');
            $dot3['dot3_information']['information'] = $json;
            $dot3['dot3_information']['md5'] = $md5;
            $dot3['dot3_information']['version'] = $commit['version'];
            $dot3['dot3_information']['commit'] = $commit['build'];
            $id_dot3_information =  $db->sql_save($dot3);
        }

        $this->logger->warning("id_dot3_information : $id_dot3_information");

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
                    echo "This master was not found : ".$master."\n";
                }
                $id_group++;
                
            }   
        }

        Debug::debug($tmp_group, "MASTER SLAVE");

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

            if (! empty($server['proxysql_runtime_mysql_servers']))
            {
                foreach($server['proxysql_runtime_mysql_servers'] as $backend) {
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


    public function generateGroupGalera($information)
    {
        $tmp_group = array();

        //$id_group = 0;  // replaced by $server['id_mysql_server'] for test
        foreach($information['servers'] as $id_mysql_server => $server)
        {
            //$id_group++;
            //$tmp_group[$idproxy] = array();
            if (!empty($server['wsrep_on']) && strtolower($server['wsrep_on']) === "on") {
                $servers = self::getIdMysqlServerFromGalera($server['wsrep_cluster_address']);
                $servers2 = self::getIdMysqlServerFromGalera($server['wsrep_incoming_addresses']);

                foreach($servers as $ip_port)
                {
                    if (!empty($information['mapping'][$ip_port])) {
                        $tmp_group[$server['id_mysql_server']][] = $information['mapping'][$ip_port];
                    }
                    else {
                        //autodetect autoadd Mysql::autodetect($server['id_mysql_server'], $ip_port);
                    }
                }
                //$id_group++;
                foreach($servers2 as $ip_port)
                {
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
            self::$build_galera = array();
            self::$build_ms = array();
            self::$build_server = array();

            //Debug::debug($group);
            

            //Debug::debug(self::$build_galera);

            $this->buildServer(array($id_dot3_information, $group));

            // il faut builder les serveur avant Galera => Galera va surcharger le noeud en cas de desync / donor / non-primary
            $this->buildGaleraCluster(array($id_dot3_information, $group));

            $this->buildLink(array($id_dot3_information, $group));
            //Debug::debug($group, "GROUP");

            //$this->linkProxySQLAdmin(array($id_dot3_information, $group));
            $this->linkHostGroup(array($id_dot3_information, $group));

            $dot = $this->writeDot();

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
        $commit = Git::getCurrentCommit();

        $sql = "SET AUTOCOMMIT=0;";
        $res = $db->sql_query($sql);
        $sql = "BEGIN";
        $res = $db->sql_query($sql);

        $this->logger->warning("MD5 (DOT) : $md5");

        $sql = "SELECT id FROM dot3_graph WHERE md5 = '".$md5."'";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $id_dot3_graph = $ob->id;
            
            $dot3_graph['dot3_graph']['id'] = $id_dot3_graph;
            $this->logger->emergency("ID : ".$id_dot3_graph);
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
            $dot3_graph['dot3_graph']['version'] = $commit['version'];
            $dot3_graph['dot3_graph']['commit'] = $commit['build'];

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

        foreach(self::$build_server as $server) {

            //Debug::debug($server);
            $dot .= Graphviz::generateServer($server);
        }

        $dot .= Graphviz::generateGalera(self::$build_galera);
    
        foreach(self::$build_ms as $edge) {
            $dot .= Graphviz::generateEdge($edge);
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
        Debug::parseDebug($param);

        $id_dot3_information = $param[0];
        $dot3_information = self::getInformation($id_dot3_information);

        $galera = $this->generateGroupGalera($dot3_information['information']);
        //Debug::debug($galera, "GALERA");

        $master_slave = $this->generateGroupMasterSlave($dot3_information['information']);
        $proxysql = $this->generateGroupProxySQL($dot3_information['information']);

        $group = $this->array_merge_group(array_merge($galera, $master_slave, $proxysql));

        //Debug::debug($group, "GROUP");
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

                foreach($dot3_information['information']['servers'][$id_mysql_server]['@slave'] as $key => $slave)
                {

                    $host = $slave['master_host'].':'.$slave['master_port'];
                    $id_master = self::findIdMysqlServer($host, $id_dot3_information);

                    $tmp = array();

                    //ALL OK
                    if (strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'yes' 
                    && $slave['seconds_behind_master'] == "0")
                    {
                        $tmp = self::$config['REPLICATION_OK'];
                        $tmp['tooltip'] = "OK";
                    }
                    //replication STOPED
                    elseif(strtolower($slave['slave_io_running']) == 'yes' 
                    && strtolower($slave['slave_sql_running']) == 'yes' 
                    && $slave['seconds_behind_master'] == "NULL")
                    {
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
                    
                    $tmp['arrow'] = $id_master.":target -> ".$id_mysql_server.":target";

                    self::$build_ms[] = $tmp;
                }
                
            }
        }


        //Debug::debug($group , "debug");

    }

    public function buildServer($param)
    {
        $id_dot3_information = $param[0];
        $group = $param[1];
        $dot3_information = self::getInformation($id_dot3_information);
        

        foreach($group as $id_mysql_server)
        {
            $server = $dot3_information['information']['servers'][$id_mysql_server];
            $tmp = array();
            
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
            foreach($server['proxysql_runtime_mysql_servers'] as $hostgroup) {
                $i++;

                $host = $hostgroup['hostname'].':'.$hostgroup['port'];

                $tmp = array();
                
                $tmp = self::$config['PROXYSQL_'.$hostgroup['status']];
                $tmp['tooltip'] = "STATUS : ".$hostgroup['status'];

                foreach($server['proxy_connect_error'] as $hostname => $error)
                {
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
                
                $id_mysql_server_target = self::findIdMysqlServer($host, $id_dot3_information);
                //headlabel="*", taillabel="1"
                
                $port = crc32($hostgroup['hostgroup_id'].':'.$host);
                
                //$tmp['arrow'] = '"'.$id_mysql_server.':hg'.$i.'" -> "'.
                $id_mysql_server_target.':target"';
                $tmp['arrow'] = $id_mysql_server.':'.$port.' -> '.$id_mysql_server_target.':target';
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
                }

                self::$build_ms[] = $tmp;
            }
        }

        //Debug::debug(self::$build_ms);
    }

    public function loadConfigColor()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `dot3_legend` order by `order`;";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            self::$config[$arr['const']] = $arr;
        }
    }

    private static function findIdMysqlServer($host, $id_dot3_information)
    {        
        $dot_information = self::getInformation($id_dot3_information);

        if (empty($dot_information['information']['mapping']))
        {
            throw new \Exception('Impossible to acess to item Mapping');
        }

        if (! empty($dot_information['information']['mapping'][$host])) {
            return  $dot_information['information']['mapping'][$host];
        }
        else {
            //Debug::debug($dot_information, "mapping");
            // create box => autodetect
            echo "This master was not found : ".$host."\n";
            die();
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

    function obfuscateIP($ip) {
        $segments = explode('.', $ip);
        $obfuscatedSegments = [];
    
        // Définir un décalage fixe pour chaque segment de l'IP
        $offsets = [24, 100, 56, 78]; // Ces valeurs peuvent être ajustées selon les besoins
    
        foreach ($segments as $index => $segment) {
            // Appliquer le décalage, s'assurer que le résultat reste dans la plage 0-255
            $newSegment = ($segment + $offsets[$index]) % 256;
            $obfuscatedSegments[] = $newSegment;
        }
    
        // Reconstruire l'IP obfusquée
        return implode('.', $obfuscatedSegments);
    }

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

        //echo str_replace("\n", "<br />",htmlentities($legend));

        file_put_contents(TMP . "/legend", $legend);


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


    public function purgeAll($param)
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
                foreach($cluster as $id_mysql_server)
                {
                    if (isset($dot3_information['information']['servers'][$id_mysql_server]['wsrep_cluster_state_uuid']))
                    {
                        $server_uuid = $dot3_information['information']['servers'][$id_mysql_server]['wsrep_cluster_state_uuid'];
                        $cluster_uuid[$server_uuid][] = $id_mysql_server;
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
            //throw new \Exception("Impossible to find : ".$variable." in (".$wsrep_provider_options.")");
            return 0;
            
        }
    }

    static function setThemeToServer($theme, $id_mysql_server)
    {
        if (empty(self::$config[$theme])) {
            // error
            THROW new \Exception("Impossible to find theme : $theme");
        }

        if (empty(self::$build_server[$id_mysql_server])) {
            THROW new \Exception("Impossible to find id_mysql_server : $id_mysql_server");
        }


        

        $tmp = self::$config[$theme];

        Debug::debug($tmp, "COLOR");

        $tmp2 = array_merge( self::$build_server[$id_mysql_server], $tmp);
        self::$build_server[$id_mysql_server] = $tmp2;

        Debug::debug($tmp2, "COLOR_GOOD");

    }

}
