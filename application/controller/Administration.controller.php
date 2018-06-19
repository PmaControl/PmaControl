<?php

use \Glial\Synapse\Controller;
use \Glial\Utility\Inflector;

class Administration extends Controller
{

    use \Glial\Neuron\Controller\Administration;
    public $module_group = "Administration";

    public function test()
    {
        $this->view = false;
        echo "main";
    }
}