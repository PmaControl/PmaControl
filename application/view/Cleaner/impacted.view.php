<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Cleaner", "menu", array($data['id_cleaner']));
echo '</div>';




\Glial\Synapse\FactoryController::addNode("mysql", "mpd", array($data['server_name'],$data['database'],$data['id_cleaner']));
