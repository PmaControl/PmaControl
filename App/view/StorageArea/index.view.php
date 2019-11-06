<?php

/*
if (($data['menu'] == "listStorage" && !empty($data['cpt']) || $data['menu'] != "listStorage")  || $data['menu'] == "index") {
    \Glial\Synapse\FactoryController::addNode("StorageArea", $data['menu']);
    
}*/

\Glial\Synapse\FactoryController::addNode("StorageArea", $data['menu']);