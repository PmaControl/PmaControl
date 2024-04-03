<?php

use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;

\Glial\Synapse\FactoryController::addNode("MysqlDatabase", "getDatabaseByServer", array($_GET['mysql_server']['id'] , array("data-style" => "btn-info", "data-live-search" => "true", "class" => "selectpicker")));
?>

<div class="btn-group">
  <!--<button type="button" class="btn btn-default">Dictionary</button>-->
  <!--<button type="button" class="btn btn-default">Rename</button>-->
<?php

  $node = FactoryController::getRootNode();

  $menu['MysqlDatabase']['mpd'] = 'Physical data model';
  $menu['MysqlDatabase']['foreignKey'] = 'Foreign keys';
  $menu['MysqlDatabase']['table'] = 'Tables';
  
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
      echo '<a href="'.LINK.$controller.'/'.$view.'/'.$param[0].'/'.$param[1].'/" type="button" class="btn btn-info '.$active.'">'.$title.'</a>'."\n";
    }
  }

?>


</div>




