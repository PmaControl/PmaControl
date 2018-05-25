<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

trait File {

    private function compressAndCrypt($file) {


        $stats['normal'] = $this->getFileinfo($file);

        //compression
        $time_start = microtime(true);
        $file_compressed = $this->compressFile($file);

        $stats['compressed'] = $this->getFileinfo($file_compressed);
        $stats['compressed']['execution_time'] = round(microtime(true) - $time_start, 0);

        //chiffrement
        $time_start2 = microtime(true);
        $file_crypted = $this->cryptFile($file_compressed);
        $stats['crypted'] = $this->getFileinfo($file_crypted);
        $stats['crypted']['execution_time'] = round(microtime(true) - $time_start2, 0);
        $stats['file_path'] = $file_crypted;

        return $stats;
    }

    private function getFileinfo($filename) {
        $data['size'] = filesize($filename);
        $data['md5'] = md5_file($filename);

        return $data;
    }

    public function cryptFile($file_name) {

        $this->view = false;
        $chiffre = new Chiffrement(CRYPT_KEY);
        $chiffre->chiffre_fichier($file_name);
        return $file_name;
    }

    public function decryptFile($file_name) {


        $this->view = false;
        $chiffre = new Chiffrement(CRYPT_KEY);
        $chiffre->dechiffre_fichier($file_name);

        return $file_name;
    }

    public function compressFile($path_file) {
        $path = pathinfo($path_file)['dirname'];
        $file_name = pathinfo($path_file)['basename'];

        shell_exec("cd " . $path . " && nice gzip " . $file_name);

        return $path . "/" . $file_name . ".gz";
    }

    public function unCompressFile($path_file) {

        $path = pathinfo($path_file)['dirname'];
        $file_name = pathinfo($path_file)['basename'];

        shell_exec("cd " . $path . " && nice gzip -d " . $file_name);

        return substr($path_file, 0, -3);
    }

    private function unCompressAndUnCrypt($file_uncrypted) {


        //déchiffrement
        $time_start2 = microtime(true);
        $stats['uncrypted'] = $this->getFileinfo($file_uncrypted);
        $file_compressed = $this->decryptFile($file_uncrypted);

        $stats['uncrypted']['execution_time'] = round(microtime(true) - $time_start2, 0);
        
        
        //décompression
        $stats['uncompressed'] = $this->getFileinfo($file_compressed);
        $time_start = microtime(true);
        $normal = $this->unCompressFile($file_compressed);

        
        $stats['uncompressed']['execution_time'] = round(microtime(true) - $time_start, 0);

        $stats['file_path'] = $normal;


        $stats['normal'] = $this->getFileinfo($normal);
        


        return $stats;
    }

}
