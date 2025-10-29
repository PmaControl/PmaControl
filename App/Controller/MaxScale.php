<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Table;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;
use App\Library\Debug;
use App\Library\Extraction2;
/*

Query made by maxscale :
SELECT * FROM mysql.user; 
SELECT DISTINCT user, host, db FROM mysql.db; 
SELECT DISTINCT * FROM ((SELECT a.user, a.host, a.db FROM mysql.tables_priv AS a) UNION (SELECT a.user, a.host, a.db FROM mysql.columns_priv AS a) ) AS c; 
SELECT DISTINCT a.user, a.host FROM mysql.proxies_priv AS a WHERE a.proxied_host <> '' AND a.proxied_user <> ''; 
SHOW DATABASES; 
SELECT a.user, a.host, a.role FROM mysql.roles_mapping AS a;
*/


/*

1000–1999 → erreurs de paramètre
2000–2999 → erreurs de connexion
3000–3999 → erreurs SQL ou MySQL
4000–4999 → erreurs de logique applicative
5000–5999 → erreurs système ou internes
*/


class MaxScale extends Controller {

    static $version_api = "v1";

    static $maxscale_cache = array();

    public function index() {

        $this->di['js']->addJavascript(array('clipboard.min.js', 'Client/index.js', 'Server/main.js'));
        $this->di['js']->code_javascript('(function() {
            new Clipboard(".copy-button");
        })();');


        $this->title = "MaxScale";

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT a.*, GROUP_CONCAT(b.id_mysql_server) AS id_mysql_servers
        FROM maxscale_server a
        LEFT JOIN maxscale_server__mysql_server b ON a.id = b.id_maxscale_server
        GROUP BY a.id ORDER BY a.display_name";

        $res = $db->sql_query($sql);

        $data = array();
        while ($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC)) {
            $services = array("filters", "listeners", "maxscale", "monitors", "sessions", "servers", "services", "users");

            foreach($services as $command)
            {
                $arr[$command] = array();
                try{

                    $arr[$command] = MaxScale::curl(array($arr['hostname'], $arr['is_ssl'],$arr['port'],$arr['login'],$arr['password'], $command ));
                }
                catch (\Throwable $e) {
                    //echo "⚠️ Erreur inattendue capturée mais ignorée : " . $e->getMessage() . "\n<br>";
                    break;
                }
            }

            $data['maxscale'][] = $arr;
        }


        $data['extra'] = Extraction2::display(array("mysql_available", "mysql_error", "maxscale::maxscale_listeners"));


        $this->set('data', $data);
    }


    public static function curl($param)
    {

        Debug::parseDebug($param);

        $host = $param[0] ?: '127.0.0.1';

        $param[1] ?: '1';

        $protocol = "http";
        if ($param[1] == "1")
        {
            $protocol = "https";
        }
        
        $port = $param[2] ?: '8989';
        $user = $param[3] ?: 'user';
        $password = $param[4] ?: 'pass';
        $command = $param[5] ?: 'servers';


        $lastError = null;



        $url = "{$protocol}://{$host}:{$port}/" . self::$version_api . "/{$command}";

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            if ($protocol === 'https') {
                // Pour les certificats auto-signés, on désactive la vérif
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                throw new \Exception("[PMACONTROL-2002] cURL error when contacting MaxScale at {$url}: {$curlErr}");
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                // Exemple de parsing pour message d'erreur JSON MaxScale
                $errMsg = $response;
                $jsonErr = json_decode($response, true);
                if (isset($jsonErr['errors'][0]['detail'])) {
                    $errMsg = $jsonErr['errors'][0]['detail'];
                }

                throw new \Exception("[PMACONTROL-3001] HTTP error {$httpCode} from MaxScale at {$url}. Response: {$errMsg}");
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("[PMACONTROL-3002] JSON decode error from MaxScale at {$url}: " . json_last_error_msg());
            }

            return $data; // OK → on sort
        } catch (\Exception $e) {
            $lastError = $e;
            Debug::debug("{$protocol} connection failed: " . $e->getMessage(), "MAXSCALE-CURL");
        }
        

        // Si aucune tentative n’a marché
        throw new \Exception("[PMACONTROL-2003] Unable to connect to MaxScale at {$host}:{$port}. Last error: " . $lastError->getMessage());
    }

    public static function getIdMysqlServer($param)
    {
        Debug::parseDebug($param);
        
        $id_maxscale_server = $param[0];


        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT `hostname`,`is_ssl` ,`port`, `login`,`password`  from maxscale_server WHERE `id`=".$id_maxscale_server.";";
        Debug::sql($sql);
        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM)) {
            
            $arr[] = "services";
            $data = self::curl($arr );

            
            //Debug::debug($data, "RESULT");

            foreach ($data['data'] as $service) {
                if (!empty($service['attributes']['listeners'])) {
                    foreach ($service['attributes']['listeners'] as $listener) {
                        $name  = $listener['id'];
                        $ip    = $listener['attributes']['parameters']['address'];
                        $port  = $listener['attributes']['parameters']['port'];
                        
                        Debug::debug("$name => $ip:$port", "EXTRACT");

                        
                        $sql2 = "SELECT `id` FROM `mysql_server` WHERE `ip`='".$ip."' AND `port` =".$port.";";
                        Debug::sql($sql2, "SQL2");
                        $res2 = $db->sql_query($sql2);
                        while($ob = $db->sql_fetch_object($res2))
                        {
                            $sql3 = "UPDATE maxscale_server SET id_mysql_server = ".$ob->id ." WHERE `id`=".$id_maxscale_server." AND id_mysql_server != ".$ob->id .";";
                            Debug::sql($sql3);
                            $db->sql_query($sql3);

                            $sql4 = "UPDATE mysql_server SET is_proxy = 1 WHERE `id`=".$ob->id ." AND is_proxy != 1;";
                            Debug::sql($sql4);
                            $db->sql_query($sql4);

                            return $ob->id;

                        }

                    }
                }
            }

        }

    }


    public static function getVersion($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = $param[0];

        $version = Extraction2::display(array("maxscale::maxscale_maxscale"), array($id_mysql_server));

        if (isset($version[$id_mysql_server]['maxscale_maxscale']['data']['attributes']['version']))
        {
            Debug::debug($version[$id_mysql_server]['maxscale_maxscale']['data']['attributes']['version'], "VERSION");
        }

        


        return $version[$id_mysql_server]['maxscale_maxscale']['data']['attributes']['version'] ?? 'N/A';

    }


    public static function getMainInfo($param)
    {
        Debug::parseDebug($param);

        if (empty($param[0])){
            throw new \Exception("[PMACONTROL-1001] ".__FUNCTION__."() requires 'id_maxscale_server' as the first parameter, none provided.");
        }

        $id_mysql_server = $param[0];

        if (!is_numeric($id_mysql_server)) {
            throw new \Exception("[PMACONTROL-1002] ".__FUNCTION__."(): 'id_maxscale_server' must be numeric, got type '" . gettype($id_mysql_server) . "'.");
        }
        
        $servers = Extraction2::display(array("maxscale::maxscale_servers","maxscale::maxscale_services", 
        "maxscale::maxscale_monitors", "maxscale::maxscale_listeners" ), array($id_mysql_server));

        if (!is_array($servers)) {
            throw new \Exception("[PMACONTROL-1004] ".__FUNCTION__."(): Extraction2::display() did not return an array.");
        }

        if (count($servers) === 0)
        {
            return [];
        }

        $rewrited = self::rewriteJson($servers[$id_mysql_server]);

        Debug::debug($rewrited, "MAXSCALE");

    }

    public static function rewriteJson($servers = array())
    {
        /*
        if (count(self::$maxscale_cache) != 0)
        {
            return self::$maxscale_cache;
        }*/
        
        if (empty($servers['maxscale_servers']))
        {
            Debug::debug($servers, "DEBUGGGG");
            return [];
        }

        $servers_index = [];
        foreach ($servers['maxscale_servers']['data'] as $srv) {
            $servers_index[$srv['id']] = $srv;
        }

        $services_index = [];
        foreach ($servers['maxscale_services']['data'] as $svc) {
            $services_index[$svc['id']] = $svc;
        }

        $result = [];

        // Parcours des listeners
        foreach ($servers['maxscale_listeners']['data'] as $lst) {
            $params = $lst['attributes']['parameters'];
            $addr = $params['address'];
            $port = $params['port'];
            $key = "$addr:$port";

            $listener_info = $lst['attributes'];
            $listener_info['name'] = $lst['id'];

            // Listener → Service
            $svc_name = $lst['relationships']['services']['data'][0]['id'] ?? null;

            $svc_info = [];
            $srv_list = [];
            $mon_info = [];

            if ($svc_name && isset($services_index[$svc_name])) {
                $svc = $services_index[$svc_name];

                $svc['attributes']['name'] = $svc['id'];
                $svc_info = $svc['attributes'];

                unset($svc_info['listeners']);
                unset($svc_info['users']);

                $monitor_linked = [];
                // Service → Servers
                foreach ($svc['relationships']['servers']['data'] as $srv_ref) {
                    $srv_name = $srv_ref['id'];
                    if (isset($servers_index[$srv_name])) {
                        $srv = $servers_index[$srv_name];
                        $srv_params = $srv['attributes']['parameters'];
                        $srv_list[trim($srv_params['address'] . ":" . $srv_params['port'])] = $srv['attributes'];
                        
                        $monitor = [];
                        foreach($srv['relationships']['monitors']['data'] as $monitor)
                        {
                            if ($monitor['type'] === "monitors")
                            {
                                // $srv_params['name']
                                $srv_list[$srv_params['address'] . ":" . $srv_params['port']]['monitors'][] = $monitor['id'];

                                if (! in_array($monitor['id'], $monitor_linked))
                                {
                                    $monitor_linked[] = $monitor['id'];
                                }
                            }
                        }
                    }
                }
            }

            foreach($servers['maxscale_monitors']['data'] as $monitor){
                if (in_array($monitor['id'], $monitor_linked)){
                    $mon_info[$monitor['id']] = $monitor['attributes'];
                    $mon_info[$monitor['id']]['name'] = $monitor['id'];
                }
            }

            //Debug::debug("RESULT FINAL");
            $result[$key] = [
                'listener' => $listener_info,
                'service' => $svc_info,
                'servers' => $srv_list,
                'monitor' => $mon_info,
            ];
        }


        // ajouter les TUNNEL ICI c'est plus simple
        

        self::$maxscale_cache = $result;
        
        //Debug::debug($result);
        // Affichage final
        //echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $result;
    }


    public function servers($param)
    {

        
    }


    public function getErrors($param)
    {
        Debug::parseDebug($param);


        $servers = Extraction2::display(array("maxscale::" ));

        $servers = self::removeArraysDeeperThan($servers, 2);
        Debug::debug($servers);
        
    }



    public static function removeArraysDeeperThan(array $array, int $maxDepth = 4, int $currentDepth = 1): array {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if ($currentDepth >= $maxDepth) {
                // Trop profond → on garde la clé mais on vide la valeur
                $array[$key] = null;
            } else {
                // On continue la récursion
                $array[$key] = self::removeArraysDeeperThan($value, $maxDepth, $currentDepth + 1);
            }
        }
    }
    return $array;
}


}