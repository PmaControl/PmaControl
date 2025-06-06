<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Post;
use App\Library\Extraction;
use App\Library\Extraction2;
use App\Library\Transfer;
use \Glial\Sgbd\Sgbd;

class Audit extends Controller {

    use \App\Library\Filter;

    var $log_files = array("/data/www/pmacontrol/data/general.log");
    var $granted = array();
    var $denied = array();

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


    public function export($param)
    {
        $this->layout_name = false;
        $_GET['ajax'] = true;
        
        
    }



    public function server($param)
    {
        $this->layout_name = false;
        $_GET['ajax'] = true;
        
        $db = Sgbd::sql(DB_DEFAULT);
        $id_mysql_server = $param[0];

        $sql ="SELECT * FROM mysql_server where id=".$id_mysql_server.";";

        $res = $db->sql_query($sql);

        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data['server'] = $arr;
        }

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
        }

        


        $this->set('data', $data);
    }



    public function upload($param)
    {

        Debug::parseDebug($param);

        $wikiUrl   = DOKUWIKI_URL;      // URL de base du wiki (sans slash final)
        $user      = DOKUWIKI_LOGIN;                             // Identifiant DokuWiki
        $pass      = DOKUWIKI_PASSWORD;                               // Mot de passe
        $namespace = 'audit';                            // Nom d'espace pour le média (dossier sous data/media)
        $pageId    = 'pmacontrol:audit';                  // ID de la page cible (incluant namespace)
        $filePath  = '/srv/www/pmacontrol/App/Webroot/image/icon/maxscale.svg';      // Chemin vers le fichier SVG local

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

        echo "Image téléversée et insérée avec succès.\n";  
    }


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


    public function getCluster($param)
    {



    }



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


    public function queryCache($param)
    {
        Debug::parseDebug($param);

        $this->layout_name = false;
        $_GET['ajax'] = true;
        
        $db = Sgbd::sql(DB_DEFAULT);
        $id_mysql_server = $param[0];

        $sql = "select * from global_variable WHERE  variable_name in ('query_cache_type', 'query_cache_size', 'query_cache_limit', 
        'query_cache_min_res_unit','query_cache_wlock_invalidate')  and id_mysql_server =1;";

        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res))
        {
            $data['variable'][$ob->variable_name] = $ob->value;

        }
        ksort($data['variable']);

        if (strtoupper($data['variable']['query_cache_type'] === "ON"))
        {
            $data['query_cache'] = "ON";
            $elems = Extraction2::display(array("qcache_hits", "qcache_inserts", "qcache_not_cached", "qcache_lowmem_prunes",
        "qcache_free_blocks", "qcache_free_memory", "qcache_queries_in_cache", "qcache_total_blocks", "com_select"), array($id_mysql_server));

            Debug::debug($elems);
            $cache = $elems[1];

            unset($cache['id_mysql_server']);
            unset($cache['date']);

            ksort($cache);
            
            $data['cache'] = $cache;

            $data['ratio'] = round($cache['qcache_hits'] / ($cache['qcache_hits']+$cache['qcache_inserts']+$cache['qcache_not_cached'])*100, 2);
            $data['ratio_efficacite'] = round($cache['qcache_hits'] / ($cache['com_select'])*100, 2);
            Debug::debug($data);
        }

        $this->set('data', $data);

    }




}
