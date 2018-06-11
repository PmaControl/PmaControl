<?php

use \Glial\Synapse\Controller;
use App\Library\Chiffrement;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;

class Ssh extends Controller
{

    public function keys()
    {
        
    }
    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description log error & start / stop daemon
     * @access public
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

    public function add($param)
    {

        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);

            $all_keys = $config['ssh'];

            foreach ($all_keys as $keys) {
                $to_check = array('user', 'private key', 'public key', 'organization');

                foreach ($to_check as $elem) {
                    if (empty($keys[$elem])) {
                        throw new \InvalidArgumentException("PMACTRL-030 : ssh.".$elem." is missing in file : ".$filename);
                    }
                }


                $this->save($keys);
            }
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                //traitement du UI en post

                if (isset($_POST['ssh_key'])) {

                    $keys = $_POST['ssh_key'];

                    $keys['public key']  = $_POST['public_key'];
                    $keys['private key'] = $_POST['private_key'];


                    $this->save($keys);
                }
            }
        }
    }

    private function save($keys)
    {

        if (!empty($keys)) {
            $fingerprint = \Glial\Cli\Ssh::ssh2_fingerprint($keys['public key'], 1);

            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "SELECT id from ssh_key WHERE fingerprint='".$fingerprint."'";
            $res = $db->sql_query($sql);


            $data            = array();
            $data['ssh_key'] = $keys;
            while ($ob              = $db->sql_fetch_object($res)) {
                $data['ssh_key']['id'] = $ob->id;
            }


            $data['ssh_key']['added_on']    = date('Y-m-d H:i:s');
            $data['ssh_key']['fingerprint'] = $db->sql_real_escape_string($fingerprint);
            $data['ssh_key']['public_key']  = Chiffrement::encrypt(str_replace('\n', "\n", $keys['public key']));
            $data['ssh_key']['private_key'] = Chiffrement::encrypt(str_replace('\n', "\n", $keys['private key']));
            $data['ssh_key']['user']        = $keys['user'];

            $res = $db->sql_save($data);

            if (!$res) {
                debug($data);

                throw new \Exception("PMACTRL-031 : Impossible to save ssh key");
            }
        }
    }

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

    public function index()
    {


        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * from ssh_key";
        $res = $db->sql_query($sql);


        $data['keys'] = array();
        while ($arr          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['keys'][] = $arr;
        }


        $sql2            = "SELECT a.*, b.active FROM mysql_server a
            INNER JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
            INNER JOIN ssh_key c ON c.id = b.id_ssh_key
            GROUP BY c.id, a.id";
        $res2            = $db->sql_query($sql2);
        $data['servers'] = array();
        while ($arr2            = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $data['servers'][] = $arr2;
        }



        $this->set('data', $data);
    }

    public function delete($param)
    {
        $this->view = false;

        if (!empty($param[0])) {
            $db      = $this->di['db']->sql(DB_DEFAULT);
            $id_clef = intval($param[0]);

            $sql = "DELETE FROM `ssh_key` WHERE `id`= ".$id_clef;

            $db->sql_query($sql);
        }

        header("location: ".LINK.__CLASS__."/index");
    }

    public function associate($param)
    {
        $this->view = false;


        Debug::parseDebug($param);


        $keys = $this->getSshKeys();

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT a.* FROM mysql_server a
            LEFT JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
            LEFT JOIN `ssh_key` c ON c.id = b.id_ssh_key
            WHERE (`active`=0 OR `active` IS NULL)";


        $res = $db->sql_query($sql);


        while ($server = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            foreach ($keys as $key) {


                $this->tryAssociate($server, $key);
            }
        }
    }

    private function getSshKeys()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `ssh_key`";

        $res = $db->sql_query($sql);


        $key = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $key[] = $arr;
        }

        return $key;
    }

    private function tryAssociate($server, $key)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $ssh = new SSH2($server['ip']);
        $rsa = new RSA();

        $login_successfull = true;

        debug(Chiffrement::decrypt($key['private_key']));

        $key['private_key'] = Chiffrement::decrypt($key['private_key']);

        debug($key);

        if ($rsa->loadKey($key['private_key']) === false) {
            $login_successfull = false;
            Debug::debug("private key loading failed!");
        }

        if (!$ssh->login($key['user'], $rsa)) {
            Debug::debug("Login Failed");
            $login_successfull = false;
        }

        $msg = ($login_successfull) ? "Successfull" : "Failed";
        $ret = "Connection to server (".$server['display_name']." ".$server['ip'].":22) : ".$msg;

        $this->logger->info($ret);
        //Debug::debug($ret);


        if ($login_successfull === true) {
            $data                                                   = array();
            $data['link__mysql_server__ssh_key']['id_mysql_server'] = $server['id'];
            $data['link__mysql_server__ssh_key']['id_ssh_key']      = $key['id'];
            $data['link__mysql_server__ssh_key']['added_on']        = date('Y-m-d H:i:s');
            $data['link__mysql_server__ssh_key']['active']          = 1;


            $db->sql_save($data);
        }
    }

}