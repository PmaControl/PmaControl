<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;
use \App\Library\Debug;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;

class Ssh
{
    /*
     * les connection active ssh
     */
    static $ssh    = array();
    /*
     * Server with active ssh key 
     */
    static $server = array();

    static function formatPrivateKey($key)
    {
        $key = str_replace('\n', "", $key);
        $key = str_replace("\n", "", $key);
        $key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $key);
        $key = str_replace("-----END RSA PRIVATE KEY-----", "", $key);

        return $key;
    }
    /*
     * 
     * return un objet de type phpseclib\Net\SSH2
     */

    static function connect($ip, $port = 22, $user, $password)
    {

        /*
          $debug = Debug::$debug;
          Debug::$debug = false;
         */

        Debug::Debug($ip, "ip");
        Debug::Debug($port, "port");
        Debug::Debug($user, "user");
        Debug::Debug($password, "password / private key");

        $ssh               = new SSH2($ip, $port, 30);
        $login_successfull = true;
        $rsa               = PublicKeyLoader::load($password);

        if (PublicKeyLoader::load($password) === false) {
            $login_successfull = false;
            Debug::debug("private key loading failed!");
        }

        if (!$ssh->login($user, $rsa)) {
            Debug::debug("Login Failed");
            $login_successfull = false;
            return false;
        }

        if ($login_successfull === true) {
            return $ssh;
        } else {
            return false;
        }
    }

    static function close()
    {
        
    }

    static function isValid($pubkeyssh)
    {
        Debug::debug(str_repeat("#", 80));
        //Debug::debug($pubkeyssh);

        if (file_exists($pubkeyssh)) {

            Debug::debug("get key from from file !");
            $pubkeyssh = file_get_contents($pubkeyssh);
        }

        Debug::debug($pubkeyssh, "public key");

        // remove ^M (happen with php 7.3 ? )
        $pubkeyssh = str_ireplace("\x0D", "", $pubkeyssh);

        $path_puplic_key = TMP."trash/key".uniqid();
        file_put_contents($path_puplic_key, $pubkeyssh."\n");

        Debug::debug($path_puplic_key, "PATH of key");
        shell_exec("chmod 600 ".$path_puplic_key);

        $file_error = TMP."trash/generate_key.error";

        if (file_exists($file_error)) {
            unlink($file_error);
        }

        $cmd = "ssh-keygen -l -f ".$path_puplic_key."  2> ".$file_error;
        Debug::debug($cmd, "CMD");

        $result = shell_exec($cmd);

        Debug::debug($result, "RESULT");

        if (file_exists($file_error)) {

            $error = file_get_contents($file_error);

            if (!empty($error)) {
                Debug::debug($error, "[ERROR]");
            }
            unlink($file_error);
        }

        unlink($path_puplic_key);

        if (empty($result)) {


            return false;
        } else {

            $elems = explode(" ", $result);

            $data['bit']    = $elems[0];
            $data['pubkey'] = $elems[1];
            $data['type']   = substr(end($elems), 1, -2);

            unset($elems[count($elems) - 1]);
            unset($elems[0]);
            unset($elems[1]);

            $data['name'] = implode(" ", $elems);
        }

        Debug::debug($data);

        return $data;
    }

    static function generate($type, $bit)
    {

        $key = TMP."trash/key".uniqid();

        $cmd = "ssh-keygen -t ".$type." -C 'PmaControl' -N \"\" -f ".$key." -b ".$bit;

        Debug::debug($cmd);

        shell_exec($cmd);

        $data['key_priv'] = trim(file_get_contents($key));
        $data['key_pub']  = trim(file_get_contents($key.".pub"));

        unlink($key);
        unlink($key.".pub");

        return $data;
    }

    static function put($server, $port, $login, $private_key, $src, $dst)
    {

        $start = microtime(true);

        $sftp = new SFTP($server, $port);
        $ssh  = new SSH2($server, $port);

        // priorité a la clef privé si les 2 sont remplie
        if (!empty($private_key)) {
            $rsa = PublicKeyLoader::load($private_key);
        }

        if (!$sftp->login($login, $key)) {
            Debug::debug("SCP Login Failed");
            return false;
        }

        if (!$ssh->login($login, $key)) {
            Debug::debug('SSH Login Failed');
            return false;
        }

        $file_name = pathinfo($dst)['basename'];
        $dst_dir   = pathinfo($dst)['dirname'];

        $ssh->exec("mkdir -p ".$dst_dir);

        $sftp->put($dst, $src, SFTP::SOURCE_LOCAL_FILE);
        $data['execution_time'] = round(microtime(true) - $start, 0);
        $data['size']           = $sftp->size($dst);

        $md5 = $ssh->exec("md5sum ".$dst);

        $data['md5']      = explode(" ", $md5)[0];
        $data['pathfile'] = $dst;

        $files = $sftp->rawlist($dst_dir);

        foreach ($files as $file) {
            if ($file['filename'] === $file_name) {

                return $data;
            }
        }

        return false;
    }

    static function spaceAvailable($param)
    {
        
    }

    static function ssh($id_mysql_server)
    {
        $server = self::getSsh($id_mysql_server);

        if ($server === false) {
            return false;
        }

        $ssh = self::connect($server['ip'], $server['port'], $server['user'], Crypt::decrypt($server['private_key'], CRYPT_KEY));

        if ($ssh) {

            Debug::debug($ssh->exec("ls -l"), "ls -l");

            self::$ssh[$id_mysql_server] = $ssh;
            return $ssh;
        }

        return false;
    }

    static function getSsh($id_mysql_server)
    {
        if (empty(self::$server[$id_mysql_server])) {

            $db  = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT a.id, a.ip, c.user, a.ssh_port as port,c.public_key,c.private_key, c.type, c.fingerprint   FROM mysql_server a
          LEFT JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
          INNER JOIN ssh_key c ON b.id_ssh_key = c.id
          WHERE `active` = 1";

            Debug::sql($sql);

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$server[$ob['id']] = $ob;
            }
        }

        if (empty(self::$server[$id_mysql_server])) {
            return false;
        }

        return self::$server[$id_mysql_server];
    }
}