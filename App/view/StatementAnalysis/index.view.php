<?php

echo '<div>';
echo '<form action="" method="POST">';
echo __("Server") . " : ";

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto")));
//echo Form::select("mysql_server", "id", $data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
echo ' ';
echo '<button type="submit" class="btn btn-primary">Filter</button>';

echo '</form>';
echo '</div>';