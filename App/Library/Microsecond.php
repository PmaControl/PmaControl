<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;


class Microsecond
{

    static function date() {
        // Créer un objet DateTime avec le temps actuel
        $dateTime = new \DateTime();
    
        // Ajouter les microsecondes actuelles
        $microseconds = sprintf("%06d", ($dateTime->format('u')));
        // Formatter la date et le temps avec les microsecondes
        $formattedDateTime = $dateTime->format("Y-m-d H:i:s") . '.' . $microseconds;
        return $formattedDateTime;
    }

    static function timestamp() {
        // Obtenir le microtime comme un float
        $microtime = microtime(true);
    
        // Convertir le float en un entier en microsecondes
        $timestampInMicroseconds = (int) ($microtime * 1000000);
        return $timestampInMicroseconds;
    }

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