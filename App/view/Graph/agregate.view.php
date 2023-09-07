<?php


use Glial\Html\Form\Form;

echo '<div class="well">';


echo '<div class="row">';
echo '<form method="POST" action="">';
echo '<div class="col-md-10">';
echo Form::select("mysql_cluster", "id", $data['grappe'], "", array("data-live-search" => "true", "class" => "selectpicker form-control"));
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" class="btn btn-primary">Check Result on this cluster</button>';
echo '</div>';
echo '</form>';
echo '</div>';


echo '<div class="row">';
echo '<br />';
echo '</div>';

echo '<div class="row">';
echo '<form method="POST" action="">';
echo '<div class="col-md-10">';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("multiple" => "multiple", "data-width" => "100%", "all_server"=>"true")));
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" class="btn btn-primary">Check Result on these servers</button>';
echo '</div>';

echo '</form>';
echo '</div>';

echo '</div>';