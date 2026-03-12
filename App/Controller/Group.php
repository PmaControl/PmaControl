<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Acl\Acl;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for group workflows.
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
class Group extends Controller {

/**
 * Render group state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/group/index
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

        $data['alias'] = $acl->getAlias();




        $parsed = parse_ini_file($acl->getPathIniFile(), true);


        $data['allow'] = $parsed['allow'];
        $data['deny'] = $parsed['deny'];
        $data['export'] = $parsed;

        foreach ($data['alias'] as $key => $alias) {
            
        }




        $this->set('data', $data);
    }

}

