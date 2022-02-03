<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


echo ' <div class="btn-group" role="group" aria-label="Default button group">';

foreach ($data['menu'] as $key => $elem) {

    if ($_GET['menu'] == $key) {
        $color = "btn-info";
    } else {
        $color = "btn-primary";
    }

    $disable ='';
    if ($elem['count'] == 0) {
        $disable = 'disabled="disabled"';
    }

    echo '<a href="'.$elem['url'].'" type="button" class="btn '.$color.'" style="font-size:12px" '.$disable.'>'
    .' '.$elem['icone'].' '.__($elem['name']).'</a>';
}
echo '</div>';
echo '<br /><br />';

\Glial\Synapse\FactoryController::addNode("Database", "create", array());
\Glial\Synapse\FactoryController::addNode("Database", "rename", array());
\Glial\Synapse\FactoryController::addNode("Database", "refresh", array());

\Glial\Synapse\FactoryController::addNode("Database", "compare", array());
\Glial\Synapse\FactoryController::addNode("Database", "analyze", array());

\Glial\Synapse\FactoryController::addNode("Database", "data", array());