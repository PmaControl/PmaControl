<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for version workflows.
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
class Version extends Controller
{

/**
 * Render version state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/version/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {

        $name         = __("Version");                   
        $this->title  = '<i class="fa fa-info-circle" style="font-size:32px"></i> '.$name;
        $this->ariane = '> <i class="fa fa-question" style="font-size:16px" aria-hidden="true"></i> Help > <i class="fa fa-info-circle" style="font-size:16px"></i> '
            .$name;


        $db = Sgbd::sql(DB_DEFAULT);
        $sql ="SELECT * FROM `version`";
        $data['version'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);


    }
}
