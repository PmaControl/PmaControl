<?php

namespace App\Library;

class Ofuscate
{

    static function ip($ip) {

        return $ip;
        /*
        if (true !== filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }*/

        $md5 = md5(CRYPT_KEY);
        $parts = str_split($md5, 8);

        // Calcule le CRC32 pour chaque partie
        $crcs = array_map(function ($part) {
            return crc32($part);
        }, $parts);

        $obfuscatedSegments = [];
        $segments = explode('.', $ip);

        foreach ($segments as $index => $segment) {
            // Appliquer le décalage, s'assurer que le résultat reste dans la plage 0-255
            $newSegment = ($segment + $crcs[$index]) % 255;
            $obfuscatedSegments[] = $newSegment;
        }

        // Reconstruire l'IP obfusquée
        return implode('.', $obfuscatedSegments);
    }


    static public function name(string $display_name) {


        $segments = explode('.', $display_name);


        foreach ($segments as $index => $segment) {
            // Appliquer le décalage, s'assurer que le résultat reste dans la plage 0-255
            $newSegment = ($segment + $crcs[$index]) % 255;
            $obfuscatedSegments[] = $newSegment;
        }

    }


}