<?php

use \Glial\Synapse\FactoryController;
use \App\Library\Display;

FactoryController::addNode("Layout", "headerPma", array($GLIALE_TITLE));
$elems = FactoryController::addNode("Layout", "ariane", array($GLIALE_ARIANE, $GLIALE_TITLE), FactoryController::RESULT);

$elems['title'] = Display::icon32($elems['title']);

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
