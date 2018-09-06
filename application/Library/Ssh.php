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


        Debug::debug($pubkeyssh);

        if (file_exists($pubkeyssh)) {

            $pubkeyssh = file_get_contents($pubkeyssh);
        }

        Debug::debug($pubkeyssh, "public key");


        $path_puplic_key = "/tmp/".uniqid();

        file_put_contents($path_puplic_key, $pubkeyssh);



        $file_error = "/tmp/isvalid_error";

        if (file_exists($file_error)) {
            unlink($file_error);
        }

        $cmd = "ssh-keygen -l -f ".$path_puplic_key."  2>".$file_error;
        Debug::debug($cmd);

        if (file_exists($file_error)) {
            Debug::debug(file_get_contents($file_error), "[ERROR]");
            unlink($file_error);
        }

        $result = shell_exec($cmd);

        Debug::debug($result, "RESULT");

        unlink($path_puplic_key);

        if (empty($result)) {

            
            return false;
        } else {

            $elems = explode(" ", $result);

            $data['bit']    = $elems[0];
            $data['pubkey'] = $elems[1];
            $data['type']   = substr(end($elems), 1, -2);

            unset($elems[count($elems)-1]);
            unset($elems[0]);
            unset($elems[1]);

            $data['name'] = implode(" ",$elems);
        }

        Debug::debug($data);

        return $data;
    }
}