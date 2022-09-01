<?php

echo '<div class="well">';

\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

echo '<br /><br />';
echo ' <div class="btn-group" role="group" aria-label="Default button group">';


unset($data['menu']['logs']);

foreach ($data['menu'] as $key => $elem) {
    if ($_GET['path'] == $elem['path']) {
        $color = "btn-primary";
    } else {
        $color = "btn-default";
    }

    echo '<a href="'.$elem['path'].'" type="button" class="btn '.$color.'" style="font-size:12px">'
    .' '.$elem['icone'].' '.$elem['name'].'</a>';
}
echo '</div>';
echo '</div>';


$elems  = explode('/', $_GET['path']);
$method = end($elems);

\Glial\Synapse\FactoryController::addNode("benchmark", $method, array());