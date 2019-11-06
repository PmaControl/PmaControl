<?php

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Cleaner", "menu", array($data['id_cleaner']));
echo '</div>';


echo '<canvas style="width: 100%; height: 550px;" id="myChart" height="550" width="1600"></canvas>';

//echo '<canvas style="width: 100%; height: 550px;" id="myChart2" height="550" width="1600"></canvas>';