<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


foreach ($data['list'] as $database => $id_mysql_server) {
    \Glial\Synapse\FactoryController::addNode("Mysql", "mpd", array($id_mysql_server, $database));
}
