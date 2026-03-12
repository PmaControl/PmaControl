<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace App\Library;

/**
 * Class responsible for json workflows.
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
class Json
{

/**
 * Retrieve json state through `getDataFromFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $configFile Input value for `configFile`.
 * @phpstan-param mixed $configFile
 * @psalm-param mixed $configFile
 * @return mixed Returned value for getDataFromFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getDataFromFile()
 * @example /fr/json/getDataFromFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getDataFromFile($configFile)
    {
        if (empty($configFile) || !file_exists($configFile)) {
            throw new \Exception('PMACTRL-255 : The file '.$configFile.' doesn\'t exit !');
        }

        $json = file_get_contents($configFile);

        return self::isJson($json);
    }


    
/**
 * Handle json state through `isJson`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $json Input value for `json`.
 * @phpstan-param mixed $json
 * @psalm-param mixed $json
 * @return mixed Returned value for isJson.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::isJson()
 * @example /fr/json/isJson
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function isJson($json)
    {
        $array = json_decode($json, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $array;
                break;
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $error = 'Unknown error';
                break;
        }

        throw new \Exception("PMACTRL-254 : JSON : ".$error, 80);
    }
}
