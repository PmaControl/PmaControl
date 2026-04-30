<?php

use App\Library\Html;
use App\Library\Security\ControllerActionSelection;

echo '<div class="well">';

\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

echo '<br /><br />';
echo ' <div class="btn-group" role="group" aria-label="Default button group">';


unset($data['menu']['logs']);

$benchmarkMenu = $data['menu'];
$selectedBenchmarkTab = ControllerActionSelection::normalize(
    $data['selected_benchmark_tab'] ?? null,
    array_keys($benchmarkMenu),
    'graph'
);

foreach ($benchmarkMenu as $key => $elem) {
    if ($selectedBenchmarkTab === $key) {
        $color = "btn-default active";
    } else {
        $color = "btn-default";
    }

    echo '<a href="'.Html::escape($elem['path']).'" type="button" class="btn '.$color.'" style="font-size:12px">'
    .' '.$elem['icone'].' '.Html::escape($elem['name']).'</a>';
}
echo '</div>';
echo '</div>';


\Glial\Synapse\FactoryController::addNode("benchmark", $selectedBenchmarkTab, array());
