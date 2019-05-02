<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Color
{

    static function setFontColor($type)
    {
        $hex = substr(md5($type), 0, 6);

        return $hex;

        //return $hex['background'];
    }

    static function setBackgroundColor($type)
    {
        /*
          $hex = self::setFontColor($type);
          $ret = hex2bin('ffffff') ^ hex2bin($hex);
          return bin2hex($ret);
         */

        $hex = self::setFontColor($type);
        return self::getFontColor($hex);

        //return $hex['background'];
    }

    public function testColor($param)
    {
        $text = $param[0];

        echo $text." : #".$this->setFontColor($text)." : #".$this->setBackgroundColor($text)."\n";
    }

    static public function getFontColor($b_color)
    {

        $white               = '#FFFFFF'; //couleur blanc
        $black               = '#000000'; //couleur noir
        $seuilCouleur        = 165; //seuil qui détermine l'intensité de couleur
        $opposite_red        = '#FFFF00'; //couleur de police pour fond rouge
        $opposite_yellow     = '#000CFF'; //couleur de police pour fond jaune
        $opposite_green      = '#0000FF'; //couleur de police pour fond vert
        $opposite_clear_blue = '#1900FF'; //couleur de police pour fond bleu clair
        $opposite_dark_blue  = '#EEFF00'; //couleur de police pour fond bleu foncé
        $opposite_purple     = '#FFFF00'; //couleur de police pour fond violet
        $tmp                 = 0;

        $r = hexdec(substr($b_color, 1, 2));
        $g = hexdec(substr($b_color, 3, 2));
        $b = hexdec(substr($b_color, 5, 2));

        $ecart1       = $r - $g;
        $ecart2       = $g - $b;
        $ecart3       = $r - $b;
        $limite_ecart = 120;
        $total        = $r + $g + $b;

        //on peut determiner si une couleur est essentiellement composée de noir ou blanc en regardant si l'écart entre les nombres est faible
        if (abs($ecart1) < $limite_ecart && abs($ecart2) < $limite_ecart && abs($ecart3) < $limite_ecart) {

            $limite = 420; //seuil qui détermine le niveau de noir ou blanc
            if ($total < $limite) {
                $f_color = $white;
            } else {
                $f_color = $black;
            }
        } else { //si une couleur domine plus que le noir et blanc
            $color    = array();
            $color[0] = $r;
            $color[1] = $g;
            $color[2] = $b;

            foreach ($color as $val) {
                if ($val > $seuilCouleur) {
                    $tmp++;
                } else {
                    $f_color = $white;
                }
            }

            //si la couleur rouge, vert ou bleu foncé domine
            if ($tmp == 1) {
                if ($r > $seuilCouleur) {//on determine si la couleur est rouge
                    $f_color = $opposite_red;
                }
                if ($g > $seuilCouleur) {//on determine si la couleur est verte
                    $f_color = $opposite_green;
                }
                if ($b > $seuilCouleur) {//on determine si la couleur est bleu foncée
                    $f_color = $opposite_dark_blue;
                }
            }

            //si la couleur jaune, bleu clair ou violet domine
            if ($tmp == 2) {
                if ($r > $seuilCouleur && $g > $seuilCouleur) {//on determine si la couleur est jaune
                    $f_color = $opposite_yellow;
                }
                if ($g > $seuilCouleur && $b > $seuilCouleur) {//on determine si la couleur est bleu claire
                    $f_color = $opposite_clear_blue;
                }
                if ($r > $seuilCouleur && $b > $seuilCouleur) {//on determine si la couleur est violet
                    $f_color = $opposite_purple;
                }
            }
        }
        return $f_color; //on retourne la couleur de police appropriée
    }

    static public function isDark()
    {
        //0.3*(couleur_rouge) + 0.59*(couleur_verte) +(0.11*(couleur_bleue)
        //est supérieur ou égal à 128
        //C'est une couleur claire ... sinon c'est une couleur foncée
    }
}