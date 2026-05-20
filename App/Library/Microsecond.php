<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;


/**
 * Class responsible for microsecond workflows.
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
class Microsecond
{

/**
 * Handle microsecond state through `date`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for date.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::date()
 * @example /fr/microsecond/date
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function date() {
        // Créer un objet DateTime avec le temps actuel
        $dateTime = new \DateTime();
    
        // Ajouter les microsecondes actuelles
        $microseconds = sprintf("%06d", ($dateTime->format('u')));
        // Formatter la date et le temps avec les microsecondes
        $formattedDateTime = $dateTime->format("Y-m-d H:i:s") . '.' . $microseconds;
        return $formattedDateTime;
    }

/**
 * Handle microsecond state through `timestamp`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for timestamp.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::timestamp()
 * @example /fr/microsecond/timestamp
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function timestamp() {
        // Obtenir le microtime comme un float
        $microtime = microtime(true);
    
        // Convertir le float en un entier en microsecondes
        $timestampInMicroseconds = (int) ($microtime * 1000000);
        return $timestampInMicroseconds;
    }

/**
 * Handle microsecond state through `tsToDate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $timestampInMicroseconds Input value for `timestampInMicroseconds`.
 * @phpstan-param mixed $timestampInMicroseconds
 * @psalm-param mixed $timestampInMicroseconds
 * @return mixed Returned value for tsToDate.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::tsToDate()
 * @example /fr/microsecond/tsToDate
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function tsToDate($timestampInMicroseconds) {
        // Convertir le timestamp en microsecondes en secondes (float)
        $seconds = $timestampInMicroseconds / 1000000;
    
        // Créer un objet DateTime à partir du timestamp en secondes
        $dateTime = \DateTime::createFromFormat('U.u', number_format($seconds, 6, '.', ''));
    
        // Formater la date et l'heure pour inclure les microsecondes
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s.u');
    
        return $formattedDateTime;
    }
}
