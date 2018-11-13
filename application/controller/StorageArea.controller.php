<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sgbd;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Cli\Crontab;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use \App\Library\Debug;

class StorageArea extends Controller
{

    public function index($param)
    {
        $this->title  = __("Storage area");
        $this->ariane = " > ".__("Backup")." > ".$this->title;
        $db           = $this->di['db']->sql(DB_DEFAULT);

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

    public function add()
    {

//df -Ph . | tail -1 | awk '{print $2}' => to know space

        $db = $this->di['db']->sql(DB_DEFAULT);
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            Crypt::$key = CRYPT_KEY;

            $storage_area['backup_storage_area'] = $_POST['backup_storage_area'];
            $password                            = $storage_area['backup_storage_area']['ssh_password'];


            // clef ssh ou password ?
            if (!empty($storage_area['backup_storage_area']['ssh_key'])) {
                $key      = new RSA();
                $key->loadKey($storage_area['backup_storage_area']['ssh_key']);
                $password = $key;
            }

            //deploy public key by SCP
            $ssh = new SSH2($storage_area['backup_storage_area']['ip']);


            //tentative connexion au serveur ssh
            if (!$ssh->login($storage_area['backup_storage_area']['ssh_login'], $password)) {
                foreach ($_POST['backup_storage_area'] as $var => $val) {
                    $ret[] = "backup_storage_area:".$var.":".urlencode(html_entity_decode($val));
                }

                $param = implode("/", $ret);

                $title = I18n::getTranslation(__("Failed to connect on ssh/scp"));
                $msg   = I18n::getTranslation(__("Please check your hostname and you credentials !"));

                set_flash("error", $title, $msg);

                header("location: ".LINK."storageArea/add/".$param);
                exit;
            }

            $storage_area['backup_storage_area']['ssh_login']    = Crypt::encrypt($storage_area['backup_storage_area']['ssh_login']);
            $storage_area['backup_storage_area']['ssh_password'] = Crypt::encrypt($storage_area['backup_storage_area']['ssh_password']);
            $storage_area['backup_storage_area']['ssh_key']      = Crypt::encrypt($storage_area['backup_storage_area']['ssh_key']);

            $id_storage_area = $db->sql_save($storage_area);

            if (!$id_storage_area) {


                $error             = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $title = I18n::getTranslation(__("Fail to add this storage area"));
                $msg   = I18n::getTranslation(__("One or more problem came when you try to add this storage, please verify your informations"));

                set_flash("error", $title, $msg);

                foreach ($_POST['backup_storage_area'] as $var => $val) {
                    $ret[] = "backup_storage_area:".$var.":".urlencode(html_entity_decode($val));
                }

                $param = implode("/", $ret);

                header("location: ".LINK."storageArea/add/".$param);
                exit;
            } else {



                $cmd = $php." ".GLIAL_INDEX." StorageArea getStorageSpace ".$id_storage_area." & echo $!";
                $pid = shell_exec($cmd);


                $title = I18n::getTranslation(__("Successfull"));
                $msg   = I18n::getTranslation(__("You storage area has been successfull added !"));

                set_flash("success", $title, $msg);
                header("location: ".LINK."storageArea/listStorage");
                exit;
            }
        }


        $this->di['js']->addJavascript(array("jquery.browser.min.js", "jquery.autocomplete.min.js"));


        $this->di['js']->code_javascript('$("#backup_storage_area-id_geolocalisation_city-auto").autocomplete("'.LINK.'user/city/none>none", {
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




        $sql                                   = "SELECT id, libelle from geolocalisation_country where libelle != '' order by libelle asc";
        $res                                   = $db->sql_query($sql);
        $this->data['geolocalisation_country'] = $db->sql_to_array($res);
        
        
        
        
        
        $sql = "SELECT * FROM ssh_key order by name";
        
        $res = $db->sql_query($sql);
        
        
        $this->data['ssh_key'] = array();
        while($ob = $db->sql_fetch_object($res))
        {
            $tmp = array();
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name." (".$ob->type.":".$ob->bit." bit) ".$ob->fingerprint;
            
            $this->data['ssh_key'][] = $tmp;
            
        }
        
        $this->set('data', $this->data);
    }

    public function listStorage()
    {
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

    public function getStorageSpace($param)
    {

        Debug::parseDebug($param);

        $this->layout_name = false;
        $this->view        = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM backup_storage_area";

        if (!empty($param[0])) {
            $sql .= " WHERE id = '".$param[0]."'";
        }
        $sql .= ";";

        Debug::debug(\SqlFormatter::highlight($sql));

        $storages = $db->sql_fetch_yield($sql);

        Crypt::$key = CRYPT_KEY;

        foreach ($storages as $storage) {

            $login = Crypt::decrypt($storage['ssh_login']);

            if (empty(trim($storage['ssh_password']))) {
                $key_ssh = Crypt::decrypt($storage['ssh_key']);

                $rsa = new RSA();

                $rsa->loadKey($key_ssh);
                $password = $rsa;
            } else {

                $password = Crypt::decrypt($storage['ssh_password']);
            }


            $ssh = new SSH2($storage['ip']);

            //$publicHostKey = $ssh->getServerPublicHostKey();

            Debug::debug(Crypt::decrypt($storage['ssh_login']));

            if (!$ssh->login(Crypt::decrypt($storage['ssh_login']), $password)) {

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

                $cmd       = 'cd '.$storage['path'].' && df -k . | tail -n +2 | sed ":a;N;$!ba;s/\n/ /g" | sed "s/\ +/ /g"';
                $resultats = $ssh->exec($cmd);
                $resultats = preg_replace('`([ ]{2,})`', ' ', $resultats);
                $results   = explode(' ', trim($resultats));


                $cmd2           = "cd ".$storage['path']." && du -s . | awk '{print $1}'";
                $used_by_backup = $ssh->exec($cmd2);

                $data                                                   = [];
                $data['backup_storage_space']['id_backup_storage_area'] = $storage['id'];
                $data['backup_storage_space']['date']                   = date('Y-m-d H:i:s');
                $data['backup_storage_space']['size']                   = $results['1'];
                $data['backup_storage_space']['used']                   = $results['2'];
                $data['backup_storage_space']['available']              = $results['3'];
                $data['backup_storage_space']['percent']                = substr(trim($results['4']), 0, -1);
                $data['backup_storage_space']['backup']                 = trim($used_by_backup);

                if (!$db->sql_save($data)) {


                    debug($cmd."\n");
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

    public function delete($param)
    {
        $id_backup_storage_area = $param[0];
        $db                     = $this->di['db']->sql(DB_DEFAULT);
        $sql                    = "DELETE FROM  backup_storage_area WHERE id ='".$id_backup_storage_area."'";

        $db->sql_query($sql);
        header("location: ".LINK."StorageArea/");
    }

    public function menu($param)
    {
        if (empty($param[0])) {
            $param[0] = "listStorage";
        }


        $data['menu'] = $param[0];

        $this->set("data", $data);
    }
}