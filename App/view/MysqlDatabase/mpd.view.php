<div >
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlDatabase", "menu", $data['param']); ?></div>
  
</div> 
<div style="clear:both"></div>

<?php


\Glial\Synapse\FactoryController::addNode("table", "mpd", $param);
