<?php

namespace App\Controller;

use App\Library\Available;
use Exception;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Post;
use \App\Library\Extraction;
use \App\Library\Extraction2;
use \App\Library\Transfer;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\FactoryController;
use \App\Library\Mysql;

/**
 * Class responsible for audit workflows.
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
class Audit extends Controller {

    use \App\Library\Filter;

/**
 * Stores `$log_files` for log files.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $log_files = array("/data/www/pmacontrol/data/general.log");
/**
 * Stores `$granted` for granted.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $granted = array();
/**
 * Stores `$denied` for denied.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $denied = array();

/**
 * Retrieve audit state through `getuser`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getuser.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getuser()
 * @example /fr/audit/getuser
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getuser($param) {
        Debug::parseDebug($param);

        foreach ($this->log_files as $file) {
            $handle = fopen($file, "r");
            if ($handle) {

                while (($buffer = fgets($handle, 4096)) !== false) {

                    $output_array = array();

                    preg_match('/Connect\s+(\S+)@(\S+)/', $buffer, $output_array);

                    //preg_match(' /(\S+)@(\S+) as anonymous on\s?(\S+)?/', $buffer, $output_array);
                    //preg_match_all('/(\S+)@(\S+) as anonymous on\s?(\S+)?/', $input_line, $output_array);
                    //preg_match('/(\S+)@(\S+) as anonymous on\s?(\S+)/', $buffer, $output_array);
                    //Debug::debug($output_array);

                    preg_match('/(\S+)@(\S+) (as anonymous\s)?on (\S+)/', $buffer, $output_array3);

                    if (!empty($output_array3[0])) {
                        //Debug::debug($output_array3);
                    }

                    if (count($output_array) > 0) {

                        $buffer2 = fgets($handle, 4096);
                        preg_match('/Access\sdenied for\suser\s\'([\w-]+)\'@\'(\S+)\'\s/', $buffer2, $output_array2);
                        //preg_match('/Access\sdenied for\suser\s\'([\w-]+)\'@\'(\S+)\'\sto\sdatabase\s\'([\w-]+)\'/', $buffer2, $output_array2);

                        if (!empty($output_array3[4])) {
                            $output_array[3] = $output_array3[4];
                        } else {
                            $output_array[3] = "N/A";
                        }

                        if (count($output_array2) > 0) {
                            if (empty($this->denied[$output_array[3]][$output_array[1]][$output_array[2]])) {
                                $this->denied[$output_array[3]][$output_array[1]][$output_array[2]] = 1;

                                Debug::debug("Added to denied : " . $output_array2[0]);
                            } else {
                                $this->denied[$output_array[3]][$output_array[1]][$output_array[2]] ++;
                            }
                        } else {

                            if (empty($this->granted[$output_array[3]][$output_array[1]][$output_array[2]])) {
                                $this->granted[$output_array[3]][$output_array[1]][$output_array[2]] = 1;

                                Debug::debug("Added to granted : " . $output_array[0]);
                            } else {
                                $this->granted[$output_array[3]][$output_array[1]][$output_array[2]] ++;
                            }
                        }
                    }
                    //$this->login_host[] = $output_array[0];
                }
            }

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }

        arsort($this->denied);
        arsort($this->granted);
        Debug::debug($this->granted, "granted");
        $tab = array_keys($this->granted, "granted");

        Debug::debug($tab, "granted");

        Debug::debug($this->denied, "denied");
        $tab2 = array_keys($this->denied, "denied");

        Debug::debug($tab2, "denied");
    }

/**
 * Handle audit state through `general_log`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for general_log.
 * @phpstan-return void
 * @psalm-return void
 * @see self::general_log()
 * @example /fr/audit/general_log
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function general_log($param) {
        Debug::parseDebug($param);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            Debug::debug($_POST);

            if (!empty($_POST['general_log']['activate'])) {
                $get = Post::getToPost();


                $url = LINK .$this->getClass(). "/" . __FUNCTION__ . "/" . $get;

                Debug::debug($url);

                header('location: ' . $url);
            }
        }

        if (!empty($_GET['mysql_server']['id'])) {

            $db = Sgbd::sql(DB_DEFAULT);

            
            $data['logs'] = Extraction::display(array("variables::general_log_file", "variables::datadir"), array($_GET['mysql_server']['id']));

            Debug::debug($data['logs']);
        }
    }

/**
 * Handle audit state through `scp`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for scp.
 * @phpstan-return void
 * @psalm-return void
 * @see self::scp()
 * @example /fr/audit/scp
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function scp($param) {

        Debug::parseDebug($param);

        $_GET['mysql_server']['id'] = 104;

        Debug::debug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        
        $data['logs'] = Extraction::display(array("variables::general_log_file", "variables::datadir"), array($_GET['mysql_server']['id']));

        Debug::debug($data['logs']);

        $general_log_file = $data['logs'][$_GET['mysql_server']['id']]['']['general_log_file'];
        $datadir = $data['logs'][$_GET['mysql_server']['id']]['']['datadir'];

        Debug::debug($general_log_file, "general_log_file");

        $dst = ROOT . "/data/general.log";
        Debug::debug($dst, "dst");

        Transfer::setDb($db);
        $info = Transfer::getFileFromMysql($_GET['mysql_server']['id'], $general_log_file, $dst);

        Debug::debug($info);
    }


/**
 * Handle audit state through `export`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for export.
 * @phpstan-return void
 * @psalm-return void
 * @see self::export()
 * @example /fr/audit/export
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function export($param)
    {
        $this->layout_name = false;
        $_GET['ajax'] = true;
    }

/**
 * Handle audit state through `server`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for server.
 * @phpstan-return void
 * @psalm-return void
 * @see self::server()
 * @example /fr/audit/server
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function server($param)
    {
        $this->layout_name = false;
        $_GET['ajax'] = true;

        $db = Sgbd::sql(DB_DEFAULT);
        $id_mysql_server = $param[0];

        $sql ="SELECT * FROM mysql_server where id=".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        $data = [];
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['server'] = $arr;
        }

        // Use wsrep_node_address if available, else ip:port
        $elem = Extraction2::display(array("wsrep_node_address","port"), array($id_mysql_server));
        $wsrep_address = $elem[$id_mysql_server]['wsrep_node_address'] ?? '';
        $port = $elem[$id_mysql_server]['port'] ?? '3306';

        $current_address = $data['server']['ip'] . ":" . $data['server']['port'];

        if (!empty($wsrep_address)) {
            $data['server']['address'] = $wsrep_address.":".$port;
        } else {
            $data['server']['address'] = $current_address;
        }


        /*
        // get cluster : 
        $sub_query = "select max(z.id) from dot3_cluster__mysql_server z where z.id_mysql_server=".$id_mysql_server;

        $sql2 = "SELECT c.svg FROM dot3_cluster__mysql_server a
        INNER JOIN dot3_cluster b ON a.id_dot3_cluster = b.id
        INNER JOIN dot3_graph c ON b.id_dot3_graph = c.id
        WHERE a.id_mysql_server = ".$id_mysql_server." AND a.id in (".$sub_query.");";


        $res2 = $db->sql_query($sql2);
        
        //$this->logger->warning($db->sql_num_rows($res));
        
        while ($ob2 = $db->sql_fetch_object($res2)) {
            $data['svg'] = $ob2->svg; 
        }*/

        $this->set('data', $data);
    }



/**
 * Handle audit state through `upload`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for upload.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::upload()
 * @example /fr/audit/upload
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function upload($param)
    {

        Debug::parseDebug($param);

        $wikiUrl   = DOKUWIKI_URL;      // URL de base du wiki (sans slash final)
        $user      = DOKUWIKI_LOGIN;                             // Identifiant DokuWiki
        $pass      = DOKUWIKI_PASSWORD;                               // Mot de passe
        $namespace = 'svg';                            // Nom d'espace pour le média (dossier sous data/media)
        $pageId    = 'pmacontrol:audit';                  // ID de la page cible (incluant namespace)
        $filePath  = $param[0];      // Chemin vers le fichier SVG local


        if (! file_exists($filePath))
        {
            throw new Exception("File not exist", 90);
        }

        // Prépare l'URL de l'API XML-RPC
        $xmlrpcUrl = rtrim($wikiUrl, '/') . '/lib/exe/xmlrpc.php';

        Debug::debug($xmlrpcUrl, "xmlrpcUrl");
        // Fichier temporaire pour stocker le cookie de session
        $cookieJar = tempnam(sys_get_temp_dir(), 'dokuwiki_cookie');
        Debug::debug($cookieJar, "xmlrpcUrl");
        // Fonction utilitaire pour envoyer une requête XML-RPC


        // 1. Authentification (dokuwiki.login)
        $xmlLogin  = '<?xml version="1.0"?>'
                . '<methodCall>'
                .   '<methodName>dokuwiki.login</methodName>'
                .   '<params>'
                .     '<param><value><string>' . htmlspecialchars($user) . '</string></value></param>'
                .     '<param><value><string>' . htmlspecialchars($pass) . '</string></value></param>'
                .   '</params>'
                . '</methodCall>';
        $loginResp = $this->callXmlRpc($xmlLogin, $xmlrpcUrl, $cookieJar);

        Debug::debug($loginResp, "loginResp");
        // (On pourrait analyser $loginResp pour vérifier la réussite)

        // 2. Téléversement du fichier SVG (wiki.putAttachment)
        $filename = basename($filePath);
        $fileData = file_get_contents($filePath);
        $base64   = base64_encode($fileData);
        $fileId   = $namespace . ':' . $filename;

        // Construction de la requête XML-RPC pour putAttachment
        $xmlUpload  = '<?xml version="1.0"?>'
                    . '<methodCall>'
                    .   '<methodName>wiki.putAttachment</methodName>'
                    .   '<params>'
                    .     '<param><value><string>' . htmlspecialchars($fileId) . '</string></value></param>'
                    .     '<param><value><base64>' . $base64 . '</base64></value></param>'
                    .     '<param><value><struct>'
                    .       '<member><name>ow</name><value><boolean>1</boolean></value></member>'
                    .     '</struct></value></param>'
                    .   '</params>'
                    . '</methodCall>';
        $uploadResp = $this->callXmlRpc($xmlUpload, $xmlrpcUrl, $cookieJar);
        // (On pourrait analyser $uploadResp pour confirmer le téléversement)

        Debug::debug($uploadResp, "uploadResp");

        // 3. Mise à jour de la page (wiki.appendPage)
        $imageTag = '{{:' . $namespace . ':' . $filename . '}}';  // syntaxe DokuWiki pour afficher l'image
        $xmlAppend  = '<?xml version="1.0"?>'
                    . '<methodCall>'
                    .   '<methodName>wiki.appendPage</methodName>'
                    .   '<params>'
                    .     '<param><value><string>' . htmlspecialchars($pageId) . '</string></value></param>'
                    .     '<param><value><string>' . htmlspecialchars($imageTag . "\n") . '</string></value></param>'
                    .     '<param><value><struct>'
                    .       '<member><name>sum</name><value><string>Ajout de l\'image ' . htmlspecialchars($filename) . '</string></value></member>'
                    .       '<member><name>minor</name><value><boolean>1</boolean></value></member>'
                    .     '</struct></value></param>'
                    .   '</params>'
                    . '</methodCall>';
        //$appendResp = $this->callXmlRpc($xmlAppend, $xmlrpcUrl, $cookieJar);
        // (On peut vérifier $appendResp pour s’assurer qu’il n’y a pas d’erreur)

        //Debug::debug($appendResp, "appendResp");

        
    }

/**
 * Handle audit state through `callXmlRpc`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $xmlContent Input value for `xmlContent`.
 * @phpstan-param mixed $xmlContent
 * @psalm-param mixed $xmlContent
 * @param mixed $xmlrpcUrl Input value for `xmlrpcUrl`.
 * @phpstan-param mixed $xmlrpcUrl
 * @psalm-param mixed $xmlrpcUrl
 * @param mixed $cookieJar Input value for `cookieJar`.
 * @phpstan-param mixed $cookieJar
 * @psalm-param mixed $cookieJar
 * @return mixed Returned value for callXmlRpc.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::callXmlRpc()
 * @example /fr/audit/callXmlRpc
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function callXmlRpc($xmlContent, $xmlrpcUrl, $cookieJar) {
        $ch = curl_init($xmlrpcUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $xmlContent,
            CURLOPT_HTTPHEADER => ['Content-Type: text/xml'],
            CURLOPT_COOKIEJAR  => $cookieJar,
            CURLOPT_COOKIEFILE => $cookieJar,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            die('Erreur cURL : ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }





/**
 * Handle audit state through `recommandation`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for recommandation.
 * @phpstan-return void
 * @psalm-return void
 * @see self::recommandation()
 * @example /fr/audit/recommandation
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function recommandation($param)
    {
        /*
        innodb_change_buffering

none
none
innodb_change_buffer_max_size
25
25
innodb_adaptive_flushing_lwm
10.000000
10.000000
innodb_max_dirty_pages_pct

90.000000
90.000000
innodb_autoextend_increment
1000
1000
thread_stack

299008
299008
transaction_prealloc_size

4096
4096
thread_cache_size

100
100
max_connections

100
100
query_cache_type

0
0
query_cache_size

0
0
query_cache_limit

131072
131072
query_cache_min_res_unit

4096
4096
key_buffer_size

134217728
134217728
max_heap_table_size

268435456
268435456
tmp_table_size

268435456
268435456
innodb_buffer_pool_size

2147483648
2147483648
innodb_log_file_size

536870912
536870912
innodb_file_per_table
1
1
sort_buffer_size

33554432
33554432
read_rnd_buffer_size

1048576
1048576
bulk_insert_buffer_size

16777216
16777216
myisam_sort_buffer_size

536870912
536870912
innodb_buffer_pool_chunk_size

33554432
33554432
join_buffer_size

262144
262144
table_open_cache

10000
10000
table_definition_cache

10000
10000
innodb_flush_log_at_trx_commit

2
2
innodb_log_buffer_size

8388608
8388608
innodb_write_io_threads

4
4
innodb_read_io_threads

4
4
innodb_flush_method

O_DIRECT
O_DIRECT
optimizer_search_depth

62
62
innodb_purge_threads

4
4
thread_handling

one-thread-per-connection
one-thread-per-connection
thread_pool_size

8
8
innodb_log_file_buffering
1
1
performance_schema_max_sql_text_length
1024
1024
max_digest_length
1024
1024
performance_schema_max_digest_length
1024
1024
performance_schema_digests_size
5000
5000

        */

    }


/**
 * Handle audit state through `queryCache`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for queryCache.
 * @phpstan-return void
 * @psalm-return void
 * @see self::queryCache()
 * @example /fr/audit/queryCache
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function queryCache($param)
    {
        Debug::parseDebug($param);

        $this->layout_name = false;
        $_GET['ajax'] = true;
        
        $db = Sgbd::sql(DB_DEFAULT);
        $id_mysql_server = $param[0];

        $sql = "select * from global_variable WHERE  variable_name in ('query_cache_type', 'query_cache_size', 'query_cache_limit', 
        'query_cache_min_res_unit','query_cache_wlock_invalidate')  and id_mysql_server =".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res))
        {
            $data['variable'][$ob->variable_name] = $ob->value;
        }
        Debug::debug($data['variable'],"VARIABLES");
        ksort($data['variable']);

        if (strtoupper($data['variable']['query_cache_type'] === "ON"))
        {
            $data['query_cache'] = "ON";
            $elems = Extraction2::display(array("qcache_hits", "qcache_inserts", "qcache_not_cached", "qcache_lowmem_prunes",
        "qcache_free_blocks", "qcache_free_memory", "qcache_queries_in_cache", "qcache_total_blocks", "com_select"), array($id_mysql_server));

            Debug::debug($elems);
            $cache = $elems[$id_mysql_server];

            unset($cache['id_mysql_server']);
            unset($cache['date']);

            ksort($cache);
            
            $data['cache'] = $cache;

            $div_by = $cache['qcache_hits']+$cache['qcache_inserts']+$cache['qcache_not_cached'];

            if ($div_by != 0)
            {
                $data['ratio'] = round($cache['qcache_hits'] / ($cache['qcache_hits']+$cache['qcache_inserts']+$cache['qcache_not_cached'])*100, 2);
            }
            else{
                $data['ratio'] = "N/A";
            }

            $data['ratio_efficacite'] = round($cache['qcache_hits'] / ($cache['com_select'])*100, 2);
            Debug::debug($data);
        }

        $this->set('data', $data);

    }


/**
 * Handle audit state through `all`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for all.
 * @phpstan-return void
 * @psalm-return void
 * @see self::all()
 * @example /fr/audit/all
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function all($param)
    {
        $this->view = false;
        $this->layout_name= false;
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $filter = $param[0];

        $data['servers'] = Extraction2::display(array("mysql_available","is_proxysql"));
        Debug::debug($data['servers']);

        echo "<pre>";
        foreach($data['servers'] as $server) {
            if ($server['mysql_available'] == "1" && $server['is_proxysql'] === "0") {

                //echo $server['id_mysql_server']."\n";
                FactoryController::addNode("audit", "server", array($server['id_mysql_server'], $filter ));
            }
        }
        echo "</pre>";

        $this->set('data', $data);
    }


/**
 * Handle audit state through `cluster`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for cluster.
 * @phpstan-return void
 * @psalm-return void
 * @see self::cluster()
 * @example /fr/audit/cluster
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cluster($param)
    {

        $this->view = false;
        $this->layout_name= false;
        Debug::parseDebug($param);

        $id_dot3_cluster = $param[0];
        $cluster_number = $param[1];
        $id_mysql_servers = $param[2];

        //if (!empy)


        $db = Sgbd::sql(DB_DEFAULT);

        Debug::debug($id_dot3_cluster, "CLSUTER NUMBER");

        $sql2 = "SELECT a.id_mysql_server,b.display_name 
        FROM dot3_cluster__mysql_server a
        INNER JOIN mysql_server b ON a.id_mysql_server = b.id
        WHERE a.id_dot3_cluster=".$id_dot3_cluster." AND a.id_mysql_server IN(".$id_mysql_servers.") AND is_proxy=0;";
        
        $server_name = [];
        $id_mysql_servers = [];

        $res2 = $db->sql_query($sql2);
        while($ob2 = $db->sql_fetch_object($res2))
        {
            //Debug::debug($ob);
            $server_name[] = $ob2->display_name;
            $id_mysql_servers[] = $ob2->id_mysql_server;
        }

        //Debug::debug($server_name, "SERVER NAME");
        //Debug::debug($id_mysql_servers, "id_mysql_server NAME");

        $common_parts = $this->get_common_parts($server_name);

        if (empty($common_parts['prefix_commun'])) {
            $cluster_name_brut = implode('-',$common_parts['segments_communs'] );
        }
        else {
            $cluster_name_brut = $common_parts['prefix_commun'];
        }

        $cluster_name = $this->retirerChiffreEtSeparateurFin($cluster_name_brut);

        $sql = "SELECT b.* FROM dot3_cluster a
        INNER JOIN dot3_graph b ON a.id_dot3_graph = b.id
        WHERE a.id=".$id_dot3_cluster." LIMIT 1;";

        $res = $db->sql_query($sql);


        while($ob = $db->sql_fetch_object($res))
        {

            //Debug::debug($ob);
            echo "\n===== Cluster : ".$cluster_name." =====\n";

            echo "\n==== Architecture ====\n";

            $prefix = "svg";
            $file_svg = $prefix."_".$ob->id.".svg";
            $path_svg = TMP."dot/".$file_svg;
            file_put_contents($path_svg, $ob->svg);
            $this->upload(array($path_svg));

            echo "\n{{ :".$prefix.":".$file_svg." |}}\n";


            if (count($id_mysql_servers) > 1)
            {
                $this->getConfig(array(implode(",",$id_mysql_servers)));
            }
            
            // table without PK
            $this->getTableWithoutFk(array(implode(",",$id_mysql_servers)));
            
            $this->getAutoInc(array(implode(",",$id_mysql_servers)));

            $this->getIndex(array(implode(",",$id_mysql_servers)));
            //unlink($path_svg);
            $this->server(array($ob->id ));

            //debug($id_mysql_servers);

            foreach($id_mysql_servers as $id_mysql_server)
            {

                //debug(Available::$mysql_available);
                //debug($id_mysql_server);
                //echo "&&&&&&&&&&&&&&&&&&&&&&&&&&&& : ". (int)Available::getMySQL($id_mysql_server)."\n";

                if (Available::getMySQL($id_mysql_server) === false){
                    echo "\n<note danger>Le serveur ".$id_mysql_server." est OFFLINE maitenant !</note>\n";
                }
                else{
                    FactoryController::addNode("audit", "server", array($id_mysql_server ));
                }
            }   
        }


    }


/**
 * Handle audit state through `byCluster`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for byCluster.
 * @phpstan-return void
 * @psalm-return void
 * @see self::byCluster()
 * @example /fr/audit/byCluster
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function byCluster($param)
    {
        $this->view = false;
        $this->layout_name= false;
        Debug::parseDebug($param);

        $where = "";
        if (! empty($param[0]))
        {
            $id_mysql_servers = $param[0];
            $where = "WHERE d.id_mysql_server in (".$id_mysql_servers.")";
        }
        

        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "WITH LatestDot3Information AS (
            SELECT MAX(id_dot3_information) AS max_id_dot3_information
            FROM dot3_cluster
        )
        SELECT a.id, a.id_dot3_information, GROUP_CONCAT(d.id_mysql_server) as id_mysql_servers, a.id as id_dot3_cluster
        FROM dot3_cluster a INNER JOIN LatestDot3Information b ON a.id_dot3_information = b.max_id_dot3_information-1 
        INNER JOIN dot3_graph c ON c.id = a.id_dot3_graph
        INNER JOIN dot3_cluster__mysql_server d ON d.id_dot3_cluster = a.id $where
        GROUP BY a.id
        ORDER BY c.height DESC, c.width desc;";


        Debug::sql($sql);
      

        $res = $db->sql_query($sql);

        $i = 1;
        while($ob = $db->sql_fetch_object($res))
        {

            //echo "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n";
            //echo "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n";


            $this->cluster(array($ob->id_dot3_cluster, $i, $ob->id_mysql_servers ));
            //FactoryController::addNode("audit", "cluster", array($ob->id, $i ));
            $i++;

            //echo "############################################################################\n";
            //echo "############################################################################\n";
        }

        Debug::debug(Available::$engines, "ENGINES");
        Debug::debug(Available::$performance_schema, "PS");
    }


/**
 * Retrieve audit state through `get_common_parts`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $servers Input value for `servers`.
 * @phpstan-param array $servers
 * @psalm-param array $servers
 * @return array Returned value for get_common_parts.
 * @phpstan-return array
 * @psalm-return array
 * @see self::get_common_parts()
 * @example /fr/audit/get_common_parts
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function get_common_parts(array $servers): array {
        if (empty($servers)) return [];

        // 1. Trouver le préfixe commun entre toutes les chaînes
        $prefix = $servers[0];
        foreach ($servers as $server) {
            $i = 0;
            while ($i < strlen($prefix) && $i < strlen($server) && $prefix[$i] === $server[$i]) {
                $i++;
            }
            $prefix = substr($prefix, 0, $i);
            if ($prefix === '') break;
        }

        // 2. Découper avec délimiteurs - et .
        $split_servers = array_map(fn($name) => preg_split('/[-.]/', $name), $servers);
        $common_segments = $split_servers[0];

        foreach ($split_servers as $segments) {
            $common_segments = array_intersect($common_segments, $segments);
        }

        // 3. Si aucun préfixe ni segment commun trouvé, fallback logique jusqu’au premier chiffre
        if (empty($prefix) && empty($common_segments)) {
            if (preg_match('/^([^\d]+)/', $servers[0], $matches)) {
                $prefix = $matches[1]; // tout ce qui est avant le premier chiffre
            }
        }

        return [
            'prefix_commun' => $prefix,
            'segments_communs' => array_values(array_unique($common_segments)),
        ];
    }

/**
 * Handle audit state through `retirerChiffreEtSeparateurFin`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $chaine Input value for `chaine`.
 * @phpstan-param mixed $chaine
 * @psalm-param mixed $chaine
 * @return mixed Returned value for retirerChiffreEtSeparateurFin.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::retirerChiffreEtSeparateurFin()
 * @example /fr/audit/retirerChiffreEtSeparateurFin
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function retirerChiffreEtSeparateurFin($chaine) {
        $gg =  preg_replace('/\d{1}$/', '', $chaine);
        return trim($gg, "-");
    }

    /*
        test
        // Exemple d'utilisation
        $servers1 = [
            'dc1-prd-mysql-01.ipex.be',
            'dc1-prd-mysql-02.ipex.be',
            'dc1-prd-mysql-03.ipex.be',
            'dc2-prd-mysql-04.ipex.be',
            'dc2-prd-mysql-05.ipex.be'
        ];

        $servers2 = [
            'ipex-galera-11',
            'ipex-galera-12',
            'ipex-galera-13',
            'ipex-galera-15',
            'ipex-galera-14'
        ];

        $servers3 = [
            'waylon1bdd',
            'waylon2'
        ];

        $servers4 = [
            'lenny1bdd',
            'secoursabdd'
        ];

    */


    function getQueryOnCluster($param)
    {
        $servers = $param[0];
        $query = $param[1];
        $require = explode(",",$param[2]);
        $type = $param[3]; // MERGE / INTERSECT / ADD

        Debug::debug($require,"GGGG");

        $id_mysql_servers = explode(",", $servers);
        
        $data = array();
        
        foreach($id_mysql_servers as $id_mysql_server)
        {
            if (Available::getMySQL($id_mysql_server) === false)
            {
                //echo "SERVER $id_mysql_server OFFLINE\n";
                continue;
            }

            if (in_array('innodb', $require))
            {
                if (Available::hasEngine($id_mysql_server, "INNODB") === false) {
                    continue;
                }
            }

            if (in_array('performance_schema', $require))
            {
                if (Available::getPS($id_mysql_server) === false) {
                    continue;
                }
            }


            $db = Mysql::getDbLink($id_mysql_server, "mysqlsys".$id_mysql_server);
            $sql = $query;
            $res = $db->sql_query($sql);

            $data[$id_mysql_server] = array();
            while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
            {
                $data[$id_mysql_server][] = $arr;
            }

            /************ */
        }

        $data = self::aggregate(array($data));

        return $data;
        

        //Debug::debug($data);
    }


/**
 * Retrieve audit state through `getTableWithoutFk`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getTableWithoutFk.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getTableWithoutFk()
 * @example /fr/audit/getTableWithoutFk
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getTableWithoutFk($param)
    {

        Debug::debug($param, "CHECK");


        $param[1] = "SELECT 
    t.table_schema,
    t.table_name,
    t.table_rows,
    t.engine
FROM 
    information_schema.tables t
LEFT JOIN (
    SELECT 
        table_schema,
        table_name
    FROM 
        information_schema.table_constraints
    WHERE 
        constraint_type = 'PRIMARY KEY'
) pk 
ON t.table_schema = pk.table_schema AND t.table_name = pk.table_name
WHERE 
    pk.table_name IS NULL
    AND t.table_type = 'BASE TABLE'
    AND t.table_schema NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys') ORDER BY 1,2 LIMIT 50;";

        //$param[1] = "SELECT * from sys.sys_config order by variable;";

        $param[2] = "";
        $param[3] = "merge";


        Debug::debug($param, "CHECK");

        $data = $this->getQueryOnCluster($param);

        if (! empty($data)) {

            echo "\n==== Liste des tables sans clefs primaire====\n";
            $this->displayTable(array($data));
        }
        

    }


/**
 * Handle audit state through `displayTable`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for displayTable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::displayTable()
 * @example /fr/audit/displayTable
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function displayTable($param)
    {

        $data = $param[0];
        if (empty($data)) {
            return true;
        }

        $number_of_server = count($data[0]['present_on']) + count($data[0]['missing_from']);
        


        $keys = array_keys($data[0]['row']);

        echo "\n^ ";
        foreach($keys as $key){
            echo "<nowiki>$key</nowiki> ^";
        }
        echo "present_on ^\n";

        $i = 1;
        
        foreach($data as $elem)
        {
            echo "| ";
            foreach($elem['row'] as $value) {
                echo "<nowiki>".$value."</nowiki> |";
            }
            echo "<nowiki>".implode(",",$elem['present_on'])."</nowiki> |\n";
        }
        echo "\n";

        
    }



/**
 * Handle audit state through `aggregate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for aggregate.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::aggregate()
 * @example /fr/audit/aggregate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function aggregate($param)
    {
        $data = $param[0];

        if (empty($data))
        {
            return $data;
        }

        $allServers = array_keys($data);
        $normalizedRows = [];

        // Construction d'un index basé sur toutes les colonnes
        foreach ($data as $serverId => $rows) {
            foreach ($rows as $row) {
                //ksort($row); // Trie les clés pour cohérence
                $key = json_encode($row); // Génère une clé unique pour cette combinaison de colonnes/valeurs

                if (!isset($normalizedRows[$key])) {
                    $normalizedRows[$key] = [
                        'row' => $row,
                        'present_on' => [],
                    ];
                }
                $normalizedRows[$key]['present_on'][] = $serverId;
            }
        }

        // Compléter le tableau avec les serveurs manquants
        $result = [];

        foreach ($normalizedRows as $entry) {
            $present = array_unique($entry['present_on']);
            $missing = array_diff($allServers, $present);

            $result[] = [
                'row' => $entry['row'],
                'present_on' => $present,
                'missing_from' => array_values($missing),
            ];
        }

        // Affichage ou retour
        //print_r($result);
    
        return $result;
    }


/**
 * Retrieve audit state through `getAutoInc`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getAutoInc.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getAutoInc()
 * @example /fr/audit/getAutoInc
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getAutoInc($param)
    {

        Debug::debug($param, "CHECK");


        $param[1] = "SELECT * FROM `sys`.`schema_auto_increment_columns` WHERE auto_increment_ratio > 0.5 ORDER BY auto_increment_ratio DESC";
        $param[2] = "innodb";
        //$param[2] = "";
        $param[3] = "merge";


        Debug::debug($param, "CHECK");

        $data = $this->getQueryOnCluster($param);

        if (! empty($data)) {

            echo "\n==== Auto Increment ====\n";

echo "Lorsque la colonne ''AUTO_INCREMENT'' atteint 100% de sa capacité (valeur maximale), le serveur ne peut plus attribuer de nouveaux identifiants : toute insertion de ligne provoque alors une erreur. En pratique, MySQL/MariaDB renvoie des messages tels que ''ERROR 1062 (23000): Duplicate entry ‘2147483647’ for key ‘PRIMARY’ ou ERROR 1467 (HY000): Failed to read auto-increment value from storage engine''. Ces erreurs indiquent que la prochaine valeur d’auto-incrément dépasserait la limite du type (par exemple 2 147 483 647 pour un ''INT'' signé) et est considérée comme un doublon ou invalide. En conséquence, les requêtes ''INSERT'' sur la table échouent, et l’application se retrouve dans l’incapacité d’ajouter de nouvelles données tant que le problème n’est pas résolu. Pour corriger ce problème, il faut augmenter la plage du champ incrémenté en changeant son type. Par exemple, convertir un ''INT SIGNED'' en ''INT UNSIGNED'' double la capacité disponible (car on supprime l’usage des valeurs négatives)
On peut aussi passer à ''BIGINT'' (de préférence ''BIGINT UNSIGNED'') pour étendre encore plus largement la limite. Le manuel MySQL rappelle d’ailleurs qu’on doit utiliser ''UNSIGNED'' pour élargir la plage d’un champ auto-incrément dès que le type initial atteint sa limite
Sur une table ''InnoDB'' volumineuse, un ''ALTER TABLE'' traditionnel serait long et bloquerait l’accès. On utilise alors l’outil ''pt-online-schema-change'' (Percona Toolkit), qui ne fonctionne qu’avec les tables InnoDB : il crée en arrière-plan une copie de la table modifiée (colonne convertie), copie les données par petits lots en maintenant des triggers pour synchroniser les changements, puis effectue un RENAME TABLE atomique pour basculer. Cette procédure en ligne permet à la table de rester accessible (en lecture, et sans doute en écriture via les triggers) durant la migration, minimisant ainsi les interruptions de service.";

echo "\nOn affiche ici uniquement les valeurs dépassant les 50% de remplissage :\n";
            $this->displayTable(array($data));
        }
        

    }


/**
 * Retrieve audit state through `getIndex`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getIndex.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getIndex()
 * @example /fr/audit/getIndex
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getIndex($param)
    {

        echo "\n==== Index ====\n";
        echo "\n\n";

        $this->getRedundantIndex($param);
        $this->getRedundantAlter($param);
        
        $this->getUnusedIndex($param);
        
    }

/**
 * Retrieve audit state through `getRedundantIndex`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getRedundantIndex.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getRedundantIndex()
 * @example /fr/audit/getRedundantIndex
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getRedundantIndex($param)
    {

        Debug::debug($param, "CHECK");


        $param[1] = "SELECT table_schema,table_name, redundant_index_name,redundant_index_name,dominant_index_name, dominant_index_columns
        FROM `sys`.`schema_redundant_indexes` ORDER BY table_schema,table_name LIMIT 50";
        $param[2] = "innodb,performance_schema";
        //$param[2] = "";
        $param[3] = "merge";


        Debug::debug($param, "CHECK");

        $data = $this->getQueryOnCluster($param);

        if (! empty($data)) {

            echo "=== Analyse des index redondants ===\n";
            echo "\n\n";


            echo "Un index est considéré comme redondant lorsqu’un autre index existant couvre déjà les mêmes colonnes dans le même ordre, voire plus.

Supprimer ces index redondants permet de :

* Réduire la taille des fichiers d’index sur disque,
* Accélérer les opérations de modification (INSERT, UPDATE, DELETE), car chaque index ajouté implique une surcharge,
* Limiter la consommation mémoire (notamment pour les caches d’index),
* Faciliter la maintenance de la base en réduisant la complexité du schéma.

De plus, éviter les index trop larges ou inutiles contribue à une meilleure performance globale et à un temps d’analyse de requêtes plus court, tout en minimisant l’empreinte de stockage.
";

            $this->displayTable(array($data));
        }
    }




/**
 * Retrieve audit state through `getRedundantAlter`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getRedundantAlter.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getRedundantAlter()
 * @example /fr/audit/getRedundantAlter
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getRedundantAlter($param)
    {

        Debug::debug($param, "CHECK");


        $param[1] = "SELECT sql_drop_index
        FROM `sys`.`schema_redundant_indexes` ORDER BY table_schema,table_name";
        $param[2] = "innodb,performance_schema";
        //$param[2] = "";
        $param[3] = "merge";


        Debug::debug($param, "CHECK");

        $data = $this->getQueryOnCluster($param);

        if (! empty($data)) {

            echo "Liste des requêtes pour dropper les index redondants : ";

            $this->displayTable(array($data));
        }
    }

/**
 * Retrieve audit state through `getUnusedIndex`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getUnusedIndex.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getUnusedIndex()
 * @example /fr/audit/getUnusedIndex
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getUnusedIndex($param)
    {

        Debug::debug($param, "CHECK");


        $param[1] = "SELECT object_schema, object_name, index_name  
        FROM `sys`.`schema_unused_indexes` ORDER BY object_schema,object_name,index_name LIMIT 50";
        $param[2] = "innodb,performance_schema";
        //$param[2] = "";
        $param[3] = "merge";


        Debug::debug($param, "CHECK");

        $data = $this->getQueryOnCluster($param);

        if (! empty($data)) {

            echo "=== Analyse des index non utilisé ===\n";
            echo "\n\n";


            echo "
Le tableau schema_unused_indexes du schéma sys identifie les index qui existent mais ne sont jamais utilisés par le moteur MariaDB (ni dans des lectures, ni dans des plans d'exécution optimisés).

Conserver de tels index a plusieurs inconvénients :

* Occupation inutile de l’espace disque,
* Surcharge lors des écritures (chaque modification d’une table met aussi à jour tous ses index),
* Consommation mémoire excessive (s’ils sont chargés en cache sans utilité réelle),
* Complexité accrue du schéma, ce qui nuit à la lisibilité et à la maintenance.

La suppression des index inutilisés permet donc d’améliorer les performances, de réduire les coûts en ressources (I/O, RAM) et de simplifier l’administration de la base.

⚠️  Remarque importante : les données de ce tableau sont fiables uniquement si le serveur tourne depuis un moment avec la surveillance des index activée (user/statistics), sans redémarrage récent, et avec une charge représentative de l’activité réelle. Les données sont remise à zéro après chaque redemarrage du serveur.

";

            $this->displayTable(array($data));
        }
        

    }


/**
 * Retrieve audit state through `getConfig`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getConfig.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getConfig()
 * @example /fr/audit/getConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getConfig($param)
    {
        echo "==== Difference de configuration entre les serveurs ====\n";

        $id_mysql_servers = explode(',',$param[0]);
        
        Debug::debug($id_mysql_servers, 'wfdgwdf');

        $sql = "SELECT
            variable_name,";

        $inter = array();
        foreach($id_mysql_servers as $id_mysql_server)
        {
            Debug::debug($id_mysql_server, 'id_mysql_server');
            $inter[] = " LEFT(MAX(CASE WHEN id_mysql_server = ".$id_mysql_server." THEN value END),50) AS value_server".$id_mysql_server." ";
        }

        $sql .= implode(',', $inter);
            
        $sql .= " FROM global_variable
        WHERE id_mysql_server IN (".$param[0].")
        GROUP BY variable_name
        HAVING COUNT(DISTINCT value) > 1
        ORDER BY variable_name;";

        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();
        $res = $db->sql_query($sql);
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $tmp = array();
            $tmp['row'] = $arr;
            $tmp['present_on'] = $id_mysql_servers;
            $tmp['missing_from'] = array();
            
            $data[]= $tmp;
        }

        $this->displayTable(array($data));

    }

/*
    public getClusterId($param)
    {
        $sql = "WITH LatestDot3Information AS (
            SELECT MAX(id_dot3_information) AS max_id_dot3_information
            FROM dot3_cluster
        )
        SELECT a.id, a.id_dot3_information, GROUP_CONCAT(d.id_mysql_server) as id_mysql_servers, a.id as id_dot3_cluster
        FROM dot3_cluster a INNER JOIN LatestDot3Information b ON a.id_dot3_information = b.max_id_dot3_information-1 
        INNER JOIN dot3_graph c ON c.id = a.id_dot3_graph
        INNER JOIN dot3_cluster__mysql_server d ON d.id_dot3_cluster = a.id
        GROUP BY a.id
        ORDER BY  c.height DESC, c.width desc;";


    }


############## STORAGE ENGINE
SELECT 
    ENGINE,
    ROUND(SUM(DATA_LENGTH) / (1024 * 1024 * 1024), 2) AS data_gb,
    ROUND(SUM(INDEX_LENGTH) / (1024 * 1024 * 1024), 2) AS index_gb,
    ROUND(SUM(DATA_FREE) / (1024 * 1024 * 1024), 2) AS free_gb,
    GROUP_CONCAT(DISTINCT TABLE_SCHEMA ORDER BY TABLE_SCHEMA SEPARATOR ', ') AS `databases`
FROM information_schema.TABLES
WHERE TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')
GROUP BY ENGINE
ORDER BY data_gb DESC;
        */
}

