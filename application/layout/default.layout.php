<?php

use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;

use \App\Library\Ariane;



FactoryController::addNode("Layout", "headerPma",array($GLIALE_TITLE));

$elems = FactoryController::addNode("Layout", "ariane", array($GLIALE_ARIANE, $GLIALE_TITLE), FactoryController::RESULT);

echo '<div id="page">';
echo "<div id=\"glial-title\">";
echo "<h2>".$elems['title']."</h2>";
echo '<span class="ariane">'.$elems['ariane'].'</span>';

echo "</div>";


echo "<div style=\"padding:0 10px 10px 10px\">";

get_flash();
echo $GLIALE_CONTENT;
echo "</div>";
echo '</div>';


FactoryController::addNode("Layout", "footerPma");