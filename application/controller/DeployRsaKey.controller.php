<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use \Glial\Synapse\Controller;
use App\Library\Post;
use Glial\I18n\I18n;
use \App\Library\Debug;
use \App\Library\Ssh;
use App\Library\Chiffrement;

class DeployRsaKey extends Controller
{
    const KEY_WORKER_DEPLOY = 148759;
    const KEY_PUBLIC        = "public_key";
    const KEY_PRIVATE       = "private_key";
    const NB_WORKER         = 10;

    public function index()
    {

        //Debug::$debug = true;


        $this->di['js']->code_javascript('
        $("#check-all").click(function(){
    $("input:checkbox").not(this).prop("checked", this.checked);
});

/* c est dégeu mais FF ne supporte pas l autocomplète */
$("#mysql_server-login_ssh").val("");
$("#mysql_server-password_ssh").val("");


/* pour griser les ligne ou on a deja deployer la clef */
$("#ssh_key-id").change(function() {

id_ssh_key = $(this).val();
$rows = $(".row-server");
$rows.removeClass("pma-grey");
$rows.find("input:checkbox").removeClass("disabled");


$rows.filter(".key-"+id_ssh_key).each(function(i) {

 

  $(this).addClass("pma-grey");
  $(this).find("input:checkbox").addClass("disabled");
  /* .prop("checked", true); */
});
});
');


        $this->title  = '<i style="font-size: 32px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '
            .'<i style="font-size: 16px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['settings'])) {

                Debug::debug($_POST, "POST");




                if (!empty($POST['mysql_server']['login_ssh'])) {
                    $login = $POST['mysql_server']['login_ssh'];
                    if (!empty($POST['mysql_server']['password_ssh'])) {
                        $private = $login."@".$POST['mysql_server']['password_ssh'];
                    }
                    if (!empty($POST['mysql_server']['key_ssh'])) {
                        $private = $login."@".$POST['mysql_server']['key_ssh'];
                    }
                }

                if (!empty($_POST['ssh_key_pv']['id'])) {
                    $private = $_POST['ssh_key_pv']['id'];
                }



                if (!empty($_POST['ssh_key']['id'])) {
                    $public = $_POST['ssh_key']['id'];
                }

                if (empty($public)) {
                    //error
                }


                if (empty($private)) {
                    //error
                }


                $list_id = [];
                foreach ($_POST['link__mysql_server__ssh_key'] as $key => $value) {

                    if (!empty($value["deploy"])) {
                        $list_id[] = $value['id_mysql_server'];
                    }
                }

                $ids = implode(",", $list_id);




                $sql = "SELECT * FROM mysql_server WHERE id IN (".$ids.");";
                $res = $db->sql_query($sql);

                $retour_ok = array();
                $retour_ko = array();

                while ($ob = $db->sql_fetch_object($res)) {


                    /*
                      array($ob->ip,
                      $_POST['mysql_server']['login_ssh'],
                      $_POST['mysql_server']['password_ssh'],
                      $path_puplic_key,
                      $_POST['mysql_server']['key_ssh'])
                     */



                    $this->deploy2(array($ob->ip, $public, $private));

                    if ($this->testConnection($ob->ip, $public) === true) {
                        Debug::debug($_POST['mysql_server']['login_ssh']."@".$ob->ip." : ".'CONNECTION OK !');
                        //Debug::debug($path_private_key);



                        $tmp                                                   = array();
                        $tmp['link__mysql_server__ssh_key']['id_mysql_server'] = $ob->id;
                        $tmp['link__mysql_server__ssh_key']['id_ssh_key']      = $public;
                        $tmp['link__mysql_server__ssh_key']['added_on']        = date("Y-m-d H:i:s");
                        $tmp['link__mysql_server__ssh_key']['active']          = 1;

                        $gg = $db->sql_save($tmp);

                        if ($gg) {
                            $retour_ok[] = $ob->display_name;
                        } else {
                            $retour_ko[] = $ob->display_name;
                        }
                    } else {
                        $retour_ko[] = $ob->display_name;
                    }
                }

                if (!empty($retour_ok)) {
                    $msg   = I18n::getTranslation(implode(',',$retour_ok));
                    $title = I18n::getTranslation(__("The public key has been added on these servers :"));
                    set_flash("success", $title, $msg);
                }

                if (!empty($retour_ko)) {
                    $msg   = I18n::getTranslation(implode(',',$retour_ko));
                    $title = I18n::getTranslation(__("The public key cannot been added to these servers :"));
                    set_flash("error", $title, $msg);
                }



//debug($list_id);
                header("location: ".LINK."DeployRsaKey/index/");
                exit;
            }
        }

        $this->title     = '<i class="fa fa-key" aria-hidden="true"></i> '.__("Deploy RSA key");
        /* $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-cog" style="font-size:14px"></i> '
          .__("Settings").'</a> > <i class="fa fa-server"  style="font-size:14px"></i> '.__("Servers");
         */
        $sql             = "SELECT *, b.libelle as organization, a.id as id_mysql_server,
            count(1) as cpt, group_concat(d.id_ssh_key) as id_ssh_key
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            LEFT JOIN link__mysql_server__ssh_key d ON a.id = d.id_mysql_server
            WHERE 1=1 ".$this->getFilter()." AND d.active=1
            GROUP BY d.`id_mysql_server`
            ";
        $data['servers'] = $db->sql_fetch_yield($sql);


        /*
          $sql = "SELECT `id_mysql_server`,count(1) as cpt, group_concat(active) as active
          FROM `link__mysql_server__ssh_key` GROUP BY `id_mysql_server` order by active ASC;";
          $res = $db->sql_query($sql);

          while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

          $data['mysql_server_ssh'][$ob['id_mysql_server']] = $ob;
          } */


        $sql = "SELECT * FROM ssh_key";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->name;

            $tmp['extra'] = array("data-content" => "<span title='".$ob->type."' class='label label-default'>".strtoupper($ob->type)." ".$ob->bit."</span> [".$ob->name."] <small class='text-muted'>".implode('-',
                    str_split($ob->fingerprint, 4))."</small>");

            $data['key_ssh'][] = $tmp;
        }


        $this->set('data', $data);
    }

//to mutualize
    /**
     *
     * @deprecated
     * @todelete
     *
     */
    private function getFilter()
    {

        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (".implode(',', json_decode($client, true)).")";
        }


        return $where;
    }
    /*
     * 
     *
     *
     *
     */

    private function testConnection($ip, $path_private_key)
    {
        $ssh2 = new SSH2($ip);
        $rsa  = new RSA();


        Debug::debug($path_private_key);

        $priv_key = $this->parseUserKey($path_private_key, self::KEY_PRIVATE);


        Debug::debug($priv_key);

        if ($priv_key !== false) {

            if ($rsa->loadKey($priv_key['key']) === false) {

                if (!$ssh2->login($priv_key['user'], $rsa)) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    public function dropKeySsh($params)
    {
//drop des clef ssh sur tout les serveur ou sur un
//a coder
    }

    public function testkey($param)
    {

        Debug::parseDebug($param);
        $gg = App\Library\Ssh::isValid("/root/.ssh/id_rsa.pub", "[RETURN]");
    }
    /*
     *
     * This function will get one combinaison and test it !
     * The goal is to associate one Key SSH with all Server
     *
     * 
     */

    public function workerDeploy()
    {
        $pid = getmypid();

        $queue = msg_get_queue(self::KEY_WORKER_DEPLOY);

        $msg_type     = NULL;
        $msg          = NULL;
        $max_msg_size = 20480;

        $data        = array();
        $data['pid'] = $pid;

        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {
            $data = json_decode(json_encode($msg), true);

            $this->deploy2($data['server'], $data['key']);
        }
    }

    /**
     * $this->deploy($ob->ip, $_POST['mysql_server']['login_ssh'], $_POST['mysql_server']['password_ssh'],
      $path_puplic_key, $_POST['mysql_server']['key_ssh']);
     *
     *
     * (PmaControl 1.3.8)<br/>
     * Deploy a key public key on remote server SSH
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @from cli or graphical
     * @param string server, under form ip:port or id_mysql_server or id_mysql_server:port, with id_mysql_server from mysql_server, if not requested the port will 22
     * @param string secret, login@key can be private key / file where to find private key, id_ssh_key in PmaControl or password used to connect remotely in SSH
     * @param string public, login@public key, can be public key / file where to find public key to deploy, id_ssh_key in PmaControl / or id from table key_ssh to remote server
     * @return boolean true or error msg, if called directly write error msg with error return else nothing

     * @description create a new MVC and display the output in standard flux
     * @access public
     *
     * @examples :
     * - ./glial DeployRsaKey deploy2 10.10.10.1:22 root@password 'pmacontrol@ecdsa-sha2-nistp256 AAAAE2VjZHNhLXNoYTItbmlzdHAyNTYAAAAIbmlzdHAyNTYAAABBBNMSqI5qyTcoRXZ8LbVhx+uUTtau2rm2VxjKBbgAQu2Ozh2EByFoev+q8j1QaefKvWWFTGBjt8EKL8K5MxhjQgQ= PmaControl'
     * - ./glial DeployRsaKey deploy2 10.10.10.1:22 root@password 'pmacontrol@/path/to/pub/key.pub'
     * - ./glial DeployRsaKey deploy2 5 root@/path/to/private/key 5
     * - ./glial DeployRsaKey deploy2 127.0.0.1:22 root@/root/id_rsa root@/root/id_rsa.pub
     */
    public function deploy2($param)
    {

        Debug::parseDebug($param);


        Debug::debug($param);


        $server  = $param[0];
        $private = $param[1];
        $public  = $param[2];

        //# Get Parameters
        // port SSH par défaut

        $port     = 22;
        $elems    = explode(":", $server);
        $nb_elems = count($elems);

        if ($nb_elems === 2) {
            $port = intval($elems[1]);
        }

        if (ctype_digit(strval($elems[0]))) {
            $db  = $this->di['db']->sql(DB_DEFAULT);
            $sql = "SELECT `ip`, `ssh_port` FROM `mysql_server` where `id`=".intval($elems[0]);
            Debug::sql($sql);
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $ip = $ob->ip;
                if ($nb_elems === 1) {
                    $port = $ob->ssh_port;
                }
            }
        } else {
            $ip = $elems[0];
        }



        $pubkey = $this->parseUserKey($private, self::KEY_PUBLIC);
        $prikey = $this->parseUserKey($public, self::KEY_PRIVATE);


        if (!empty($pubkey['id_ssh_key']) && !empty($prikey['id_ssh_key']) && $prikey['id_ssh_key'] == $pubkey['id_ssh_key']) {
            return "You cannot push the same public / private key";
        }


        //$ip, $port = 22, $user, $password
        if (Ssh::connect($ip, $port, $prikey['user'], $prikey['key']) !== false) {
            Debug::debug($prikey['user']."@".$ip.":".$port." - SSH successfull !");
        } else {
            Debug::debug($prikey['user']."@".$ip.":".$port." - SSH failed ! ");
            return $prikey['user']."@".$ip.":".$port." - SSH failed ! ";
        }


        $tmp_file          = uniqid();
        $file_name_pub_key = TMP.$tmp_file;
        file_put_contents($file_name_pub_key, $pubkey['key']."\n");

        if ($pubkey['user'] === "root") {
            $dest_path = '/root/'.$tmp_file;
        } else {
            $dest_path = '/home/'.$pubkey['user'].'/'.$tmp_file;
        }



        Debug::debug(shell_exec("cat ".$file_name_pub_key));

        Ssh::put($ip, $port, $prikey['user'], $prikey['key'], $file_name_pub_key, $dest_path);

        Debug::debug(Ssh::$ssh->exec("cat ".$dest_path));


        if ($prikey['user'] === "root") {

            $cmd = "mkdir -p /root/.ssh && cat ".$dest_path." >> /root/.ssh/authorized_keys\n";

            Debug::debug($prikey['user']."@".$ip."> ".$cmd, "CMD");
            $res = Ssh::$ssh->exec($cmd, "return");

            Debug::debug($res);
        } else {


            // @todo  this time need to test
            Ssh::$ssh->setTimeout(1);
            $output = Ssh::$ssh->read('/.*@.*[$|#]/');
            Debug::debug($output);

            Ssh::$ssh->write("sudo su -\n");
            Ssh::$ssh->setTimeout(1);

            Ssh::$ssh->write($password."\n");
            $output = Ssh::$ssh->read('/.*@.*[$|#]/');
            Debug::debug($output);

            Ssh::$ssh->write("whoami\n");
            Ssh::$ssh->write("mkdir -p /root/.ssh && cat /home/".$pubkey['user']."/".$tmp_file." >> /root/.ssh/authorized_keys\n");

            $output = Ssh::$ssh->read('/.*@.*[$|#]/');
            Debug::debug($output);
        }


        if (substr($file_name_pub_key, 0, 4) === "/tmp/") {
            unlink($file_name_pub_key);
        }

        return true;
    }

    /**
     *
     * (PmaControl 1.3.9)<br/>
     * Deploy a key public key on remote server SSH
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @from cli 
     * @param string as formated as user@key
     * key can be id_shh_key (from PmaControl)
     * key can be directly an ssh key (private or public)
     * key can be path to a ssh key (private or public)
     * key can be password (in case self::KEY_PRIVATE), sudo will be try if not root
     * @param const self::KEY_PUBLIC or self::KEY_PRIVATE (to know if was are looking for private_key / or public_key
     * @return boolean true or error msg, if called directly write error msg with error return else nothing
     */
    public function parseUserKey($elem, $type_key = self::KEY_PUBLIC)
    {

        if (!in_array($type_key, array(self::KEY_PUBLIC, self::KEY_PRIVATE))) {
            throw new \Exception("PMACTRL-871 : This type of key : '".$type_key."' is not supported");
        }

        $data = array();

        $elems = explode("@", $elem);
        if (count($elems) === 2) {
            $login = $elems[0];
            $key   = $elems[1];
        } else {

            $login = "root";
            $key   = end($elems);
        }

        if (is_numeric($key)) {

            $db  = $this->di['db']->sql(DB_DEFAULT);
            $sql = "SELECT * FROM `ssh_key` WHERE `id` = ".$key;
            Debug::sql($sql);

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $key = Chiffrement::decrypt($ob->{$type_key});

                $data['id_ssh_key'] = $ob->id;
            }
        } elseif (file_exists($key)) {

            Debug::debug($key, "file exist");

            $key = file_get_contents($key);
        }

        $ret = Ssh::isValid($key);
        if ($ret === false) {

            Debug::debug($ret, "ERROR !!!!!!!!!!!");


            if (self::KEY_PUBLIC === $type_key) {
                return __("This public key is not valid");
            } else {
                return __("This private key is not valid");
            }
        }

        $data['type'] = $type_key;
        $data['info'] = $ret;
        $data['key']  = $key;
        $data['user'] = $login;

        Debug::debug($data);

        return $data;
    }

    public function testParseUserKey()
    {
        Debug::$debug = true;

        $ret = $this->parseUserKey(2, self::KEY_PUBLIC);

        Debug::debug($ret);
    }

    public function queue($param)
    {

        // system de queue
        // démarage des worker
        $pids = array();

        if (msg_queue_exists(self::KEY_WORKER_DEPLOY)) {


            $queue = msg_get_queue(self::KEY_WORKER_DEPLOY);
            msg_remove_queue($queue);
        }

//ajout de tout les messages a traiter :
        $queue = msg_get_queue(self::KEY_WORKER_DEPLOY);

        $php = explode(" ", shell_exec("whereis php"))[1];

        for ($id_worker = 1; $id_worker < self::NB_WORKER; $id_worker++) {

            $cmd = $php." ".GLIAL_INDEX." Ssh workerDeploy >> ".TMP."log/".__CLASS__."-".__FUNCTION__."-".$id_worker.".log 2>&1 & echo $!";
            Debug::debug($cmd);

            $pids[] = shell_exec($cmd);
        }

        Debug::debug("Démarage des ".$id_worker." workers terminé");
        /* */


        $msg_qnum = msg_stat_queue($queue)['msg_qnum'];


        Debug::debug($msg_qnum, "msg dans la liste d'attente : ".$msg_qnum);


        $i  = 0;
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            foreach ($keys as $key) {
                $i++;

                // Create dummy message object
                $object         = new stdclass;
                $object->server = $ob;
                $object->key    = $key;



                //try to add message to queue
                if (msg_send($queue, 1, $object)) {
                    Debug::debug("Added to queue - msg n°".$i);
                    // you can use the msg_stat_queue() function to see queue status
                    //print_r(msg_stat_queue($queue));
                } else {

                    Debug::debug("[ERROR] Could not add message to queue !");
                }
            }
        }


// attend la fin des worker
// on attend d'avoir vider la file d'attente
        do {
            $msg_qnum = msg_stat_queue($queue)['msg_qnum'];


            Debug::debug("Nombre de msg en attente : ".$msg_qnum);
            if ($msg_qnum == 0) {
                break;
            }
            sleep(1);
        } while (true);

// kill des workers !
        foreach ($pids as $pid) {

            $cmd = "kill ".$pid;
            shell_exec($cmd);
        }

        if (!IS_CLI) {
            header("location: ".LINK.__CLASS__."/index");
        }
    }
}