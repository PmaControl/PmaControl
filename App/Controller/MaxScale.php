<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Table;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;


class MaxScale extends Controller {

    public function index() {

        $this->title = "MaxScale";

    }



}