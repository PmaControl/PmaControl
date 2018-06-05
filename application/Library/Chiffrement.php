<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use phpseclib\Crypt\AES;
use phpseclib\Crypt\Random;
use \Glial\Security\Crypt\Crypt;


class Chiffrement
{
    const CHIFFRE   = 1;
    const DECHIFFRE = 2;

    var $tmpfile;
    var $object;

    public function __construct($clef)
    {

        $this->tmpfile = '/tmp/'.uniqid().".pmactrl";

        $cipher = new AES();
        $cipher->setKey($clef);
        //$cipher->setIV(Random::string($cipher->getBlockLength() >> 3));

        $this->object = $cipher;
    }

    public function chiffre($plaintext)
    {

        return base64_encode($this->object->encrypt($plaintext));
    }

    public function dechiffre($plaintext)
    {

        return $this->object->decrypt(base64_decode($plaintext));
    }

    private function loop($src, $chiffre = self::CHIFFRE)
    {

        if (file_exists($src)) {
            $handle = fopen($src, "r");
            $fp     = fopen($this->tmpfile, "w");

            if ($handle) {
                if ($fp) {
                    if ($chiffre === self::CHIFFRE) {

                        while (($buffer = fgets($handle, 4096)) !== false) {
                            fwrite($fp, $this->chiffre($buffer)."\n");
                        }
                    } else if ($chiffre === self::DECHIFFRE) {
                        while (($buffer = fgets($handle)) !== false) {
                            fwrite($fp, $this->dechiffre((rtrim($buffer))));
                        }
                    } else {
                        throw new \Exception("PMACTRL-452: Invalid argument", 80);
                    }

                    if (!feof($handle)) {
                        echo "Erreur: fgets() a échoué\n";
                    }
                    fclose($handle);
                    fclose($fp);

                    if (!unlink($src)) {
                        throw new \Exception("PMACTRL-453: Impossible to remove original file", 80);
                    }

                    if (copy($this->tmpfile, $src)) {
                        unlink($this->tmpfile);
                        return true;
                    } else {
                        throw new \Exception("PMACTRL-453: Impossible to move file", 80);
                    }
                }
            }
        }

        return false;
    }

    public function chiffre_fichier($src)
    {

        $this->loop($src, self::CHIFFRE);
    }

    public function dechiffre_fichier($src)
    {

        $this->loop($src, self::DECHIFFRE);
    }

    function fwrite_stream($fp, $string)
    {
        for ($written = 0; $written < strlen($string); $written += $fwrite) {
            $fwrite = fwrite($fp, substr($string, $written));
            if ($fwrite === false) {
                return $fwrite;
            }
        }
        return $written;
    }


    static function encrypt($password)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::encrypt($password);
        
        return $passwd;
    }


    static function decrypt($password_crypted)
    {
        Crypt::$key = CRYPT_KEY;
        $passwd     = Crypt::decrypt($password_crypted);
        
        return $passwd;
    }
    
    
}