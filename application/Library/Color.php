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
        $hex = self::setFontColor($type);
        $ret = hex2bin('ffffff') ^ hex2bin($hex);
        return bin2hex($ret);

        //return $hex['background'];
    }

    public function testColor($param)
    {
        $text = $param[0];

        echo $text." : #".$this->setFontColor($text)." : #".$this->setBackgroundColor($text)."\n";
    }
}