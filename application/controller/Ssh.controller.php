<?php

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;

class Ssh extends Controller
{

    public function keys()
    {
        
    }

    public function add($param)
    {

        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);

            $keys = $config['ssh'];

            $to_check = array('user', 'private key', 'public key');

            foreach ($to_check as $elem) {
                if (empty($keys[$elem])) {
                    throw new \InvalidArgumentException("PMACTRL-030 : ssh.".$elem." is missing in file : ".$filename);
                }
            }
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                //traitement du UI en post


                $keys = $_POST['ssh_key'];
            }
        }


        $fingerprint = \Glial\Cli\Ssh::ssh2_fingerprint($keys['public key'], 1);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id from ssh_key WHERE fingerprint='".$fingerprint."'";
        $res = $db->sql_query($sql);


        $data            = array();
        $data['ssh_key'] = $keys;
        while ($ob              = $db->sql_fetch_object($res)) {
            $data['ssh_key'] ['id'] = $ob->id;
        }


        $data['ssh_key']['added_on']    = date('Y-m-d H:i:s');
        $data['ssh_key']['fingerprint'] = $db->sql_real_escape_string($fingerprint);
        $data['ssh_key']['public_key']  = $this->crypt($keys['public key']);
        $data['ssh_key']['private_key'] = $this->crypt($keys['private key']);
        $data['ssh_key']['user']        = $keys['user'];



        $res = $db->sql_save($data);

        if (!$res) {
            debug($data);

            throw new \Exception("PMACTRL-031 : Impossible to save ssh key");
        }
    }

    private function crypt($password)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::encrypt($password);

        return $passwd;
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

    public function associate()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);






        $sql = "SELECT * FROM mysql_server a
            LEFT JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
            INNER JOIN `ssh_key` c ON c.id = b.id_ssh_key
            WHERE `active`=0";


        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {

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
}