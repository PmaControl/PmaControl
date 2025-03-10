<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction2;
use App\Library\Format;
use App\Library\Debug;
use App\Library\Mysql;
use \Glial\Sgbd\Sgbd;
use App\Library\Chiffrement;

class Demo extends Controller {

    public function index() {
        


    }

    /*
    * Only used for demo with Docker
    */

    public function getTestServer($param = array())
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $env = 11;
        $tags = array("docker", "mariadb");

        $sql = "SELECT a.* FROM mysql_server a 
        INNER JOIN `link__mysql_server__tag` b ON a.id = b.id_mysql_server
        INNER JOIN tag c ON b.id_tag = c.id
        WHERE c.name in ('mariadb', 'docker')
        GROUP BY a.id having count(1) = 2;";

        $res = $db->sql_query($sql);

        $tab_version = Extraction2::display(array('version', 'version_comment'));
        $data['mysql_server'] = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $formated = Format::getMySQLNumVersion($tab_version[$arr['id']]['version'],$tab_version[$arr['id']]['version_comment']);
            
            //Debug::debug($formated,"formated");

            $data['mysql_server'][$arr['id']] = $arr;
            $data['mysql_server'][$arr['id']]['version'] = $formated['number'];
            $data['mysql_server'][$arr['id']]['fork'] = $formated['fork'];
            $data['mysql_server'][$arr['id']]['used_as_master'] = 0;
        }

        return $data;
    }

    public function AssociateServerByLevel($param = array())
    {
        Debug::parseDebug($param);

        $data = $this->getTestServer();

        // Trier le tableau en utilisant la fonction de comparaison personnalisée
        uasort($data['mysql_server'], array($this,'compareVersions'));

        $nb_mysql_server = count($data['mysql_server']);

        $nombre_de_niveau = $this->obtenirPlusGrandChiffre($nb_mysql_server);

        Debug::debug($nombre_de_niveau,'nombre_de_niveau');

        $reste = $nb_mysql_server -($nombre_de_niveau * ($nombre_de_niveau+1))/2;
        $level = 1;

        $data['bylevel'] = array();

        for($i = $nombre_de_niveau; $i > 0; $i--)
        {
            $nb_server_fo_this_level = $i;

            if ($level === 1) {
                $nb_server_fo_this_level += $reste;
            }

            foreach($data['mysql_server'] as $id_mysql_server => $server){
                if (empty($server['level'])) {

                    $data['mysql_server'][$id_mysql_server]['level'] = $level;
                    $data['bylevel'][$level][] = $data['mysql_server'][$id_mysql_server];
                    $nb_server_fo_this_level--;
                }

                if ($nb_server_fo_this_level === 0){
                    break;
                }

                Debug::debug($nb_server_fo_this_level,"nb_server_fo_this_level");
            }

            $level++;
        }

        Debug::debug($data['bylevel'], "WITH LEVEL");

        return $data;
    }

    // Fonction de comparaison personnalisée pour trier par 'version'
    function compareVersions($a, $b) {
        // Utilisez la fonction version_compare pour comparer les versions
        return version_compare($a['version'], $b['version']);
    }


    function compareDigit($a, $b) {
        // Utilisez la fonction version_compare pour comparer les versions
        return version_compare($a['used_as_master'], $b['used_as_master']);
    }
    /*
    *   Obtenir le plus grand terme (n) pour la suite suivante en fonction de S
    *   s = 1 + 2 + 3 + 4 + 5 + (n-2) + (n-1) + n
    */
    function obtenirPlusGrandChiffre($s) {
        // Calculer la somme des chiffres
        $somme = 0;
        $n = 1;
    
        while ($somme + $n <= $s) {
            $somme += $n;
            $n++;
        }
    
        return --$n;
    }

    // each server got 2 slaves
    public function generatePair($param = array())
    {
        $nb_slave = 2;

        Debug::parseDebug($param);

        $data   = $this->AssociateServerByLevel();
        $levels = array_reverse(array: $data['bylevel'], preserve_keys: true);
        $master_slave = array();

        foreach($levels as $key1 => $lvl)
        {
            foreach($lvl as $key2 => $server)
            {
                foreach($server as $key3 => $val)
                {
                    if (! in_array($key3, array("id", "version","used_as_master", "level" )))
                    {
                        unset($levels[$key1][$key2][$key3]);
                    }
                }
            }
        }

        Debug::debug($levels, 'data');

        $id_parsed = array();

        foreach($levels as $mysql_servers){
            foreach($mysql_servers as $mysql_server) {

                $loop = 0;

                $level = $mysql_server['level'];

                if ($level === 1){
                    break;
                }

                $id_parsed['slave'][$level][] = $mysql_server['id'];

                Debug::debug($level, "LEVEL");

                //tri by used_as_master
                $level_master = $level-1;
                Debug::debug($level_master, "LEVEL_MASTER");
                Debug::debug("###########################################################################################");
                //Debug::debug($mysql_server,"mysql_server");

                // We make a sort to get the server who are less used in master as first
                $temp_levels = $levels;

                Debug::debug($temp_levels[$level_master], "BEFORE");

                uasort($temp_levels[$level_master], array($this,'compareDigit'));
                Debug::debug($temp_levels[$level_master], "AFTER");

                $i = 0;
                Debug::debug($mysql_server['id'], "id_mysql__slave");
                Debug::debug("-----------------------------------------------------------");
                foreach($temp_levels[$level_master] as $key => $master){
                    

                    Debug::debug($levels[$level_master][$key]['used_as_master'], "USED_AS_MASTER______");
                    Debug::debug($temp_levels[$level_master][$key]['used_as_master'], "TMP_USED_AS_MASTER_2");
                    Debug::debug("-----------------------------------------------------------");

                    if ($nb_slave == $i){
                        break;
                    }

                    $master_slave[] = array($master['id'] => $mysql_server['id']);
                    $id_parsed['master'][$level_master][] = $master['id'];

                    Debug::debug($master['id'], "MASTER CHOOSEN");

                    $levels[$level_master][$key]['used_as_master']++;

                    $i++;
                }
            }
            //break;
        }

        Debug::debug($id_parsed);

        Debug::debug($levels, "FULL");
        Debug::debug($master_slave, "PAIR");

        return $master_slave;
    }

    public function configMasterSlave($param)
    {
        Debug::parseDebug($param);

        Debug::debug($param, "PARAMS");
        $id_mysql_server__master = $param[0];
        $id_mysql_server__slave  = $param[1];
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server WHERE id in ($id_mysql_server__master, $id_mysql_server__slave);";

        $res = $db->sql_query($sql);

        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)){
            $mysql_server[$arr['id']] = $arr;
        }

        if ($mysql_server[$id_mysql_server__master]['ip'] === "127.0.0.1")
        {
            $cmd = "ip -4 addr show | awk '/inet / {print $2}' | cut -d/ -f1 | grep -vE '^(127\.0\.0\.1|172\.17\.)'";
            $ipv4_master = explode("\n",trim(shell_exec($cmd)))[0];
            
            Debug::debug($ipv4_master, "IPV4 master");

            if (filter_var($ipv4_master, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
                $mysql_server[$id_mysql_server__master]['ip'] = $ipv4_master;
            }
            else {
                throw new \Exception("the slave Docker cannot access to master with IP 127.0.0.1, and impossible to get one alias or an other one");
            }            
        }

        $db_master = Sgbd::sql($mysql_server[$id_mysql_server__master]['name']);
        $db_slave = Sgbd::sql($mysql_server[$id_mysql_server__slave]['name']);

        $res = $db_master->sql_query("SHOW MASTER STATUS");
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)){
            Debug::debug($arr);
            $FILE = $arr['File'];
            $POSITION = $arr['Position'];
        }

        $channel = "slave".$id_mysql_server__master."_".$id_mysql_server__slave;
        $mysql_user = "replication_".$id_mysql_server__master."_".$id_mysql_server__slave;
        $mysql_password2 = $this->randomPassword();

        $mysql_user = "replication";
        $mysql_password2 = "replication";


        $ip_slave = $mysql_server[$id_mysql_server__slave]['ip'];
        $port_slave = $mysql_server[$id_mysql_server__slave]['port'];
        $login_slave = $mysql_server[$id_mysql_server__slave]['login'];
        $password_slave = Chiffrement::decrypt($mysql_server[$id_mysql_server__slave]['passwd']);

        $sql = "GRANT ALL ON *.* TO '$mysql_user'@'%' IDENTIFIED BY '$mysql_password2';";

        Debug::sql($sql);
        $db_master->sql_query($sql);

        Debug::debug($FILE, "FILE");
        Debug::debug($POSITION, "POSITION");

        $res2 = $db_slave->sql_query("SHOW ALL SLAVES STATUS");
        while($arr2 = $db_slave->sql_fetch_array($res2, MYSQLI_ASSOC)){

            if ($arr2['Connection_name'] === "$channel"){
                $sql = "STOP SLAVE '".$channel."';";
                Debug::sql($sql);
                $db_slave->sql_query("STOP SLAVE '".$channel."';");
                $sql = "RESET SLAVE ALL '".$channel."';";
                Debug::sql($sql);
                $db_slave->sql_query("RESET SLAVE '".$channel."' ALL;");
            }
        }

        //mysql
        $sql5 = "SELECT COUNT(*) AS channel_exists FROM mysql.slave_master_info WHERE Channel_name = '".$channel."';";


        //mariadb 
        $sql5 = "SHOW ALL SLAVES STATUS";
        $res5 = $db_slave->sql_query($sql5);

        while ($ob5 = $db_slave->sql_fetch_object($res5))
        {
            //Debug::debug($ob5,"Channel Name");
        }

        Debug::debug($mysql_user, "MASTER_USER");

        $sql3 = "CHANGE MASTER '".$channel."' TO 
        MASTER_HOST='".$mysql_server[$id_mysql_server__master]['ip']."', 
        MASTER_PORT=".$mysql_server[$id_mysql_server__master]['port'].", 
        MASTER_USER='".$mysql_user."', 
        MASTER_PASSWORD='".$mysql_password2."', 
        MASTER_LOG_FILE='".$FILE."',
        MASTER_LOG_POS=".$POSITION.";";

        Debug::debug("mysql -h $ip_slave -P ".$mysql_server[$id_mysql_server__master]['port'].""
        ." -u $mysql_user -p$mysql_password2", 'STRING MYSQL MASTER (user replicate)');
        
        
        Debug::debug("mysql -h $ip_slave -P $port_slave -u $login_slave -p$password_slave", 'STRING MYSQL');

        Debug::sql($sql3, "THE CHANNEL !");
        $db_slave->sql_query($sql3);


/*************** */

/*
        $db_slave->sql_query("START SLAVE '".$channel."';");
        $res2 = $db_slave->sql_query("SHOW SLAVE '".$channel."' STATUS");

        while($arr2 = $db_slave->sql_fetch_array($res2, MYSQLI_ASSOC)){
            Debug::debug($arr2, "show slave status");
        }

        $db_slave->sql_query("STOP SLAVE '".$channel."';");
        */



/*************** */


        $sql4 = "CHANGE MASTER '".$channel."' TO master_use_gtid=slave_pos;";

        $db_slave->sql_query($sql4);

        $db_slave->sql_query("START SLAVE '".$channel."';");

        $res2 = $db_slave->sql_query("SHOW SLAVE '".$channel."' STATUS");

        while($arr2 = $db_slave->sql_fetch_array($res2, MYSQLI_ASSOC)){
            Debug::debug($arr2, "show slave status");
        }
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 30; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function install($param)
    {
        Debug::parseDebug($param);
        $pair = $this->generatePair();

        foreach($pair as $ms ) {
            foreach($ms as $master => $slave){
                //Debug::debug($master, 'master');
                //Debug::debug($slave, 'slave');
                $this->configMasterSlave(array($master, $slave));
            }
        }
    }


    public function createSakila($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server= $param[0] ?? 1;

        Debug::debug($id_mysql_server,'id_mysql_server');

        Mysql::execute($id_mysql_server, ROOT.'/test/sakila/sakila-schema.sql');
        Mysql::execute($id_mysql_server, ROOT.'/test/sakila/sakila-data.sql');
    }

    public function createInstanceMariaDB($param)
    {
        $id_docker_server = $param[0];
        $port = $param[1];
        $mariadb_version = $param[2];

    }

    public function dropDemo($param)
    {
        Debug::parseDebug($param);
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.* FROM mysql_server a 
        INNER JOIN `link__mysql_server__tag` b ON a.id = b.id_mysql_server
        INNER JOIN tag c ON b.id_tag = c.id
        WHERE c.name in ('mariadb', 'docker')
        GROUP BY a.id having count(1) = 2;";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $sql = "DELETE FROM mysql_server WHERE id = ".$ob->id.";";
            Debug::sql($sql);
            $db->sql_query($sql);
            
        }

    }

}