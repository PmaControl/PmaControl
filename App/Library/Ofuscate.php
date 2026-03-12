<?php

namespace App\Library;

/**
 * Class responsible for ofuscate workflows.
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
class Ofuscate
{

/**
 * Handle ofuscate state through `ip`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $ip Input value for `ip`.
 * @phpstan-param mixed $ip
 * @psalm-param mixed $ip
 * @return mixed Returned value for ip.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::ip()
 * @example /fr/ofuscate/ip
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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


/**
 * Handle ofuscate state through `name`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $display_name Input value for `display_name`.
 * @phpstan-param string $display_name
 * @psalm-param string $display_name
 * @return void Returned value for name.
 * @phpstan-return void
 * @psalm-return void
 * @see self::name()
 * @example /fr/ofuscate/name
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function name(string $display_name) {


        $segments = explode('.', $display_name);


        foreach ($segments as $index => $segment) {
            // Appliquer le décalage, s'assurer que le résultat reste dans la plage 0-255
            $newSegment = ($segment + $crcs[$index]) % 255;
            $obfuscatedSegments[] = $newSegment;
        }

    }


}
