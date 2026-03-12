<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Trait responsible for file workflows.
 *
 * This trait belongs to the PmaControl application layer and documents the
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
trait File
{

/**
 * Handle file state through `compressAndCrypt`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file Input value for `file`.
 * @phpstan-param mixed $file
 * @psalm-param mixed $file
 * @return mixed Returned value for compressAndCrypt.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::compressAndCrypt()
 * @example /fr/file/compressAndCrypt
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function compressAndCrypt($file)
    {


        $stats['normal'] = $this->getFileinfo($file);

        //compression
        $time_start      = microtime(true);
        $file_compressed = $this->compressFile($file);

        $stats['compressed']                   = $this->getFileinfo($file_compressed);
        $stats['compressed']['execution_time'] = round(microtime(true) - $time_start, 0);

        //chiffrement
        $time_start2                        = microtime(true);
        $file_crypted                       = $this->cryptFile($file_compressed);
        $stats['crypted']                   = $this->getFileinfo($file_crypted);
        $stats['crypted']['execution_time'] = round(microtime(true) - $time_start2, 0);
        $stats['file_path']                 = $file_crypted;

        return $stats;
    }

/**
 * Retrieve file state through `getFileinfo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $filename Input value for `filename`.
 * @phpstan-param mixed $filename
 * @psalm-param mixed $filename
 * @return mixed Returned value for getFileinfo.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getFileinfo()
 * @example /fr/file/getFileinfo
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getFileinfo($filename)
    {
        $data['size'] = filesize($filename);
        $data['md5']  = md5_file($filename);

        return $data;
    }

/**
 * Handle file state through `cryptFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file_name Input value for `file_name`.
 * @phpstan-param mixed $file_name
 * @psalm-param mixed $file_name
 * @return mixed Returned value for cryptFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::cryptFile()
 * @example /fr/file/cryptFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cryptFile($file_name)
    {

        $this->view = false;
        $chiffre    = new Chiffrement(CRYPT_KEY);
        $chiffre->chiffre_fichier($file_name);
        return $file_name;
    }

/**
 * Handle file state through `decryptFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file_name Input value for `file_name`.
 * @phpstan-param mixed $file_name
 * @psalm-param mixed $file_name
 * @return mixed Returned value for decryptFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::decryptFile()
 * @example /fr/file/decryptFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function decryptFile($file_name)
    {


        $this->view = false;
        $chiffre    = new Chiffrement(CRYPT_KEY);
        $chiffre->dechiffre_fichier($file_name);

        return $file_name;
    }

/**
 * Handle file state through `compressFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $path_file Input value for `path_file`.
 * @phpstan-param mixed $path_file
 * @psalm-param mixed $path_file
 * @return mixed Returned value for compressFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::compressFile()
 * @example /fr/file/compressFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function compressFile($path_file)
    {
        $path      = pathinfo($path_file)['dirname'];
        $file_name = pathinfo($path_file)['basename'];

        shell_exec("cd ".$path." && nice gzip ".$file_name);

        return $path."/".$file_name.".gz";
    }

/**
 * Handle file state through `unCompressFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $path_file Input value for `path_file`.
 * @phpstan-param mixed $path_file
 * @psalm-param mixed $path_file
 * @return mixed Returned value for unCompressFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::unCompressFile()
 * @example /fr/file/unCompressFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function unCompressFile($path_file)
    {

        $path      = pathinfo($path_file)['dirname'];
        $file_name = pathinfo($path_file)['basename'];

        shell_exec("cd ".$path." && nice gzip -d ".$file_name);

        return substr($path_file, 0, -3);
    }

/**
 * Handle file state through `unCompressAndUnCrypt`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file_uncrypted Input value for `file_uncrypted`.
 * @phpstan-param mixed $file_uncrypted
 * @psalm-param mixed $file_uncrypted
 * @param bool $is_crypted Input value for `is_crypted`.
 * @phpstan-param bool $is_crypted
 * @psalm-param bool $is_crypted
 * @return mixed Returned value for unCompressAndUnCrypt.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::unCompressAndUnCrypt()
 * @example /fr/file/unCompressAndUnCrypt
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function unCompressAndUnCrypt($file_uncrypted, $is_crypted = "1")
    {


        Debug::debug($file_uncrypted, "file_uncrypted");

        //déchiffrement

        if ($is_crypted === "1") {
            $time_start2        = microtime(true);
            $stats['uncrypted'] = $this->getFileinfo($file_uncrypted);
            $file_compressed    = $this->decryptFile($file_uncrypted);

            $stats['uncrypted']['execution_time'] = round(microtime(true) - $time_start2, 0);
        } else {
            $file_compressed = $file_uncrypted;
        }


        Debug::debug($file_compressed, "file_compressed");

        //décompression
        $stats['uncompressed'] = $this->getFileinfo($file_compressed);
        $time_start            = microtime(true);
        $normal                = $this->unCompressFile($file_compressed);


        $stats['uncompressed']['execution_time'] = round(microtime(true) - $time_start, 0);
        $stats['file_path']                      = $normal;
        $stats['normal']                         = $this->getFileinfo($normal);


        Debug::debug($normal, "file_usage");


        return $stats;
    }
}
