<?php

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-style" => "btn-primary", "data-width" => "auto","all_selectable"=> "true")));
?>

<div class="btn-group">
  <button type="button" class="btn btn-primary active">Database</button>
  <!--<button type="button" class="btn btn-primary">Cluster</button>
  <button type="button" class="btn btn-primary">Graph</button>
  <button type="button" class="btn btn-primary">Binlogs</button>
  <button type="button" class="btn btn-primary">Processlist</button>-->
</div>