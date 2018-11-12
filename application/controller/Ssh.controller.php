<?php

use \Glial\Synapse\Controller;
use App\Library\Chiffrement;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Ssh as SshLib;
use \Glial\I18n\I18n;

class Ssh extends Controller {

    public function keys() {
        
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

    public function before($param) {
        $logger = new Logger('Daemon');
        $file_log = LOG_FILE;
        $handler = new StreamHandler($file_log, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

    public function add($param) {

        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);

            $all_keys = $config['ssh'];

            foreach ($all_keys as $keys) {
                $to_check = array('name', 'user', 'private_key', 'public_key', 'organization');

                foreach ($to_check as $elem) {
                    if (empty($keys[$elem])) {
                        throw new \InvalidArgumentException("PMACTRL-030 : ssh." . $elem . " is missing in file : " . $filename);
                    }
                }

                $this->save($keys);
            }
        } else {
            if (!IS_CLI) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    //traitement du UI en post

                    if (isset($_POST['ssh_key'])) {

                        $keys = $_POST['ssh_key'];

                        $keys['public_key'] = $_POST['public_key'];
                        $keys['private_key'] = $_POST['private_key'];

                        debug($keys);

                        $this->save($keys);
                    }
                }
            }
        }
    }

    private function save($keys) {

        if (!empty($keys)) {
            $fingerprint = \Glial\Cli\Ssh::ssh2_fingerprint($keys['public_key'], 1);

            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "SELECT id from ssh_key WHERE fingerprint='" . $fingerprint . "'";
            $res = $db->sql_query($sql);


            $data = array();
            $data['ssh_key'] = $keys;
            while ($ob = $db->sql_fetch_object($res)) {
                $data['ssh_key']['id'] = $ob->id;
            }

            preg_match("/ssh\-(\w+)/", $keys['public_key'], $output_array);


            $ret = SshLib::isValid(str_replace('\n', "\n", $keys['public_key']));
            if ($ret === false) {
                $msg = I18n::getTranslation(__("Your public key is not valid"));
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                header('location: ' . LINK . "ssh/add");
            }
            
            
            $data['ssh_key']['comment'] = $ret['name'];
            $data['ssh_key']['bit'] = $ret['bit'];


            $ret = SshLib::isValid(str_replace('\n', "\n", $keys['private_key']));

            
            
            debug($ret);
            
            exit;

            if ($ret === false) {
                $msg = I18n::getTranslation(__("Your private key is not valid"));
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                header('location: ' . LINK . "ssh/add");
            }


            $data['ssh_key']['type'] = $output_array[1];
            $data['ssh_key']['added_on'] = date('Y-m-d H:i:s');
            $data['ssh_key']['fingerprint'] = $db->sql_real_escape_string($fingerprint);
            $data['ssh_key']['public_key'] = Chiffrement::encrypt(str_replace('\n', "\n", $keys['public_key']));
            $data['ssh_key']['private_key'] = Chiffrement::encrypt(str_replace('\n', "\n", $keys['private_key']));
            $data['ssh_key']['user'] = $keys['user'];



            $res = $db->sql_save($data);

            if (!$res) {
                debug($data);

                throw new \Exception("PMACTRL-031 : Impossible to save ssh key");
            }
        }
    }

    private function parseConfig($configFile) {

        if (empty($configFile) || !file_exists($configFile)) {
            throw new \Exception('PMACTRL-255 : The file ' . $configFile . ' doesn\'t exit !');
        }

        $file = file_get_contents($configFile);
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


        throw new \Exception("PMACTRL-254 : JSON : " . $error, 80);
    }

    public function index() {
        $this->title = '<i class="fa fa-key" aria-hidden="true"></i> SSH keys';

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT * from ssh_key";
        $res = $db->sql_query($sql);


        $data['keys'] = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['keys'][] = $arr;
        }


        $sql2 = "SELECT a.*, b.active, c.id as id_key FROM mysql_server a
            INNER JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
            INNER JOIN ssh_key c ON c.id = b.id_ssh_key
            GROUP BY c.id, a.id";
        $res2 = $db->sql_query($sql2);
        $data['servers'] = array();
        while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $data['servers'][$arr2['id_key']][] = $arr2;
        }


        $data['ssh_supported'] = array('rsa', 'dsa');

        $this->set('data', $data);
    }

    public function delete($param) {
        $this->view = false;

        if (!empty($param[0])) {
            $db = $this->di['db']->sql(DB_DEFAULT);
            $id_clef = intval($param[0]);

            $sql = "DELETE FROM `ssh_key` WHERE `id`= " . $id_clef;

            $db->sql_query($sql);
        }

        header("location: " . LINK . __CLASS__ . "/index");
    }

    public function associate($param) {
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

    private function getSshKeys() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `ssh_key`";

        $res = $db->sql_query($sql);


        $key = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $key[] = $arr;
        }

        return $key;
    }

    private function tryAssociate($server, $key) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $ssh = new SSH2($server['ip']);
        $rsa = new RSA();

        $login_successfull = true;


        $key['private_key'] = Chiffrement::decrypt($key['private_key']);


        if ($rsa->loadKey($key['private_key']) === false) {
            $login_successfull = false;
            Debug::debug($server['ip'], "private key loading failed!");
        }

        if (!$ssh->login($key['user'], $rsa)) {
            Debug::debug($server['ip'], "Login Failed");
            $login_successfull = false;
        }

        $msg = ($login_successfull) ? "Successfull" : "Failed";
        $ret = "Connection to server (" . $server['display_name'] . " " . $server['ip'] . ":22) : " . $msg;

        $this->logger->info($ret);
        //Debug::debug($ret);


        if ($login_successfull === true) {

            Debug::debug($server['ip'], "Login Successfull");

            $data = array();
            $data['link__mysql_server__ssh_key']['id_mysql_server'] = $server['id'];
            $data['link__mysql_server__ssh_key']['id_ssh_key'] = $key['id'];
            $data['link__mysql_server__ssh_key']['added_on'] = date('Y-m-d H:i:s');
            $data['link__mysql_server__ssh_key']['active'] = 1;


            $db->sql_save($data);
        }
    }

    public function display_public($param) {
        $id_ssh_key = $param[0];

        $this->view = false;
        $this->layout_name = false;

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "select public_key from ssh_key where id =" . $id_ssh_key;

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            echo Chiffrement::decrypt($ob->public_key) . "\n";
        }
    }

    
    
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
    
    
        public function test2_key($param)
    {
        Debug::parseDebug($param);
        
        
        $ret = SshLib::isValid("ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCoHXuJ0TMZjrsQF5+Ont93sFWPXm4kok9lj4AFFOMJ4u3sGRW5TIlRT5gQV8a5igULKKEtoqzwGJ3L/GgXkNWdRfHS/A+R7thq6VqabBtL7X0Mal8aFd2yltsVIUZ+O4vl273PSR3BLhsD2zQpm+/TW1TUOMGmIaastrj/+0exCwoxFrzFMKUQ+cqWVA5CSVWeXQgKPg0Lj2d2G+UyoUzCIq+RfEYf44oO0GaMjoNWLIb0QrTaRwySy3kjd5obVoO+zOJEJkJd4jT+hEXWZtNOZaKFV+HyJ3oPJnNLNYoLBZSf4hHBJFldq/nelRPjG5zW8n7AQJkdamMBCsU5fBwf root@aurelien-rdc");
        
        Debug::debug($ret);
        
        
    }
    
    
    
    
}
