<?php

use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;

use \App\Library\Ariane;


//$title = FactoryController::addNode("Layout", "title",array(), FactoryController::DISPLAY);

//debug($title);


FactoryController::addNode("Layout", "headerPma",$GLIALE_TITLE);




echo '<div id="page">';
echo "<div id=\"glial-title\">";
echo "<h2>".$GLIALE_TITLE."</h2>";

FactoryController::addNode("Layout", "ariane", array($GLIALE_ARIANE, $GLIALE_TITLE));

echo "</div>";


echo "<div style=\"padding:0 10px 10px 10px\">";

get_flash();
echo $GLIALE_CONTENT;
echo "</div>";
echo '</div>';


FactoryController::addNode("Layout", "footerPma");