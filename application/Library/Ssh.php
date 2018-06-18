<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Library;


use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

use App\Library\Chiffrement;


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
}