<?php
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<div style="float:right; border:#000 0px solid">';
\Glial\Synapse\FactoryController::addNode("Dot2", "legend", array());
echo '</div>';

// @TODO remove empry graph from dot generateCache
foreach ($data['graphs'] as $graph) {
    if (!empty($graph['display'])) {

        echo '<div style="float:left; border:#000 0px solid">';
        //echo $graph['height'];
        echo $graph['display'];
        echo '</div>';
        $date['date'][] = $graph['date'];
    }
}

echo '<div style="clear:both"></div>';

if (!empty($data['graphs'])
) {

    if (!empty($date['date'])) {
        sort($date['date']);
        echo __("Date de rafraichissement :", "fr")." ".$date['date'][0];
    }
}