<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use \Glial\Security\Crypt\Crypt;



/**
 * Class responsible for chiffrement workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Chiffrement
{
    const CHIFFRE   = 1;
    const DECHIFFRE = 2;

/**
 * Stores `$tmpfile` for tmpfile.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $tmpfile;
/**
 * Stores `$object` for object.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $object;

/**
 * Handle chiffrement state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $clef Input value for `clef`.
 * @phpstan-param mixed $clef
 * @psalm-param mixed $clef
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @see self::__construct()
 * @example /fr/chiffrement/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct($clef)
    {

        $this->tmpfile = '/tmp/'.uniqid().".pmactrl";

        $cipher = new AES();
        $cipher->setKey($clef);
        //$cipher->setIV(Random::string($cipher->getBlockLength() >> 3));

        $this->object = $cipher;
    }

/**
 * Handle chiffrement state through `chiffre`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $plaintext Input value for `plaintext`.
 * @phpstan-param mixed $plaintext
 * @psalm-param mixed $plaintext
 * @return mixed Returned value for chiffre.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::chiffre()
 * @example /fr/chiffrement/chiffre
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function chiffre($plaintext)
    {

        return base64_encode($this->object->encrypt($plaintext));
    }

/**
 * Handle chiffrement state through `dechiffre`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $plaintext Input value for `plaintext`.
 * @phpstan-param mixed $plaintext
 * @psalm-param mixed $plaintext
 * @return mixed Returned value for dechiffre.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::dechiffre()
 * @example /fr/chiffrement/dechiffre
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dechiffre($plaintext)
    {

        return $this->object->decrypt(base64_decode($plaintext));
    }

/**
 * Handle chiffrement state through `loop`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @param mixed $chiffre Input value for `chiffre`.
 * @phpstan-param mixed $chiffre
 * @psalm-param mixed $chiffre
 * @return mixed Returned value for loop.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::loop()
 * @example /fr/chiffrement/loop
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle chiffrement state through `chiffre_fichier`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @return void Returned value for chiffre_fichier.
 * @phpstan-return void
 * @psalm-return void
 * @see self::chiffre_fichier()
 * @example /fr/chiffrement/chiffre_fichier
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function chiffre_fichier($src)
    {

        $this->loop($src, self::CHIFFRE);
    }

/**
 * Handle chiffrement state through `dechiffre_fichier`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @return void Returned value for dechiffre_fichier.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dechiffre_fichier()
 * @example /fr/chiffrement/dechiffre_fichier
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dechiffre_fichier($src)
    {

        $this->loop($src, self::DECHIFFRE);
    }

/**
 * Handle chiffrement state through `fwrite_stream`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $fp Input value for `fp`.
 * @phpstan-param mixed $fp
 * @psalm-param mixed $fp
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return mixed Returned value for fwrite_stream.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::fwrite_stream()
 * @example /fr/chiffrement/fwrite_stream
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle chiffrement state through `encrypt`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $msg Input value for `msg`.
 * @phpstan-param mixed $msg
 * @psalm-param mixed $msg
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for encrypt.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::encrypt()
 * @example /fr/chiffrement/encrypt
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function encrypt($msg, $password = CRYPT_KEY)
    {

        //Crypt::$key = $password;
        $msg_chiffre = Crypt::encrypt($msg, $password);
        Crypt::$key  = CRYPT_KEY;

        return $msg_chiffre;
    }

/**
 * Handle chiffrement state through `decrypt`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $password_crypted Input value for `password_crypted`.
 * @phpstan-param mixed $password_crypted
 * @psalm-param mixed $password_crypted
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for decrypt.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::decrypt()
 * @example /fr/chiffrement/decrypt
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function decrypt($password_crypted, $password = CRYPT_KEY)
    {
        //;

        if (empty($password)) {
            throw new \Exception("PMACTRL-478 : Empty password");
        }

        $en_clair   = Crypt::decrypt($password_crypted, $password);
        Crypt::$key = CRYPT_KEY;

        return $en_clair;
    }
}

