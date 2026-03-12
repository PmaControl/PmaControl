<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Chiffrement;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Ssh as SshLib;
use App\Library\Post;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \phpseclib3\Crypt\PublicKeyLoader;
use \phpseclib3\Math\BigInteger;
use \phpseclib3\Crypt\RSA;
use \phpseclib3\Net\SSH2;

/**
 * Class responsible for ssh workflows.
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
class Ssh extends Controller
{
    const KEY_WORKER_ASSOCIATE = 435665;
    const NB_WORKER            = 10;

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $logger;

/**
 * Handle ssh state through `keys`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for keys.
 * @phpstan-return void
 * @psalm-return void
 * @see self::keys()
 * @example /fr/ssh/keys
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function keys()
    {
        //https://guacamole.apache.org/releases/
        //https://fr.linux-console.net/?p=9161
    }


/**
 * Prepare ssh state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/ssh/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $logger       = new Logger('Daemon');
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

/**
 * Create ssh state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/ssh/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param)
    {
        $this->title = '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>'." ".__("Add a key SSH");

        $filename = $param[0] ?? "";


        if (!IS_CLI) {

            $this->di['js']->code_javascript('
                $(function(){

                var priv = $("#ssh_key-key_priv");
                var pub = $("#ssh_key-key_pub");

                $(".link").click(function(){
                var elem = $(this);
                
                $.ajax({
                    type: "GET",
                    url: elem.attr("href"),
                    dataType:"json",
                    success: function(data) {
                        if(data.key_priv){
                               priv.html(data.key_priv);
                               pub.html(data.key_pub);
                        }
                    }
                });
                return false;
            });
        });');

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                //traitement du UI en post


                if (isset($_POST['ssh_key'])) {

                    $keys = $_POST['ssh_key'];

                    $keys['public_key']  = $_POST['ssh_key']['public_key'];
                    $keys['private_key'] = $_POST['ssh_key']['private_key'];


                    $this->save($keys);
                }
            }
        }
        
        /*
          if (!empty($filename) && file_exists($filename)) {

          $config = $this->parseConfig($filename);

          $all_keys = $config['ssh'];

          foreach ($all_keys as $keys) {
          $to_check = array('name', 'user', 'private_key', 'public_key', 'organization');

          foreach ($to_check as $elem) {
          if (empty($keys[$elem])) {
          throw new \InvalidArgumentException("PMACTRL-030 : ssh.".$elem." is missing in file : ".$filename);
          }
          }

          $this->save($keys);
          }
          } else
         */
    }

/**
 * Update ssh state through `save`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $keys Input value for `keys`.
 * @phpstan-param mixed $keys
 * @psalm-param mixed $keys
 * @return void Returned value for save.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::save()
 * @example /fr/ssh/save
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function save($keys)
    {
        if (!empty($keys)) {
            $fingerprint = \Glial\Cli\Ssh::ssh2_fingerprint($keys['public_key'], 1);

            $db = Sgbd::sql(DB_DEFAULT);


            $_POST['ssh_key']['user'] = $_POST['ssh_key']['user'] ?? '';

            $sql = "SELECT id from ssh_key WHERE fingerprint='".$fingerprint."' and user = '".$_POST['ssh_key']['user']."'";
            $res = $db->sql_query($sql);

            $data            = array();
            $data['ssh_key'] = $keys;

            while ($ob = $db->sql_fetch_object($res)) {
                $data['ssh_key']['id'] = $ob->id;
            }

            if (empty($data['ssh_key']['id'])) {
                $method = "add";
            } else {
                $method = "edit/".$data['ssh_key']['id'];
            }

            preg_match("/ssh\-(\w+)/", $keys['public_key'], $output_array);


            $error = array();

            if (empty($_POST['ssh_key']['name'])) {
                $error[] = __('The name of the key is required !');
            }


            if (empty($_POST['ssh_key']['user'])) {
                $error[] = __('The user of the key is required !');
            }


            //test clef publique
            $ret = SshLib::isValid(str_replace('\n', "\n", $keys['public_key']));
            if ($ret === false) {
                $error[] = __("Your public key is not valid");
            } else {
                $data['ssh_key']['comment'] = $ret['name'];
                $data['ssh_key']['bit']     = $ret['bit'];
                $data['ssh_key']['type']    = $ret['type'];
            }

            // c'est degeu, mais il faut trouver un autre moyen de tester la clef privée ED25519

                //test clef private
            $ret_priv = SshLib::isValid(str_replace('\n', "\n", $keys['private_key']));

            if ($ret_priv === false) {
                $error[] = __("Your private key is not valid");
            }


            if (!empty($error)) {
                $msg   = I18n::getTranslation("<ul><li>".implode("</li><li>", $error)."</li><ul>");
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                //echo Post::getToPost();

                if (empty($_POST['ssh_key']['id'])) {
                    unset($_POST['ssh_key']['id']);
                }

                $_SESSION['ssh_key']['private_key'] = $_POST['ssh_key']['private_key'];
                $_SESSION['ssh_key']['public_key']  = $_POST['ssh_key']['public_key'];

                unset($_POST['ssh_key']['private_key']);
                unset($_POST['ssh_key']['public_key']);

                header('location: '.LINK."ssh/".$method."/".Post::getToPost());
                exit;
            }

            $data['ssh_key']['added_on']    = date('Y-m-d H:i:s');
            $data['ssh_key']['fingerprint'] = $db->sql_real_escape_string($fingerprint);
            $data['ssh_key']['public_key']  = Chiffrement::encrypt(str_replace('\n', "\n", $keys['public_key']));
            $data['ssh_key']['private_key'] = Chiffrement::encrypt(str_replace('\n', "\n", $keys['private_key']));
            $data['ssh_key']['user']        = $keys['user'];

            if (empty($data['ssh_key']['id'])) {
                unset($data['ssh_key']['id']);
            }

            $res = $db->sql_save($data);
            if (!$res) {
                debug($data);

                throw new \Exception("PMACTRL-031 : Impossible to save ssh key");
            }


            if (empty($data['ssh_key']['id'])) {
                $word = "added";
            } else {
                $word = "updated";
            }


            $msg   = I18n::getTranslation(__("Your private key was ".$word));
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);

            header('location: '.LINK."ssh/index/");
        }
    }

/**
 * Handle ssh state through `parseConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $configFile Input value for `configFile`.
 * @phpstan-param mixed $configFile
 * @psalm-param mixed $configFile
 * @return mixed Returned value for parseConfig.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::parseConfig()
 * @example /fr/ssh/parseConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function parseConfig($configFile)
    {

        if (empty($configFile) || !file_exists($configFile)) {
            throw new \Exception('PMACTRL-255 : The file '.$configFile.' doesn\'t exit !');
        }

        $file   = file_get_contents($configFile);
        $config = json_decode($file, true);


        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $config;
                break;
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $error = 'Unknown error';
                break;
        }


        throw new \Exception("PMACTRL-254 : JSON : ".$error, 80);
    }

/**
 * Render ssh state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/ssh/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {

        $this->title = '<i class="fa fa-key" aria-hidden="true"></i> SSH keys';

        $this->di['js']->addJavascript(array('clipboard.min.js'));

        $this->di['js']->code_javascript('
(function(){
  new Clipboard(".copy-button");
})();

');
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from ssh_key";
        $res = $db->sql_query($sql);


        $data['keys'] = array();
        while ($arr          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $arr['public_key'] = Chiffrement::decrypt($arr['public_key']);

            $data['keys'][] = $arr;
        }


        $sql2            = "SELECT a.*, b.active, c.id as id_key FROM mysql_server a
            INNER JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
            INNER JOIN ssh_key c ON c.id = b.id_ssh_key
            GROUP BY c.id, a.id";
        $res2            = $db->sql_query($sql2);
        $data['servers'] = array();
        while ($arr2            = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $data['servers'][$arr2['id_key']][] = $arr2;
        }


        $data['ssh_supported'] = array('rsa', 'dsa', 'RSA', 'DSA', 'ED25519');

        $this->set('data', $data);
    }

/**
 * Delete ssh state through `delete`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for delete.
 * @phpstan-return void
 * @psalm-return void
 * @see self::delete()
 * @example /fr/ssh/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function delete($param)
    {
        $this->view = false;

        if (!empty($param[0])) {
            $db      = Sgbd::sql(DB_DEFAULT);
            $id_clef = intval($param[0]);

            $sql = "DELETE FROM `ssh_key` WHERE `id`= ".$id_clef;

            $db->sql_query($sql);
        }

        header("location: ".LINK.$this->getClass()."/index");
    }

/**
 * Handle ssh state through `associate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for associate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::associate()
 * @example /fr/ssh/associate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function associate($param)
    {
        $this->view = false;


        Debug::parseDebug($param);


        $id_ssh_key = $param[0];

        $keys = $this->getSshKeys($id_ssh_key);


        Debug::debug($keys, "SSH KEYS");

        $db = Sgbd::sql(DB_DEFAULT);



        $sql = "WITH z as (SELECT a.id
FROM mysql_server a
INNER JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
WHERE `active`=1 AND a.is_vip=0 AND a.is_deleted=0 and b.id_ssh_key in(".$id_ssh_key."))
SELECT b.id,b.ssh_port FROM mysql_server b, ssh_key c
WHERE c.id in (".$id_ssh_key.")
AND b.id NOT IN (select id from z)";




        Debug::sql($sql);
        $res = $db->sql_query($sql);


// old version
        /*
          while ($server = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

          foreach ($keys as $key) {
          $this->tryAssociate($server, $key);
          }
          } */


// system de queue
// démarage des worker
        $pids = array();

        if (msg_queue_exists(self::KEY_WORKER_ASSOCIATE)) {


            $queue = msg_get_queue(self::KEY_WORKER_ASSOCIATE);
            msg_remove_queue($queue);
        }

//ajout de tout les messages a traiter :
        $queue = msg_get_queue(self::KEY_WORKER_ASSOCIATE);

        $php = explode(" ", shell_exec("whereis php"))[1];


        if (Debug::$debug === true) {
            $debug = "--debug ";
        } else {
            $debug = '';
        }



        for ($id_worker = 1; $id_worker <= self::NB_WORKER; $id_worker++) {

            $cmd = $php." ".GLIAL_INDEX." Ssh workerAssociate ".$debug.">> ".TMP."log/".__FUNCTION__."_".$id_worker.".log 2>&1 & echo $!";
            Debug::debug($cmd);

            $pids[] = trim(shell_exec($cmd));
        }

        Debug::debug("Démarage des ".self::NB_WORKER." workers terminé");
        Debug::debug($pids, "PIDS");
        $msg_qnum = msg_stat_queue($queue)['msg_qnum'];


        Debug::debug($msg_qnum, "msg dans la liste d'attente : ".$msg_qnum);


        $i  = 0;
        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            Debug::debug($ob, "ARRAY");


            //foreach ($keys as $key) {
            $i++;

            // Create dummy message object
            $object         = new \stdclass;
            $object->server = $ob['id'];
            $object->key    = $id_ssh_key;


            Debug::debug($object, "MSG");

            //try to add message to queue
            if (msg_send($queue, 1, $object)) {
                Debug::debug("Added to queue - msg n°".$i);
                // you can use the msg_stat_queue() function to see queue status
                //print_r(msg_stat_queue($queue));
            } else {

                Debug::debug("[ERROR] Could not add message to queue !");
            }
            //}
        }


// attend la fin des worker
// on attend d'avoir vider la file d'attente
        do {
            $msg_qnum = msg_stat_queue($queue)['msg_qnum'];


            sleep(2); // la queue est vide mais il faut prendre le temps de traité les msg
            Debug::debug("Nombre de msg en attente : ".$msg_qnum);
            if ($msg_qnum == 0) {
                break;
            }
        } while (true);

// kill des workers !
        foreach ($pids as $pid) {

            $cmd = "kill ".$pid;
            shell_exec($cmd);
        }
        
        if (!IS_CLI) {
            header("location: ".LINK.$this->getClass()."/index");
        }
    }

/**
 * Retrieve ssh state through `getSshKeys`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_ssh_key Input value for `id_ssh_key`.
 * @phpstan-param int $id_ssh_key
 * @psalm-param int $id_ssh_key
 * @return mixed Returned value for getSshKeys.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getSshKeys()
 * @example /fr/ssh/getSshKeys
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getSshKeys($id_ssh_key = "")
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $where = "";

        if (!empty($id_ssh_key)) {
            $where = " WHERE id = ".$id_ssh_key;
        }

        $sql = "SELECT * FROM `ssh_key`".$where;
        $res = $db->sql_query($sql);

        $key = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $key[] = $arr;
        }

        return $key;
    }

/**
 * Handle ssh state through `tryAssociate`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for tryAssociate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::tryAssociate()
 * @example /fr/ssh/tryAssociate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function tryAssociate($param)
    {
        if (! defined('NET_SSH2_LOGGING'))
        {
            define('NET_SSH2_LOGGING', 2);
        }
        Debug::parseDebug($param);


        $id_mysql_server = $param[0];
        $id_ssh_key      = $param[1];

        Debug::debug($id_mysql_server, "SERVER");
        Debug::debug($id_ssh_key, "KEY");




        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT * FROM `mysql_server` WHERE `id`=".$id_mysql_server.";";
        Debug::sql($sql);

        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server = $arr;
        }

        $sql2 = "SELECT * FROM `ssh_key` WHERE `id`=".$id_ssh_key.";";
        Debug::sql($sql2);
        $res2 = $db->sql_query($sql2);
        while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $key = $arr2;
        }


        $ip_port = $server['ip'].':'.$server['ssh_port'];


        $ssh = new SSH2($server['ip'], $server['ssh_port']);
        //$rsa = new RSA();


        //Debug::debug($ssh->host, "IP");
        //Debug::debug($ssh->port, "Port");

        $login_successfull = true;


        Debug::debug(Chiffrement::decrypt($key['private_key']), "PRIVATE KEY");

        $rsa = PublicKeyLoader::load(Chiffrement::decrypt($key['private_key']));

        Debug::debug($rsa);
        /*
        if (RSA::loadFormat('OpenSSH', file_get_contents($key['private_key'])) === false) {
        //if ($rsa->loadKey($key['private_key']) === false) {
            $login_successfull = false;
            Debug::debug($ip_port, "private key loading failed!");

            return false;
        }*/

        Debug::debug($key['user'], "User");

        if (!$ssh->login($key['user'], $rsa)) {

            Debug::debug($key['user'], "Login Failed");
            $login_successfull = false;


            //Debug($ssh, "ssh");
        }
echo $ssh->getLog();


        $msg = ($login_successfull) ? "Successfull" : "Failed";
        $ret = "Connection to server (".$server['display_name']." ".$ip_port.") : ".$msg;

        $this->logger->info($ret);
        Debug::debug($ret);


        if ($login_successfull === true) {

            Debug::debug($ip_port, "Login Successfull");

            $data                                                   = array();
            $data['link__mysql_server__ssh_key']['id_mysql_server'] = $server['id'];
            $data['link__mysql_server__ssh_key']['id_ssh_key']      = $key['id'];
            $data['link__mysql_server__ssh_key']['added_on']        = date('Y-m-d H:i:s');
            $data['link__mysql_server__ssh_key']['active']          = 1;


            $db->sql_save($data);
        } else {
            Debug::debug($ip_port, "Login Failed");
        }
    }

/**
 * Handle ssh state through `display_public`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for display_public.
 * @phpstan-return void
 * @psalm-return void
 * @see self::display_public()
 * @example /fr/ssh/display_public
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function display_public($param)
    {
        $id_ssh_key = $param[0];

        $this->view        = false;
        $this->layout_name = false;

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "select public_key from ssh_key where id =".$id_ssh_key;

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            echo Chiffrement::decrypt($ob->public_key)."\n";
        }
    }

/**
 * Handle ssh state through `test_key`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for test_key.
 * @phpstan-return void
 * @psalm-return void
 * @see self::test_key()
 * @example /fr/ssh/test_key
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function test_key($param)
    {
        Debug::parseDebug($param);


        $ret = SshLib::isValid("-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAqB17idEzGY67EBefjp7fd7BVj15uJKJPZY+ABRTjCeLt7BkV
uUyJUU+YEFfGuYoFCyihLaKs8Bidy/xoF5DVnUXx0vwPke7YaulammwbS+19DGpf
GhXdspbbFSFGfjuL5du9z0kdwS4bA9s0KZvv01tU1DjBpiGmrLa4//tHsQsKMRa8
xTClEPnKllQOQklVnl0ICj4NC49ndhvlMqFMwiKvkXxGH+OKDtBmjI6DViyG9EK0
2kcMkst5I3eaG1aDvsziRCZCXeI0/oRF1mbTTmWihVfh8id6DyZzSzWKCwWUn+IR
wSRZXav53pUT4xuc1vJ+wECZHWpjAQrFOXwcHwIDAQABAoIBAGUJsyHVVXzax5qY
WBEDcxMgK4wLGO9zjXxgjnR/ZSSf+paXTPMdCLqRt7a6ynjgdr+KH7SpvH5gjRX4
ESd4qKnpS7mePE1c2z0GGqoMpysvBKTdmWK4GZIoEGvWn+NmLmJretyF+RgNebcL
m4IWckD49zbFFb2fI+lRuEZA44mHO6iNReuRPOKDEi8SJxHBzFj4v8kQc+NjlIzU
xrX2uRxhXdSLhYVyXmUHZPOqTqFNI272sIBFW+XQNFvraIOGCb+RazLOfY+8qtpg
hhz/k41aVBYnWf3RXqk32z3UhBAEtnAUXV4dzwpKMhsquzYX/dD1H6U3RnC5BDzM
Xuu4E+ECgYEA0nTyWdQrjL2QNU6oHL46VquMjE/69wFQNjDa3xS5gW9nx9WsAOHv
76SVfuP5gggP50kAcLnS4IirWNF3g9WT6Vfly7H5WxfA2dsGPwGWweZDpoOK8MfJ
GlRmeuAKqAYht/nj2G1vYF67G3WlnfruSpz6TBSuBLeOcTHqPe64ZEUCgYEAzH7b
/h3fG/4+eWwk8vjps5Aep1cNk5WShATMAswbbor/yPCbMhNKOpCrTm8NxprOhYpi
RdlxGmsfW7LKof3Oya50NklsNKD6XlezKbXdTBqLDtovATLSc2tCI2HIrVFyzsKt
J0td81qMinRKfUJKXH1I3XXXmyGeWYCcH6oULxMCgYAm5RYlI+EokaAlOfQ327BM
dEf1ZpKrM8LvQPgyYlImacB0Xjj7sMX3NCOs39UtAvBtfkBmlPE0Lg38zDmaU86S
QXxmuO2suCccHC57Vn/WNggqrgTvmvy/sPl/nAhcJUX2Cmjhhtgep2NNH+EL4WRI
xdo8VVYT6RiaMu9nosbRQQKBgAMp+1FlOOx/9IuAZtnzi/ohQrgoGqer6sZsJJPu
gIYnVGnRfzU5Iy7gyiW+hiIKhyN9zqNyB9P20Fdk3sm+2ZI5RscIP8pYq0cGaFk+
3RuuVXR3X77PAH6UrENL4gT8e6BDVtaCzgNT5VTHE9f4TJo9vgDfL+TQklikKsY6
pXFNAoGBAIVe6alhrFOcbN/3Oizc9l2ohR3CyLfjv53DRkE6hth1NnYYi/ubiGW+
hKJpixKUd4UzjhoBOc/yfncqaFtO8DG721rNQ2IGGrEgwJsNEihkS8m1hbQsRR/Y
3Jqb39NMtJSyeAB6lHcoCjaVYoukjXbR/pjGsmiEGy+dfrauaur8
-----END RSA PRIVATE KEY-----");

        Debug::debug($ret);
    }

/**
 * Handle ssh state through `test2_key`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for test2_key.
 * @phpstan-return void
 * @psalm-return void
 * @see self::test2_key()
 * @example /fr/ssh/test2_key
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function test2_key($param)
    {
        Debug::parseDebug($param);


        $ret = SshLib::isValid("ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCoHXuJ0TMZjrsQF5+Ont93sFWPXm4kok9lj4AFFOMJ4u3sGRW5TIlRT5gQV8a5igULKKEtoqzwGJ3L/GgXkNWdRfHS/A+R7thq6VqabBtL7X0Mal8aFd2yltsVIUZ+O4vl273PSR3BLhsD2zQpm+/TW1TUOMGmIaastrj/+0exCwoxFrzFMKUQ+cqWVA5CSVWeXQgKPg0Lj2d2G+UyoUzCIq+RfEYf44oO0GaMjoNWLIb0QrTaRwySy3kjd5obVoO+zOJEJkJd4jT+hEXWZtNOZaKFV+HyJ3oPJnNLNYoLBZSf4hHBJFldq/nelRPjG5zW8n7AQJkdamMBCsU5fBwf root@aurelien-rdc");

        Debug::debug($ret);
    }

/**
 * Handle ssh state through `generate`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for generate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generate()
 * @example /fr/ssh/generate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function generate($param)
    {

        $this->view        = false;
        $this->layout_name = false;

        Debug::parseDebug($param);

        $type = $param[0];
        $bit  = $param[1];


        $key = SshLib::generate($type, $bit);


        echo json_encode($key);
    }
    /*
     * (PmaControl 1.3.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return none
     * @package Controller
     * @since 1.3.8 First time this was introduced.
     * @description worker to
     * @access public


      If you are getting this message on your *NIX box:

      Warning: msg_get_queue() [function.msg-get-queue]: failed for key 0x12345678: No space left on device in /path/to/script.php on line 1

      you may use the command "ipcrm" as root to clear the message queue. Use "man ipcrm" to get more info on it.
      The default setting for maximum messages in the queue is stored in /proc/sys/fs/mqueue/msg_max. To increase it to a maximum of 100 messages, just run:
      echo 100 > /proc/sys/fs/mqueue/msg_max

      ipcs to see the process
      Please ensure to follow a good programming style and close/free all your message queues before your script exits to avoid those warning messages.
     */

    public function workerAssociate($param)
    {

        Debug::parseDebug($param);

        $pid = getmypid();

        $queue = msg_get_queue(self::KEY_WORKER_ASSOCIATE);

        $msg_type     = NULL;
        $msg          = NULL;
        $max_msg_size = 20480;

        $data        = array();
        $data['pid'] = $pid;

        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {
            $data = json_decode(json_encode($msg), true);

            //$server = json_encode($data['server']);
            //$key    = json_encode($data['key']);
            //Debug::debug($server, "server");
            Debug::debug($data, "data");


            $db = Sgbd::sql(DB_DEFAULT);

            $this->tryAssociate(array($data['server'], $data['key']));

            $db->sql_close();
        }
    }

/**
 * Handle ssh state through `edit`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for edit.
 * @phpstan-return void
 * @psalm-return void
 * @see self::edit()
 * @example /fr/ssh/edit
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function edit($param)
    {
        $id_ssh_key            = $param[0];
        $_GET['ssh_key']['id'] = $id_ssh_key;

        $this->add(array());


// ajout de la bonne vue
        $this->view = "add";


        $id_ssh_key = $param[0];

        $this->title = '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>'." ".__("Edit a key SSH");


        if (!empty($id_ssh_key)) {

            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT * FROM ssh_key WHERE id = ".$id_ssh_key;
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {

                $_SESSION['ssh_key']['private_key'] = Chiffrement::decrypt($ob->private_key);
                $_SESSION['ssh_key']['public_key']  = Chiffrement::decrypt($ob->public_key);
                $_GET['ssh_key']['user']            = $ob->user;
                $_GET['ssh_key']['name']            = $ob->name;
                $_GET['ssh_key']['id']              = $ob->id;
            }
        }
    }
}
