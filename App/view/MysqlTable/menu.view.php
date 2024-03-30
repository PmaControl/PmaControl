<?php

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", 
array("mysql_server", "id", array("data-style" => "btn-default", "data-width" => "auto","all_selectable"=> "true")));
?>

<div class="btn-group">

  <button type="button" class="btn btn-default">Browse</button>
  <button type="button" class="btn btn-default">Structure</button>
  <button type="button" class="btn btn-default active">Relation view</button>
  
<!--
    <button type="button" class="btn btn-default">Search</button>
      <button type="button" class="btn btn-default">Insert</button>
  <button type="button" class="btn btn-default">Export</button>
  <button type="button" class="btn btn-default">Import</button>
  <button type="button" class="btn btn-default">Privileges</button>
  <button type="button" class="btn btn-default">Operations</button>
  <button type="button" class="btn btn-default">Triggers</button>
  -->
</div>
