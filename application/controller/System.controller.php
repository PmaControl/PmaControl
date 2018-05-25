<?php

use Glial\Synapse\Controller;

class System extends Controller
{
    
    function timeOut($params)
    {
        $this->view = false;
        
        $controller = $params[0];
        $action = $params[1];
        $param = $params[2];
        
        
        \Glial\Synapse\FactoryController::rootNode($controller, $action, $param);
        
    }
    
    
}