<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Acl\Acl;

class Group extends Controller {

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
