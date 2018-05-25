<?php

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
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
        echo "Date de rafraichissement : " . $date['date'][0];
    }
}