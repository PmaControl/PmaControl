<?php

use \Glial\Synapse\FactoryController;
$serverId = (int) ($param[0] ?? 0);
FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-style" => "btn-primary", "data-width" => "auto","all_selectable"=> "true"), $serverId));
?>

<div class="btn-group">

  <!--<button type="button" class="btn btn-primary">Cluster</button>
  <button type="button" class="btn btn-primary">Graph</button>
  <button type="button" class="btn btn-primary">Binlogs</button>
  <button type="button" class="btn btn-primary">Processlist</button>-->

  <?php

$node = FactoryController::getRootNode();
$routeExtra = isset($param[1]) && $param[1] !== '' ? '/'.$param[1] : '';
$runDateLabel = (string) ($param[2] ?? '');

$menu= array();
$menu['MysqlServer']['main'] = __('Info');
$menu['MysqlServer']['processlist'] = __('Processlist');
$menu['MysqlServer']['logs'] = __('Logs');
$menu['Pmm']['index'] = __('PMM');
$menu['Cluster']['svg'] = __('Cluster');
$menu['Query']['all'] = __('Queries');
$menu['MysqlDatabase']['mpd'] = __('Database');


foreach($menu as $controller => $views)
{
  foreach($views as $view => $title)
  {
    if (
      ($node[0] === "$controller" && $node[1] === "$view")
      || strtolower($node[0]) == strtolower($view)
      || ($node[0] === 'MysqlServer' && $node[1] === 'runDetail' && $controller === 'MysqlServer' && $view === 'main')
      || ($node[0] === 'Pmm' && $controller === 'Pmm' && $view === 'index')
      || ($node[0] === 'Cluster' && $node[1] === 'viewDot' && $controller === 'Cluster' && $view === 'svg')
    ){
      $active ='active';
    }
    else{
      $active ='';
    }
    echo '<a href="'.LINK.$controller.'/'.$view.'/'.$serverId.$routeExtra.'/" type="button" class="btn btn-primary '.$active.'">'.$title.'</a>'."\n";
  }
}

if ($node[0] === 'MysqlServer' && $node[1] === 'runDetail' && $runDateLabel !== '') {
  echo '<a href="#" type="button" class="btn btn-default active">'.__('Run').' '.htmlspecialchars($runDateLabel, ENT_QUOTES, 'UTF-8').'</a>'."\n";
}

?>

</div>
