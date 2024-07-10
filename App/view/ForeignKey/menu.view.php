<?php

use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;

?>

<div class="btn-group">
  <!--<button type="button" class="btn btn-default">Dictionary</button>-->
  <!--<button type="button" class="btn btn-default">Rename</button>-->
<?php

  $node = FactoryController::getRootNode();

  $menu['MysqlDatabase']['foreignKey'] = 'FK Virtual';
  $menu['ForeignKey']['real'] = 'FK Real';
  $menu['ForeignKey']['custom'] = 'FK Custom';
  $menu['ForeignKey']['blacklist'] = 'FK blacklist';

  //$menu['ForeignKey']['proposal'] = 'FK Proposal';
  //$menu['ForeignKey']['blackList'] = 'FK Black list';
  //$menu['ForeignKey']['prefix'] = 'Prefix';
  
  foreach($menu as $controller => $views)
  {
    foreach($views as $view => $title)
    {
      if ($node[0] === "$controller" && $node[1] === "$view"){
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
