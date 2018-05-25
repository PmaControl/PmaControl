<?php

use \Glial\Synapse\FactoryController;

echo '<div class="well">';
echo '<div class="btn-group" role="group" aria-label="Default button group">';


foreach ($data['link'] as $link) {

    echo '<a href="' . LINK . $link['url'] . '" type="button" class="btn btn-primary" style="font-size:12px">'
    . ' <span class="glyphicon ' . $link['icon'] . '" aria-hidden="true"></span> ' . $link['name'] . '</a>';
}


echo '</div>';

echo '</div>';




FactoryController::addNode("Home", "list_server", array());


$svg = 'tmp/replication.svg';
//echo '<div style="background: url('.IMG.$svg.')"></div>';
//echo '<embed src="'.IMG.$svg.'" type="image/svg+xml" />';

