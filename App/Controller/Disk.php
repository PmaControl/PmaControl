<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction2;

/**
 * Class responsible for disk workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Disk extends Controller
{
/**
 * Retrieve disk state through `getData`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getData.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getData()
 * @example /fr/disk/getData
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getData($param)
    {
        Debug::parseDebug($param);

        $data = Extraction2::display(array("variables::"), array(1));

        $filtered = array_filter($data[1], function($value) {
            return is_string($value) && str_starts_with($value, '/');
        });

        Debug::debug($filtered, "GGG");
    }


/**
 * Handle disk state through `gg`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for gg.
 * @phpstan-return void
 * @psalm-return void
 * @see self::gg()
 * @example /fr/disk/gg
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function gg($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $data = $db->isSlave();

        echo json_encode($data, JSON_PRETTY_PRINT);
 


        //Debug::debug($data);
    }
}


