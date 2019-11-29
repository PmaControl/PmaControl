<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use App\Library\Chiffrement;
use App\Library\Debug;

class Transfer
{
    /* used for link MySQL */
    static $db;

    static public function setDb($db)
    {
        self::$db = $db;
    }

    public function sendFile($id_backup_storage_area, $src, $dst)
    {

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT *, b.id FROM backup_storage_area a
                INNER JOIN ssh_key b ON a.id_ssh_key = b.id
                WHERE a.id = ".$id_backup_storage_area;
        $res = $db->sql_query($sql);



        while ($ob = $db->sql_fetch_object($res)) {

            $start = microtime(true);

            if (substr($dst, 0, 1) === "/") {
                throw new \Exception("PMACTRL-576 : The path must be relative, we use the main path of storage area : '".$ob->path."'");
                exit;
            }

            $dst = $ob->path."/".$dst;



            if (!empty($ob->private_key)) {
                $pv_key = Chiffrement::decrypt($ob->private_key);
            }

            $sftp = new SFTP($ob->ip);
            $ssh  = new SSH2($ob->ip);

            // priorité a la clef privé si les 2 sont remplie
            if (!empty($pv_key)) {
                $key = new RSA();
                $key->loadKey($pv_key);
            }

            if (!$sftp->login($ob->user, $key)) {
                echo 'SCP Login Failed';
                return false;
            }

            if (!$ssh->login($ob->user, $key)) {
                echo 'SSH Login Failed';
                return false;
            }


            $file_name = pathinfo($dst)['basename'];
            $dst_dir   = pathinfo($dst)['dirname'];


            Debug::debug(pathinfo($dst), "Path_info");

            $ssh->exec("mkdir -p ".$dst_dir);

            Debug::debug($dst_dir, "mkdir -p");


            $db->sql_close();
            $sftp->put($dst, $src, SFTP::SOURCE_LOCAL_FILE);
            $data['execution_time'] = round(microtime(true) - $start, 0);


            $data['size'] = $sftp->size($dst);

            $md5 = $ssh->exec("md5sum ".$dst);

            $data['md5']      = explode(" ", $md5)[0];
            $data['pathfile'] = $dst;


            Debug::debug($data, "data");

            $files = $sftp->rawlist($dst_dir);


            Debug::debug($files, "files");

            foreach ($files as $file) {
                if ($file['filename'] === $file_name) {

                    return $data;
                }
            }

            return false;
        } //end while
    }

    private static function getFile($server, $src, $dst)
    {
        $start = microtime(true);

        if (substr($dst, 0, 1) != "/") {
            throw new \Exception("PMACTRL-577 : The path must be fully qualified");
            exit;
        }




        $sftp = new SFTP($server['hostname']);
        $ssh  = new SSH2($server['hostname']);


        // priorité a la clef privé si les 2 sont remplie
        if (!empty($server['private_key'])) {
            $key = new RSA();
            $key->loadKey($server['private_key']);
        }

        if (!$sftp->login($server['user'], $key)) {
            echo 'SCP Login Failed';
            return false;
        }

        if (!$ssh->login($server['user'], $key)) {
            echo 'SSH Login Failed';
            return false;
        }


        $sftp->get($src, $dst);
        $data['execution_time'] = round(microtime(true) - $start, 0);


        $data['size'] = $sftp->size($src);

        $md5 = $ssh->exec("md5sum ".$src." 2>1 >> /dev/null");

        $data['md5'] = explode(" ", $md5)[0];

        return $data;
    }

    static public function getFileFromMysql($id_mysql_server, $src, $dst)
    {

        $sql = "select b.user,b.private_key,b.`type`,c.ip  , c.ssh_port
            from link__mysql_server__ssh_key a
        INNER JOIN ssh_key b ON a.id_ssh_key = b.id
        INNER JOIN mysql_server c on c.id = a.id_mysql_server
        where id_mysql_server = ".$id_mysql_server." and active='1' LIMIT 1;";

        $res = self::$db->sql_query($sql);

        while ($ob = self::$db->sql_fetch_object($res)) {

            $server                = array();
            $server['hostname']    = $ob->ip;
            $server['port']        = $ob->ssh_port;
            $server['user']        = $ob->user;
            $server['private_key'] = Chiffrement::decrypt($ob->private_key, CRYPT_KEY); ;

            $ret = self::getFile($server, $src, $dst);
        }

        return $ret;
    }
}