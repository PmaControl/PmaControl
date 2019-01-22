<?php

use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;

use \App\Library\Ariane;
use \App\Library\Title;


$title = FactoryController::addNode("Layout", "Title",array());

FactoryController::addNode("Layout", "headerPma",array($title));


echo '<div id="page">';
echo "<div id=\"glial-title\">";
echo "<h2>".$title ."</h2>";

FactoryController::addNode("Layout", "ariane", array($GLIALE_ARIANE, $title));

echo "</div>";


echo "<div style=\"padding:0 10px 10px 10px\">";

get_flash();
echo $GLIALE_CONTENT;
echo "</div>";
echo '</div>';


FactoryController::addNode("Layout", "footerPma");