<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

\Glial\Synapse\FactoryController::addNode("Database", "create", array());
\Glial\Synapse\FactoryController::addNode("Database", "rename", array());
\Glial\Synapse\FactoryController::addNode("Database", "refresh", array());


\Glial\Synapse\FactoryController::addNode("Database", "analyze", array());