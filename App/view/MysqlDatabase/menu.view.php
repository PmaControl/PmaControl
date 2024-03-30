<?php

use Glial\Html\Form\Form;

$_GET['mysql_server']['id'] = 1;

\Glial\Synapse\FactoryController::addNode("Common", "getDatabaseByServer", array("g", "g", "1", array("data-style" => "btn-info", "data-live-search" => "true", "class" => "selectpicker")));

//echo Form::select("foreign_key_remove_prefix", "database_name", $data['table_schema'], "", );
//\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-style" => "btn-info","data-width" => "auto","all_selectable"=> "true")));
?>

<div class="btn-group">
  <button type="button" class="btn btn-info active">Tables</button>
  <!--<button type="button" class="btn btn-default">Rename</button>-->
  <button type="button" class="btn btn-info">Physical data model</button>
  <!--<button type="button" class="btn btn-default">Dictionary</button>-->
  <button type="button" class="btn btn-info">Foreign keys</button>
</div>
