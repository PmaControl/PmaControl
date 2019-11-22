<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use \App\Library\Debug;
use \App\Library\Post;

class StorageArea extends Controller {

    public function index($param) {

        $db = $this->di['db']->sql(DB_DEFAULT);


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'StorageArea/index.js'));


        if (empty($param[0])) {
            $data['menu'] = "listStorage";
        } else {
            $data['menu'] = $param[0];
        }

        $sql = "SELECT count(1) as cpt from backup_storage_area";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['cpt'] = $ob->cpt;
        }
        $this->set('data', $data);
    }

    public function add($param) {

//df -Ph . | tail -1 | awk '{print $2}' => to know space




        if (!empty($_GET['backup_storage_area']['path'])) {
            $_GET['backup_storage_area']['path'] = str_replace("[DS]", "/", $_GET['backup_storage_area']['path']);
        }


        $db = $this->di['db']->sql(DB_DEFAULT);
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            Crypt::$key = CRYPT_KEY;

            $storage_area['backup_storage_area'] = $_POST['backup_storage_area'];



            //debug($storage_area);

            $db = $this->di['db']->sql(DB_DEFAULT);

            $sql = "SELECT * FROM ssh_key WHERE id =" . $storage_area['backup_storage_area']['id_ssh_key'] . "";

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $ssh_private_key = Crypt::decrypt($ob->private_key, CRYPT_KEY);
                $ssh_user = $ob->user;
            }

            // clef ssh ou password ?
            if (!empty($ssh_private_key)) {
                $key = new RSA();
                $key->loadKey($ssh_private_key);
            }

            //deploy public key by SCP
            $ssh = new SSH2($storage_area['backup_storage_area']['ip']);

            //tentative connexion au serveur ssh
            if (!$ssh->login($ssh_user, $key)) {

                $elems = Post::getToPost();

                $title = I18n::getTranslation(__("Failed to connect on ssh/scp"));
                $msg = I18n::getTranslation(__("Please check your hostname and you credentials !"));

                set_flash("error", $title, $msg);

                header("location: " . LINK . "storageArea/add/" . $elems);
                exit;
            }

            $id_storage_area = $db->sql_save($storage_area);

            if (!$id_storage_area) {


                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $title = I18n::getTranslation(__("Fail to add this storage area"));
                $msg = I18n::getTranslation(__("One or more problem came when you try to add this storage, please verify your informations"));

                set_flash("error", $title, $msg);

                $elems = Post::getToPost();

                header("location: " . LINK . "storageArea/add/" . $elems);
                exit;
            } else {


                $php = explode(" ", shell_exec("whereis php"))[1];
                $cmd = $php . " " . GLIAL_INDEX . " StorageArea getStorageSpace " . $id_storage_area . "";
                shell_exec($cmd);

                $title = I18n::getTranslation(__("Successfull"));
                $msg = I18n::getTranslation(__("You storage area has been successfull added !"));

                set_flash("success", $title, $msg);
                header("location: " . LINK . "storageArea/listStorage");
                exit;
            }
        }


        $this->di['js']->addJavascript(array("jquery.browser.min.js", "jquery.autocomplete.min.js"));
        $this->di['js']->code_javascript('$("#backup_storage_area-id_geolocalisation_city-auto").autocomplete("' . LINK . 'user/city/none>none", {
		extraParams: {
			country: function() {return $("#backup_storage_area-id_geolocalisation_country").val();}
		},
        mustMatch: true,
        autoFill: true,
        max: 100,
        scrollHeight: 302,
        delay:0
		});
		$("#backup_storage_area-id_geolocalisation_city-auto").result(function(event, data, formatted) {
			if (data)
				$("#backup_storage_area-id_geolocalisation_city").val(data[1]);
		});
		$("#backup_storage_area-id_geolocalisation_country").change( function() 
		{
			$("#backup_storage_area-id_geolocalisation_city-auto").val("");
			$("#backup_storage_area-id_geolocalisation_city").val("");
		} );

		');

        $sql = "SELECT id, libelle from geolocalisation_country where libelle != '' order by libelle asc";
        $res = $db->sql_query($sql);
        $data['geolocalisation_country'] = $db->sql_to_array($res);


        $sql = "SELECT * FROM ssh_key order by name";

        $res = $db->sql_query($sql);


        $data['ssh_key'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = array();
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name . " (" . $ob->type . ":" . $ob->bit . " bit) " . $ob->fingerprint;

            $data['ssh_key'][] = $tmp;
        }


        $data['menu'] = __FUNCTION__;

        $this->set('data', $data);
    }

    public function listStorage() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT *,c.libelle as city, a.libelle as name,a.id as id_backup_storage_area
        FROM backup_storage_area a
        INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id
        INNER JOIN geolocalisation_city c ON c.id = a.id_geolocalisation_city
        ORDER BY a.libelle";

        $data['storage'] = $db->sql_fetch_yield($sql);

        $sql = "SELECT * FROM backup_storage_space b  
        JOIN (select max(id) as id from backup_storage_space a group by id_backup_storage_area) a ON a.id = b.id";

        $res = $db->sql_query($sql);

        while ($tab = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['space'][$tab['id_backup_storage_area']] = $tab;
        }

        $this->set('data', $data);
    }

    public function getStorageSpace($param) {

        Debug::parseDebug($param);

        $this->layout_name = false;
        $this->view = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT a.*, b.`user`, b.`private_key` FROM `backup_storage_area` a
            INNER JOIN `ssh_key` b ON a.`id_ssh_key` = b.id ";

        if (!empty($param[0])) {
            $sql .= " WHERE a.`id` = " . intval($param[0]) . "";
        }
        $sql .= ";";

        Debug::sql($sql);

        $storages = $db->sql_fetch_yield($sql);

        foreach ($storages as $storage) {

            $login = $storage['user'];
            $key_ssh = Crypt::decrypt($storage['private_key'], CRYPT_KEY);

            $rsa = new RSA();

            $rsa->loadKey($key_ssh);
            $password = $rsa;


            $ssh = new SSH2($storage['ip']);

            //$publicHostKey = $ssh->getServerPublicHostKey();


            if (!$ssh->login($login, $password)) {

                Debug::debug("SSH FAILED ! ");
            } else {
                Debug::debug("SSH ok !");

                /*
                 * df -k . => get file systeme for current directory
                 * tail -n +2 => remove the first line
                 * sed ':a;N;$!ba;s/\n/ /g' => remove \n (in case of the name of partition is really big and need to be on 2 lines)
                 * sed \"s/\ +/ /g\" => remove + in some case
                 * awk '{print $2 \" \" $3 \" \" $4 \" \" $5}' => split result by space
                 */

                $cmd = 'cd ' . $storage['path'] . ' && df -k . | tail -n +2 | sed ":a;N;$!ba;s/\n/ /g" | sed "s/\ +/ /g"';
                $resultats = $ssh->exec($cmd);
                $resultats = preg_replace('`([ ]{2,})`', ' ', $resultats);
                $results = explode(' ', trim($resultats));


                $cmd2 = "cd " . $storage['path'] . " && du -s . | awk '{print $1}'";
                $used_by_backup = $ssh->exec($cmd2);

                $data = [];
                $data['backup_storage_space']['id_backup_storage_area'] = $storage['id'];
                $data['backup_storage_space']['date'] = date('Y-m-d H:i:s');
                $data['backup_storage_space']['size'] = $results['1'];
                $data['backup_storage_space']['used'] = $results['2'];
                $data['backup_storage_space']['available'] = $results['3'];
                $data['backup_storage_space']['percent'] = substr(trim($results['4']), 0, -1);
                $data['backup_storage_space']['backup'] = trim($used_by_backup);

                if (!$db->sql_save($data)) {


                    debug($cmd . "\n");
                    debug($resultats);
                    debug($results);
                    debug($data);
                    debug($db->sql_error());
                    echo "\n";

                    return false;
                }
            }
        }

        return true;
    }

    public function delete($param) {
        
        $this->view = false;
        
        $id_backup_storage_area = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "DELETE FROM  backup_storage_area WHERE id ='" . $id_backup_storage_area . "'";

        $db->sql_query($sql);
        header("location: " . LINK . "StorageArea/index");
        exit;
    }

    public function menu($param) {



        if (empty($param[0])) {
            $data['menu'] = "listStorage";
        } else {
            $data['menu'] = $param[0];
        }




        $this->set("data", $data);
    }

    public function update($param) {

        $this->view = false;
        $this->layout_name = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "UPDATE menu SET `" . $_POST['name'] . "` = '" . $_POST['value'] . "' WHERE id = " . $db->sql_real_escape_string($_POST['pk']) . "";
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            header("HTTP/1.0 503 Internal Server Error");
        }
    }

}
