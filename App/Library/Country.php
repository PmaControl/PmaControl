<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for country workflows.
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
class Country
{

/**
 * Retrieve country state through `getFlag`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $flag Input value for `flag`.
 * @phpstan-param mixed $flag
 * @psalm-param mixed $flag
 * @return mixed Returned value for getFlag.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getFlag()
 * @example /fr/country/getFlag
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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


/**
 * Retrieve country state through `getLetter`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $letter Input value for `letter`.
 * @phpstan-param mixed $letter
 * @psalm-param mixed $letter
 * @return mixed Returned value for getLetter.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getLetter()
 * @example /fr/country/getLetter
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
