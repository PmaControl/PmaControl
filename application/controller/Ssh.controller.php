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

class Ssh extends Controller
{
    const KEY_WORKER_ASSOCIATE = 435665;
    const NB_WORKER            = 10;

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

        $this->title = '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>'." ".__("Add a key SSH");



        $this->di['js']->code_javascript('
                $(function(){

                var priv = $("#key_priv");
                var pub = $("#key_pub");

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


        $filename = $param[0] ?? "";

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
        } else {
            if (!IS_CLI) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    //traitement du UI en post

                    if (isset($_POST['ssh_key'])) {

                        $keys = $_POST['ssh_key'];

                        $keys['public_key']  = $_POST['public_key'];
                        $keys['private_key'] = $_POST['private_key'];


                        $this->save($keys);
                    }
                }
            }
        }
    }

    private function save($keys)
    {

        if (!empty($keys)) {
            $fingerprint = \Glial\Cli\Ssh::ssh2_fingerprint($keys['public_key'], 1);

            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "SELECT id from ssh_key WHERE fingerprint='".$fingerprint."'";
            $res = $db->sql_query($sql);

            $data            = array();
            $data['ssh_key'] = $keys;
            while ($ob              = $db->sql_fetch_object($res)) {
                $data['ssh_key']['id'] = $ob->id;
            }

            preg_match("/ssh\-(\w+)/", $keys['public_key'], $output_array);


            $ret = SshLib::isValid(str_replace('\n', "\n", $keys['public_key']));
            if ($ret === false) {
                $msg   = I18n::getTranslation(__("Your public key is not valid"));
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                header('location: '.LINK."ssh/add");
            }

            $data['ssh_key']['comment'] = $ret['name'];
            $data['ssh_key']['bit']     = $ret['bit'];
            $data['ssh_key']['type']    = $ret['type'];

            $ret = SshLib::isValid(str_replace('\n', "\n", $keys['private_key']));

            if ($ret === false) {
                $msg   = I18n::getTranslation(__("Your private key is not valid"));
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                header('location: '.LINK."ssh/add");
            }

            $data['ssh_key']['added_on']    = date('Y-m-d H:i:s');
            $data['ssh_key']['fingerprint'] = $db->sql_real_escape_string($fingerprint);
            $data['ssh_key']['public_key']  = Chiffrement::encrypt(str_replace('\n', "\n", $keys['public_key']));
            $data['ssh_key']['private_key'] = Chiffrement::encrypt(str_replace('\n', "\n", $keys['private_key']));
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

        $this->title = '<i class="fa fa-key" aria-hidden="true"></i> SSH keys';

        $this->di['js']->addJavascript(array('clipboard.min.js'));

        $this->di['js']->code_javascript('
(function(){
  new Clipboard(".copy-button");
})();

');
        $db = $this->di['db']->sql(DB_DEFAULT);


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


        $data['ssh_supported'] = array('rsa', 'dsa', 'RSA', 'DSA');

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


        $id_ssh_key = $param[0];

        $keys = $this->getSshKeys($id_ssh_key);

        $db = $this->di['db']->sql(DB_DEFAULT);



        $sql = "WITH z as (SELECT a.id
FROM mysql_server a
INNER JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
WHERE `active`=1 and b.id_ssh_key in(".$id_ssh_key."))
SELECT b.* FROM mysql_server b, ssh_key c
WHERE c.id in (".$id_ssh_key.")
AND b.id NOT IN (select id from z)
AND b.is_available = 1;";




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

        for ($id_worker = 1; $id_worker < self::NB_WORKER; $id_worker++) {

            $cmd = $php." ".GLIAL_INDEX." Ssh workerAssociate >> ".TMP."log/".__FUNCTION__."_".$id_worker.".log 2>&1 & echo $!";
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

    private function getSshKeys($id_ssh_key = "")
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

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

    private function tryAssociate($server, $key)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $ssh = new SSH2($server['ip']);
        $rsa = new RSA();

        $login_successfull = true;


        $key['private_key'] = Chiffrement::decrypt($key['private_key']);


        if ($rsa->loadKey($key['private_key']) === false) {
            $login_successfull = false;
            Debug::debug($server['ip'], "private key loading failed!");

            return false;
        }


        if (!$ssh->login($key['user'], $rsa)) {
            Debug::debug($server['ip'], "Login Failed");
            $login_successfull = false;
        }

        $msg = ($login_successfull) ? "Successfull" : "Failed";
        $ret = "Connection to server (".$server['display_name']." ".$server['ip'].":22) : ".$msg;

        $this->logger->info($ret);
        //Debug::debug($ret);


        if ($login_successfull === true) {

            Debug::debug($server['ip'], "Login Successfull");

            $data                                                   = array();
            $data['link__mysql_server__ssh_key']['id_mysql_server'] = $server['id'];
            $data['link__mysql_server__ssh_key']['id_ssh_key']      = $key['id'];
            $data['link__mysql_server__ssh_key']['added_on']        = date('Y-m-d H:i:s');
            $data['link__mysql_server__ssh_key']['active']          = 1;


            $db->sql_save($data);
        }
    }

    public function display_public($param)
    {
        $id_ssh_key = $param[0];

        $this->view        = false;
        $this->layout_name = false;

        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "select public_key from ssh_key where id =".$id_ssh_key;

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            echo Chiffrement::decrypt($ob->public_key)."\n";
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

    public function generate($param)
    {

        $this->view        = false;
        $this->layout_name = false;

        Debug::parseDebug($param);

        $type = $param[0];
        $bit  = $param[1];


        $key = SshLib::generate($type,$bit);


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

    public function workerAssociate()
    {
        $pid = getmypid();

        $queue = msg_get_queue(self::KEY_WORKER_ASSOCIATE);

        $msg_type     = NULL;
        $msg          = NULL;
        $max_msg_size = 20480;

        $data        = array();
        $data['pid'] = $pid;

        while (msg_receive($queue, 1, $msg_type, $max_msg_size, $msg)) {
            $data = json_decode(json_encode($msg), true);
            $this->tryAssociate($data['server'], $data['key']);
        }
    }
}