<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\Graphviz;
use \Glial\Synapse\Controller;
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

    static $config = array();

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
        $versioning = "";
        $versioning2 = "";

        //to prevent id of daemon in comment
        if (! is_a($date_request, 'DateTime')) {
            $date_request ="";
        }

        if ( ! empty($date_request))
        {
            $versioning = " WHERE '".$date_request."' between a.row_start and a.row_end ";
            $versioning2 = " WHERE '".$date_request."' between b.row_start and b.row_end AND '".$date_request."' between c.row_start and c.row_end ";
            
            $date_request = array($date_request);
        }

        //Debug::debug($date_request, "Date");

        $db  = Sgbd::sql(DB_DEFAULT);
        $all = Extraction2::display(array("variables::hostname", "variables::binlog_format", "variables::time_zone", "variables::version",
                "variables::system_time_zone", "variables::wsrep_desync", "variables::port", "variables::is_proxysql", "variables::wsrep_cluster_address",
                "variables::wsrep_cluster_name", "variables::wsrep_provider_options", "variables::wsrep_on", "variables::wsrep_sst_method",
                "variables::wsrep_desync", "status::wsrep_cluster_status", "status::wsrep_local_state", "status::wsrep_local_state_comment",
                "status::wsrep_incoming_addresses", "variables::wsrep_patch_version","mysql_server::available", "mysql_server::ping", "mysql_server::error",
                "status::wsrep_cluster_size", "status::wsrep_cluster_state_uuid", "status::wsrep_gcomm_uuid", "status::wsrep_local_state_uuid",
                "slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "mysql_available", "mysql_error","variables::version_comment","is_proxy", "variables::server_id","read_only",
                "slave::last_sql_error", "slave::last_sql_errno", "slave::using_gtid", "variables::is_proxysql",
                "proxysql_main_var::mysql-interfaces", "proxysql_main_var::admin-version", "proxysql_runtime_server::mysql_servers",
                "auto_increment_increment", "auto_increment_offset"
            ),array() , $date_request);

        $sql = "SELECT id as id_mysql_server, ip, port, display_name, is_proxy
                FROM mysql_server a ".$versioning."
                UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_proxy  
                from alias_dns b INNER JOIN mysql_server c ON b.id_mysql_server =c.id
                ".$versioning2.";";

        //Debug::sql($sql);

        $res = $db->sql_query($sql);

        $server_mysql = array();
        //$mapping_master = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_mysql[$arr['id_mysql_server']] = $arr;

            //TODO add alias_dns and virtual_ip
            $data['mapping'][$arr['ip'].':'.$arr['port']] = $arr['id_mysql_server'];
        }
        $data['servers'] = array_replace_recursive($all, $server_mysql);

        
        $sql = "select `id`, `id_mysql_server`, `hostname`, `port` from proxysql_server a $versioning;";

        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'][$arr['id_mysql_server']] = array_merge($arr, $data['servers'][$arr['id_mysql_server']]);

            //json decode in same time
            $data['servers'][$arr['id_mysql_server']]['mysql_servers'] = json_decode($data['servers'][$arr['id_mysql_server']]['mysql_servers'], true);
        }

        Debug::debug($data, "SERVER");

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

        $previous_md5 = '';
        $dot3_information = self::getInformation('');
        
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
                $tmp_group[$id_group][] = $server['id_mysql_server'];
                $master = $slave['master_host'].":" .$slave['master_port'];

                if (! empty($information['mapping'][$master])) {
                    $tmp_group[$id_group][] = $information['mapping'][$master];
                }
                else {
                    echo "This master was not found : ".$master."\n";
                }
                
                $id_group++;
            }   
        }
        return $tmp_group;
    }


    public function generateGroupProxySQL($information)
    {
        $tmp_group = array();

        foreach($information['servers'] as $id_mysql_server => $server)
        {
            if ($server['is_proxysql'] != "1")
            {
                continue;
            }


            $tmp_group[$id_mysql_server] = array();
            $tmp_group[$id_mysql_server][] = $id_mysql_server;
            
            foreach($server['mysql_servers'] as $proxysql)
            {
                $server = $proxysql['hostname'].':'.$proxysql['port'];

                $tmp_group[$id_mysql_server][] = $information['mapping'][$server];
                //Debug::debug($server, 'server');
            }
        }

        return $tmp_group;
    }


    public function generateGroupGalera($information)
    {
        $tmp_group = array();

        $id_group = 0;
        foreach($information['servers'] as $id_mysql_server => $server)
        {
            $id_group++;
            //$tmp_group[$idproxy] = array();
            if (!empty($server['wsrep_on']) && strtolower($server['wsrep_on']) === "on") {
                $servers = $this->getIdMysqlServerFromGalera($server['wsrep_cluster_address']);
                $servers2 = $this->getIdMysqlServerFromGalera($server['wsrep_incoming_addresses']);

                foreach($servers as $ip_port)
                {
                    if (!empty($information['mapping'][$ip_port])) {
                        $tmp_group[$id_group][] = $information['mapping'][$ip_port];
                    }
                    else {
                        //autodetect autoadd Mysql::autodetect($server['id_mysql_server'], $ip_port);
                    }
                }
                $id_group++;
                foreach($servers2 as $ip_port)
                {
                    if (!empty($information['mapping'][$ip_port])) {
                        $tmp_group[$id_group][] = $information['mapping'][$ip_port];
                    }
                    else {
                        //autodetect autoadd Mysql::autodetect($server['id_mysql_server'], $ip_port);
                    }
                }
            }
        }
        return $tmp_group;
    }

    /*
        prend en paramètre wsrep_cluster_address ou wsrep_incoming_addresses
        a bouger dans App\Lib\Galera
    */

    public function getIdMysqlServerFromGalera($cluster_address)
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

        return $resultArray;
    }


    public function run($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_dot3_information = $this->generateInformation($param);

        $info = self::getInformation($id_dot3_information);
        //TODO : add if date > now => return true to not was time to regenerate dot for nothing

        $groups = $this->getGroup(array($id_dot3_information));

        foreach($groups as $group)
        {
            self::$build_ms = array();
            self::$build_server = array();

            $this->buildLink(array($id_dot3_information, $group));
            $this->buildServer(array($id_dot3_information, $group));
            //$this->linkProxySQLAdmin(array($id_dot3_information, $group));
            $this->linkHostGroup(array($id_dot3_information, $group));
            
            $dot = $this->generateDot();

            $reference = md5(json_encode($group));
            $file_name = Graphviz::generateDot($reference, $dot);

            $this->saveGraph($id_dot3_information, $file_name, $dot, $group);
        }
    }

    public function saveGraph($id_dot3_information, $file_name, $dot, $group)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $md5 = md5($dot);
        $dot3_graph = array();
        $commit = Git::getCurrentCommit();


        $sql = "SELECT id FROM dot3_graph WHERE md5 = '".$md5."'";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $id_dot3_graph = $ob->id;
            
            $dot3_graph['dot3_graph']['id'] = $id_dot3_graph;
        }


        $dot3_graph['dot3_graph']['filename'] = $file_name;
        $dot3_graph['dot3_graph']['dot'] = $dot;
        $dot3_graph['dot3_graph']['svg'] = file_get_contents($file_name);
        $dot3_graph['dot3_graph']['md5'] = $md5;
        $dot3_graph['dot3_graph']['version'] = $commit['version'];
        $dot3_graph['dot3_graph']['commit'] = $commit['build'];
        $id_dot3_graph = $db->sql_save($dot3_graph);


        $dot3_cluster = array();
        $sql = "SELECT id FROM dot3_cluster WHERE id_dot3_graph = ".$id_dot3_graph." AND id_dot3_information = ".$id_dot3_information."";
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            $id_dot3_graph = $ob->id;
            
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


    }

    public function generateDot()
    {
        
        $dot = '';
        $dot .= Graphviz::generateStart();

        foreach(self::$build_server as $server) {
            $dot .= Graphviz::generateServer($server);
        }   
    
        foreach(self::$build_ms as $edge) {
            $dot .= Graphviz::generateEdge($edge);
        }  
        $dot .= Graphviz::generateEnd();
//        Debug::debug($dot);

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
                $lst = array_merge($lst, $this->array_values_recursive($v)
                );
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
        $master_slave = $this->generateGroupMasterSlave($dot3_information['information']);
        $proxysql = $this->generateGroupProxySQL($dot3_information['information']);

        $group = $this->array_merge_group(array_merge($galera, $master_slave, $proxysql));

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
                        $tmp['tooltip'] = "DELAY : ".$slave['seconds_behind_master'].__("seconds");
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
                    }
                    elseif(strtolower($slave['slave_io_running']) == 'no' 
                    && strtolower($slave['slave_sql_running']) == 'no' )
                    {
                        $tmp = self::$config['REPLICATION_STOPPED'];
                        $tmp['tooltip'] = __("Replication stopped");
                    }
                    elseif(strtolower($slave['slave_io_running']) == 'no' 
                    && strtolower($slave['slave_sql_running']) == 'no' )
                    {
                        $tmp = self::$config['REPLICATION_ERROR_BOTH'];
                        $tmp['tooltip'] = "ERROR : ".$slave['last_sql_errno'].":".$slave['last_sql_error']
                        ." - ERROR : ".$slave['last_io_errno'].":".$slave['last_io_error'];
                    }
                    else{
                        $tmp = self::$config['REPLICATION_BUG'];
                        $tmp['tooltip'] = __("Case unknow !");
                    }

                    //override ALL is slave is offline
                    if (empty($dot3_information['information']['servers'][$id_mysql_server]['mysql_available']))
                    {
                        $tmp = self::$config['REPLICATION_BLACKOUT'];
                        $tmp['tooltip'] = __("Server is offline");
                    }
                    
                    $tmp['options']['penwidth'] = "3";
                    if (!empty($slave['using_gtid'])) {
                        if (strtolower($slave['using_gtid']) != "no") {
                            $tmp['color'] = $tmp['color'].":#ffffff:".$tmp['color'];
                            $tmp['options']['penwidth'] = "2";
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
            {
                $tmp = self::$config['NODE_BUSY'];
            }

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
            if (empty($server['is_proxysql']) && $server['is_proxysql'] != "1") {
                continue;
            }

            $i = 0;
            foreach($server['mysql_servers'] as $hostgroup) {
                $i++;

                $host = $hostgroup['hostname'].':'.$hostgroup['port'];
                $id_mysql_server_target = self::findIdMysqlServer($host, $id_dot3_information);

                $tmp = array();
                $tmp = self::$config['PROXYSQL_'.$hostgroup['status']];
                //$tmp['arrow'] = '"'.$id_mysql_server.':hg'.$i.'" -> "'.$id_mysql_server_target.':target"';
                $tmp['arrow'] = ''.$id_mysql_server.':hg'.$i.' -> '.$id_mysql_server_target.':target';
                
                $tmp['tooltip'] = $hostgroup['status'];
                $tmp['options']['dir'] = 'none';
                $tmp['options']['style'] = $tmp['style'];
                
                self::$build_ms[] = $tmp;
            }
        }

        //Debug::debug(self::$build_ms);
    }

    public function loadConfigColor()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `architecture_legend` order by `order`;";
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
            Debug::debug($dot_information, "mapping");
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
        
        //Debug::sql($sql);
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
        $sql = "SELECT * FROM `architecture_legend` WHERE `type`= 'REPLICATION' order by `order`;";
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

        $edge['color'] = "#000000";

        $legend .= 'key:i' . $i . ':e -> key2:i' . $i . ':w [color="' . $edge['color'] . ':#ffffff:' . $edge['color'] . '" arrowsize="1.5" style=' . $edge['style'] . ',penwidth="2"]' . "\n";
        $i++;
        $legend .= 'key:i' . $i . ':e -> key2:i' . $i . ':w [color="' . $edge['color'] . '" arrowsize="1.5" style=' . $edge['style'] . ',penwidth="2"]' . "\n";
        $i++;


        $legend .= '
  }
}';

        //echo str_replace("\n", "<br />",htmlentities($legend));

        file_put_contents(TMP . "/legend", $legend);

        $data['legend'] = $this->getRenderer($legend);

        $this->set('data', $data);

        //https://dreampuf.github.io/GraphvizOnline/
    }


    public function getRenderer($dot)
    {
        $file_id = uniqid();

        $tmp_in  = "/tmp/dot_" . $file_id . ".dot";
        $tmp_out = "/tmp/svg_" . $file_id . ".svg";

        file_put_contents($tmp_in, $dot);

        $cmd = "dot " . $tmp_in . " -Tsvg -o " . $tmp_out . " 2>&1";

        $ret = shell_exec($cmd);

        if (!empty($ret)) {
            throw new \Exception('PMACTRL-842 : Dot2/getRenderer ' . trim($ret), 70);
        }

        $svg = file_get_contents($tmp_out);

        unlink($tmp_in);
        unlink($tmp_out);

        return $svg;
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

}
