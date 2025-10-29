<?php

use \Glial\Synapse\FactoryController;

FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-style" => "btn-primary", "data-width" => "auto","all_selectable"=> "true")));




?>

<div class="btn-group">

  <!--<button type="button" class="btn btn-primary">Cluster</button>
  <button type="button" class="btn btn-primary">Graph</button>
  <button type="button" class="btn btn-primary">Binlogs</button>
  <button type="button" class="btn btn-primary">Processlist</button>-->

  <?php

$node = FactoryController::getRootNode();

$menu= array();
$menu['MysqlServer']['processlist'] = __('Processlist');
$menu['Cluster']['svg'] = __('Cluster');
$menu['Query']['all'] = __('Queries');
$menu['MysqlDatabase']['mpd'] = __('Database');


foreach($menu as $controller => $views)
{
  foreach($views as $view => $title)
  {
    if (($node[0] === "$controller" && $node[1] === "$view") || strtolower($node[0]) == strtolower($view)){
      $active ='active';
    }
    else{
      $active ='';
    }
    echo '<a href="'.LINK.$controller.'/'.$view.'/'.$param[0].'/'.$param[1].'/" type="button" class="btn btn-primary '.$active.'">'.$title.'</a>'."\n";
  }
}

?>

</div>

