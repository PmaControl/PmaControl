<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Post;
use App\Library\Extraction;
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



        $wikiUrl   = 'http://monsite.example/dokuwiki';      // URL de base du wiki (sans slash final)
        $user      = 'monlogin';                             // Identifiant DokuWiki
        $pass      = 'monmdp';                               // Mot de passe
        $namespace = 'monespace';                            // Nom d'espace pour le média (dossier sous data/media)
        $pageId    = 'monespace:PageCible';                  // ID de la page cible (incluant namespace)
        $filePath  = '/chemin/local/vers/monimage.svg';      // Chemin vers le fichier SVG local

        // Prépare l'URL de l'API XML-RPC
        $xmlrpcUrl = rtrim($wikiUrl, '/') . '/lib/exe/xmlrpc.php';

        // Fichier temporaire pour stocker le cookie de session
        $cookieJar = tempnam(sys_get_temp_dir(), 'dokuwiki_cookie');

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
        $uploadResp = callXmlRpc($xmlUpload, $xmlrpcUrl, $cookieJar);
        // (On pourrait analyser $uploadResp pour confirmer le téléversement)

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
        $appendResp = callXmlRpc($xmlAppend, $xmlrpcUrl, $cookieJar);
        // (On peut vérifier $appendResp pour s’assurer qu’il n’y a pas d’erreur)

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

}
