<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;


class Country
{

    public static function getFlag($flag)
    {
        $letters = mb_str_split($flag);

        if (count($letters) != 2) {
            throw new \Exception("Should be iso code with exatly 2 letters");   
        }

        $letter_flag= array();
        foreach($letters as $letter) {
            $letter_flag[] = self::getLetter($letter);
        }

        return implode("", $letter_flag);
    }


    static private function getLetter($letter)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql ="SELECT emotj FROM country_flag WHERE letter in ('".$letter."');";
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            return $ob->emotj;
        }
    }




}