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
use App\Library\Chiffrement;

class DeployRsaKey extends Controller
{

    public function index()
    {

        $this->di['js']->code_javascript('
        $("#check-all").click(function(){
    $("input:checkbox").not(this).prop("checked", this.checked);
});');


        $this->title  = '<i style="font-size: 32px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '
            .'<i style="font-size: 16px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['settings'])) {

                debug($_POST);


                $sql = "SELECT * FROM ssh_key WHERE id=".$_POST['ssh_key']['id'];
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    $path_puplic_key = Chiffrement::decrypt($ob->public_key);
                }


                $path_private_key = $_POST['mysql_server']['key_ssh'];

                $list_id = [];
                foreach ($_POST['id'] as $key => $value) {

                    if (!empty($_POST['mysql_server'][$key]["is_monitored"]) && $_POST['mysql_server'][$key]["is_monitored"] === "on") {
                        $list_id[] = $value;
                    }
                }

                $ids = implode(",", $list_id);

                $sql = "SELECT * FROM mysql_server WHERE id IN (".$ids.");";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {

                    $this->deploy($ob->ip, $_POST['mysql_server']['login_ssh'], $_POST['mysql_server']['password_ssh'], $path_puplic_key, $_POST['mysql_server']['key_ssh']);

                    if ($this->testConnection($ob->ip, "root", $path_private_key) === true) {
                        echo "CONNECTION OK !!!";
                    }
                }

//debug($list_id);
//header("location: ".LINK."DeployRsaKey/index/");
                exit;
            }
        }

        $this->title     = '<i class="fa fa-key" aria-hidden="true"></i> '.__("Deploy RSA key");
        /* $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-cog" style="font-size:14px"></i> '
          .__("Settings").'</a> > <i class="fa fa-server"  style="font-size:14px"></i> '.__("Servers");
         */
        $sql             = "SELECT *, b.libelle as organization FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            WHERE 1=1 ".$this->getFilter()." ORDER by name";
        $data['servers'] = $db->sql_fetch_yield($sql);


        $sql = "SELECT * FROM ssh_key where type='rsa'";
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

    private function deploy($ip, $login, $password, $path_public_key, $pv_key)
    {
        echo "$ip, $login, $password<br />";


        if (!empty($pv_key)) {
            $key = new RSA();
            $key->loadKey($pv_key);


            $password = $key;
        }

//deploy public key by SCP
        $sftp = new SFTP($ip);


        if (!$sftp->login($login, $password)) {
            echo 'SCP Login Failed';
            return false;
        } else {
            debug("SCP OK ! ");
        }

        $data      = file_get_contents($path_public_key);
        $file_name = pathinfo($path_public_key)['basename'];
        $sftp->put($file_name, $data);

        $files = $sftp->rawlist();



        $found = false;
        foreach ($files as $file) {
            if ($file['filename'] === $file_name) {


                debug($file['filename']);
                debug("found KEY SSH");
                $found = true;
                break;
            }
        }

        if ($found === false) {
            return false;
        }



//connect with user and then with sudo su - and move key to root
        $ssh2 = new SSH2($ip);


        if (!$ssh2->login($login, $password)) {

            debug('FAILED !!!!!!!!');
        }


        if ($login === "root") {
            $cmd = "mkdir -p /root/.ssh && cat /".$login."/".$file_name." >> /root/.ssh/authorized_keys\n";

            debug($cmd);
            $res = $ssh2->exec($cmd);

            debug($res);
        } else {

            $ssh2->setTimeout(1);
            $output = $ssh2->read('/.*@.*[$|#]/');
            debug($output);

            $ssh2->write("sudo su -\n");
            $ssh2->setTimeout(1);

            $ssh2->write($password."\n");
            $output = $ssh2->read('/.*@.*[$|#]/');
            debug($output);

            $ssh2->write("whoami\n");
            $ssh2->write("mkdir -p /root/.ssh && cat /home/".$login."/".$file_name." >> /root/.ssh/authorized_keys\n");

            $output = $ssh2->read('/.*@.*[$|#]/');
            debug($output);
        }

        if ($login === "root") {
            $cmd = "rm /root/".$file_name;
        } else {
            $cmd = "rm /home/".$login."/".$file_name."";
        }
        $output = $ssh2->exec($cmd);
        debug($output);

        return true;
    }

    private function testConnection($ip, $login, $path_private_key)
    {
        $ssh2 = new SSH2($ip);
        $rsa  = new RSA();


        if (file_exists($path_private_key)) {

            $privatekey = file_get_contents($path_private_key);
        } else {
            $privatekey = $path_private_key;
        }


        if ($rsa->loadKey($privatekey) === false) {

            if (!$ssh2->login($login, $rsa)) {
                return false;
            }

            
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
}