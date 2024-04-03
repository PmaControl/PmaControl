<div >
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlDatabase", "menu", $data['param']); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("ForeignKey", "menu", $data['param']); ?></div>
</div> 
<div style="clear:both"></div>
<br />
<?php

\Glial\Synapse\FactoryController::addNode("ForeignKey", "virtual", $param);