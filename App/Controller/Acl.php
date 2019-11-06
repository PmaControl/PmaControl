<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Acl\Acl as Droit;

class Acl extends Controller {

    public function index() {
        $this->title = '<span class="glyphicon glyphicon glyphicon-user"></span> ' . __("Groups");
        $this->ariane = ' > <a href⁼"' . LINK . '">' . '<span class="glyphicon glyphicon glyphicon-cog" style="font-size:12px">'
                . '</span> ' . __("Settings") . '</a> >' . $this->title;


        $acl = $this->di['acl'];

        $data['export'] = $acl->exportCombinaison();


        $this->set('data', $data);
    }

    public function check() {
        $this->view = false;

        $acl = new Droit(CONFIG . "acl.config.ini");

        echo $acl;
    }

}
