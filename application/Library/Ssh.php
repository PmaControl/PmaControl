<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use \App\Library\Chiffrement;
use \App\Library\Debug;

class Ssh
{

    static function formatPrivateKey($key)
    {
        $key = str_replace('\n', "", $key);
        $key = str_replace("\n", "", $key);
        $key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $key);
        $key = str_replace("-----END RSA PRIVATE KEY-----", "", $key);

        return $key;
    }

    static function connect($ip, $port = 22, $user, $password)
    {

        $ssh = new SSH2($ip);
        $rsa = new RSA();

        $login_successfull = true;


        // debug(Chiffrement::decrypt($key['private_key']));




        $private_key = self::formatPrivateKey($password);

        if ($rsa->loadKey($private_key) === false) {
            $login_successfull = false;
            Debug::debug("private key loading failed!");
        }

        if (!$ssh->login($user, $rsa)) {
            Debug::debug("Login Failed");
            $login_successfull = false;
        }

        $msg = ($login_successfull) ? "Successfull" : "Failed";
        $ret = "Connection to server (".$ip.":22) : ".$msg;

        Debug::debug($ret);

        return $ssh;
    }

    static function close()
    {
        
    }

    static function isValid($pubkeyssh)
    {
        // check public key

        //Debug::$debug = true;

        Debug::debug(str_repeat("#", 80));
        Debug::debug($pubkeyssh);

        if (file_exists($pubkeyssh)) {

            Debug::debug("get key from from file !");
            $pubkeyssh = file_get_contents($pubkeyssh);
        }

        Debug::debug($pubkeyssh, "public key");


        $path_puplic_key = TMP."trash/key".uniqid();

        file_put_contents($path_puplic_key, $pubkeyssh."\n");


        //sleep(10);
        Debug::debug($path_puplic_key, "PATH of key");


        shell_exec("chmod 600 ".$path_puplic_key);


        echo "\n".file_get_contents($path_puplic_key)."\n\n";

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

                //throw new \Exception("PMACTRL-145 : ".$error);
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
}