<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Acl\Acl as Droit;

/**
 * Class responsible for acl workflows.
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
class Acl extends Controller {

/**
 * Render acl state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/acl/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        $this->title = '<span class="glyphicon glyphicon glyphicon-user"></span> ' . __("Groups");
        $this->ariane = ' > <a href⁼"' . LINK . '">' . '<span class="glyphicon glyphicon glyphicon-cog" style="font-size:12px">'
                . '</span> ' . __("Settings") . '</a> >' . $this->title;


        $acl = $this->di['acl'];

        $data['export'] = $acl->exportCombinaison();


        $this->set('data', $data);
    }

/**
 * Handle acl state through `check`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for check.
 * @phpstan-return void
 * @psalm-return void
 * @see self::check()
 * @example /fr/acl/check
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function check() {
        $this->view = false;

        $acl = new Droit(CONFIG . "acl.config.ini");

        echo $acl;
    }

}

