<?php
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


//debug($data);

?>

<div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <?= __("Topology") ?> <a style="float:right" href="<?= LINK ?>dot3/download/" class="btn btn-success btn-xs" role="button"> Download JSON for debug <span class="glyphicon glyphicon-download-alt"></span></a>
                    
                    </h3>
            </div>
            <div class="mpd grid">
<?php


/*
echo '<div style="float:right; border:#000 0px solid">';
\Glial\Synapse\FactoryController::addNode("Dot2", "legend", array());
echo '</div>';
*/

// @TODO remove empry graph from dot generateCache
foreach ($data['graphs'] as $graph) {


//debug($graph);

    if (!empty($graph['svg'])) {

        echo '<div class="grid-item" style="float:left; border:#000 0px solid">';
        //echo $graph['height'];
        echo $graph['svg'];
        echo '</div>';
        $date['date'][] = $graph['date_refresh'];
    }
}


echo '<div style="clear:both"></div>';


echo '</div>'; //end mpd

if (!empty($data['graphs'])
) {

    if (!empty($date['date'])) {
        sort($date['date']);
        echo '<div style="float:right;">'.__("Date de rafraichissement :", "fr")." ".$date['date'][0]."</div>";
    }
}

