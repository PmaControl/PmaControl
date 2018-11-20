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

trait Scp {

    public function sendFile($id_backup_storage_area, $src, $dst) {

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT *, b.id FROM backup_storage_area a 
                INNER JOIN ssh_key b ON a.id_ssh_key = b.id
                WHERE a.id = " . $id_backup_storage_area;
        $res = $db->sql_query($sql);



        while ($ob = $db->sql_fetch_object($res)) {

            $start = microtime(true);

            if (substr($dst, 0, 1) === "/") {
                throw new \Exception("PMACTRL-576 : The path must be relative, we use the main path of storage area : '" . $ob->path . "'");
                exit;
            }

            $dst = $ob->path . "/" . $dst;

            
            
            if (!empty($ob->private_key)) {
                $pv_key = Chiffrement::decrypt($ob->private_key);
            }

            $sftp = new SFTP($ob->ip);
            $ssh = new SSH2($ob->ip);

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
            $dst_dir = pathinfo($dst)['dirname'];


            $ssh->exec("mkdir -p " . $dst_dir);

            $sftp->put($dst, $src, SFTP::SOURCE_LOCAL_FILE);
            $data['execution_time'] = round(microtime(true) - $start, 0);


            $data['size'] = $sftp->size($dst);

            $md5 = $ssh->exec("md5sum " . $dst);

            $data['md5'] = explode(" ", $md5)[0];
            $data['pathfile'] = $dst;



            $files = $sftp->rawlist($dst_dir);

            foreach ($files as $file) {
                if ($file['filename'] === $file_name) {

                    return $data;
                }
            }

            return false;
        } //end while
    }

    private function getFile($id_backup_storage_area, $src, $dst) {

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT *, b.id FROM backup_storage_area a 
                INNER JOIN ssh_key b ON a.id_ssh_key = b.id
            WHERE a.id = " . $id_backup_storage_area;
        $res = $db->sql_query($sql);

        Crypt::$key = CRYPT_KEY;

        while ($ob = $db->sql_fetch_object($res)) {

            $start = microtime(true);

            if (substr($dst, 0, 1) != "/") {
                throw new \Exception("PMACTRL-577 : The path must be fully qualified");
                exit;
            }

            
            if (!empty($ob->private_key)) {
                $pv_key = Chiffrement::decrypt($ob->private_key);
            }

            $sftp = new SFTP($ob->ip);
            $ssh = new SSH2($ob->ip);

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
            

            $sftp->get($src, $dst);
            $data['execution_time'] = round(microtime(true) - $start, 0);


            $data['size'] = $sftp->size($src);

            $md5 = $ssh->exec("md5sum " . $src . " 2>1 >> /dev/null");

            $data['md5'] = explode(" ", $md5)[0];

            return $data;
        } //end while
    }

}
