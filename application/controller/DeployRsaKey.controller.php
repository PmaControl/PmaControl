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

class DeployRsaKey extends Controller {

    public function index() {

        $this->di['js']->code_javascript('
        $("#checkAll").click(function(){
    $("input:checkbox").not(this).prop("checked", this.checked);
});');


        $this->title = '<i style="font-size: 32px" class="fa fa-key" aria-hidden="true"></i> ' . "Deploy key RSA";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '
                . '<i style="font-size: 16px" class="fa fa-key" aria-hidden="true"></i> ' . "Deploy key RSA";

        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['settings'])) {

                

                $list_id = [];
                foreach ($_POST['id'] as $key => $value) {

                    if (!empty($_POST['mysql_server'][$key]["is_monitored"]) && $_POST['mysql_server'][$key]["is_monitored"] === "on") {
                        $list_id[] = $value;
                    }
                }

                $ids = implode(",", $list_id);

                $sql = "SELECT * FROM mysql_server WHERE id IN (" . $ids . ")";
                $res = $db->sql_query($sql);

                shell_exec("mkdir " . DATA . "keys/");

                while ($ob = $db->sql_fetch_object($res)) {

                    $path_private_key = DATA . "keys/" . $ob->ip . '.key';
                    $path_puplic_key = $path_private_key . '.pub';

                    $checks = [$path_private_key, $path_puplic_key];
                    foreach ($checks as $file) {
                        if (file_exists($file)) {
                            $ret = shell_exec("rm -f " . $file);
                        }
                    }

                    $cmd = 'ssh-keygen -t rsa -N "" -b 4096 -C "PmaControl@esysteme.com" -f ' . $path_private_key;
                    $ret = shell_exec($cmd);




                    $this->deploy($ob->ip, $_POST['mysql_server']['login_ssh'], $_POST['mysql_server']['password_ssh'], $path_puplic_key, $_POST['mysql_server']['key_ssh']);

                    if ($this->testConnection($ob->ip, "root", $path_private_key) === true) {
                        $table = [];
                        $table['mysql_server']['id'] = $ob->id;
                        $table['mysql_server']['ssh_login'] = $_POST['mysql_server']['login_ssh'];
                        $table['mysql_server']['ssh_password'] = $_POST['mysql_server']['password_ssh'];
                        $table['mysql_server']['key_private_path'] = $path_private_key;
                        $table['mysql_server']['key_private_user'] = "root";

                        $ret = $db->sql_save($table);

                        if (!$ret) {
                            debug($table);
                            debug($ret->sql_error());
                        }
                    }
                }

                //debug($list_id);

                //header("location: ".LINK."DeployRsaKey/index/");
                exit;
            }
        }

        $this->title = '<i class="fa fa-server"></i> ' . __("Servers");
        $this->ariane = ' > <a href⁼"">' . '<i class="fa fa-cog" style="font-size:14px"></i> '
                . __("Settings") . '</a> > <i class="fa fa-server"  style="font-size:14px"></i> ' . __("Servers");

        $sql = "SELECT * FROM mysql_server a WHERE 1=1 " . $this->getFilter() . " ORDER by name";
        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    //to mutualize
    private function getFilter() {

        $where = "";


        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }


        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (" . implode(',', json_decode($environment, true)) . ")";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client IN (" . implode(',', json_decode($client, true)) . ")";
        }


        return $where;
    }

    private function deploy($ip, $login, $password, $path_public_key, $pv_key) {
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

        $data = file_get_contents($path_public_key);
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
            $cmd = "mkdir -p /root/.ssh && cat /" . $login . "/" . $file_name . " >> /root/.ssh/authorized_keys\n";

            debug($cmd);
            $res = $ssh2->exec($cmd);

            debug($res);
        } else {

            $ssh2->setTimeout(1);
            $output = $ssh2->read('/.*@.*[$|#]/');
            debug($output);

            $ssh2->write("sudo su -\n");
            $ssh2->setTimeout(1);

            $ssh2->write($password . "\n");
            $output = $ssh2->read('/.*@.*[$|#]/');
            debug($output);

            $ssh2->write("whoami\n");
            $ssh2->write("mkdir -p /root/.ssh && cat /home/" . $login . "/" . $file_name . " >> /root/.ssh/authorized_keys\n");

            $output = $ssh2->read('/.*@.*[$|#]/');
            debug($output);
        }

        if ($login === "root") {
            $cmd = "rm /root/" . $file_name;
        } else {
            $cmd = "rm /home/" . $login . "/" . $file_name . "";
        }
        $output = $ssh2->exec($cmd);
        debug($output);

        return true;
    }

    private function testConnection($ip, $login, $path_private_key) {
        $ssh2 = new SSH2($ip);
        $rsa = new RSA();

        $privatekey = file_get_contents($path_private_key);

        if ($rsa->loadKey($privatekey) === false) {
            return false;
        }

        if (!$ssh2->login($login, $rsa)) {
            return false;
        }

        return true;
    }

    public function dropKeySsh($params) {
        //drop des clef ssh sur tout les serveur ou sur un
        //a coder
    }

}
