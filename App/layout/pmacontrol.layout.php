<?php

use \Glial\Synapse\FactoryController;

FactoryController::addNode("Layout", "headerPmacontrol", array($GLIALE_TITLE));

echo '<main class="marketing">';
get_flash();
echo $GLIALE_CONTENT;
echo '</main>';

FactoryController::addNode("Layout", "footerPmacontrol");
