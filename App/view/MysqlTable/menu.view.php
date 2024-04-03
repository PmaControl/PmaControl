<?php

use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;

\Glial\Synapse\FactoryController::addNode("MysqlTable", "getTableByDatabase", array($_GET['mysql_server']['id'], $param[1], array("data-style" => "btn-default", "data-live-search" => "true", "class" => "selectpicker")));
?>

<div class="btn-group">
  <!--<button type="button" class="btn btn-default">Dictionary</button>-->
  <!--<button type="button" class="btn btn-default">Rename</button>-->
<?php

  $node = FactoryController::getRootNode();

  $menu['MysqlDatabase']['table'] = 'Listing';
  $menu['table']['mpd'] = 'Relation view';
  
  
  foreach($menu as $controller => $views)
  {
    foreach($views as $view => $title)
    {
      if ((strtolower($node[0]) === strtolower($controller) && $node[1] === "$view") ){
        $active ='active';
      }
      else{
        $active ='';
      }
      echo '<a href="'.LINK.$controller.'/'.$view.'/'.$param[0].'/'.$param[1].'/" type="button" class="btn btn-default '.$active.'">'.$title.'</a>'."\n";
    }
  }

?>


</div>