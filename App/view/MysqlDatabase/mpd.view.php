<div >
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlDatabase", "menu", $data['param']); ?></div>
  
</div> 
<div style="clear:both"></div>

<?php


\Glial\Synapse\FactoryController::addNode("table", "mpd", $param);


?>
<p>
By default, the ERD is generated using foreign key definitions. This means that only tables stored with the InnoDB engine are included, because other engines (like MyISAM, RocksDB or MEMORY) do not support foreign key constraints, and therefore cannot express relational structure.<br>
<br>
However, if your schema does not explicitly define foreign keys, or you are working with legacy databases, we can still infer relationships automatically. The generator supports common naming conventions such as:<br>
<code>
id_table → table.id<br>
user_id → user.id<br>
customerId → customer.id<br>
customerId → customer.id_customer<br>
</code>
…and many others.<br>
<br>
This allows us to rebuild a complete ERD even for schemas without formal FK constraints. If you'd like, we can enable this heuristic mode and generate a fully relational diagram from your existing naming patterns.<br>

</p>
