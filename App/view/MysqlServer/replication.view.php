<?php

use \Glial\Synapse\FactoryController;

$serverId = (int)($id_mysql_server ?? $param[0] ?? 0);
FactoryController::addNode("MysqlServer", "menu", [$serverId]);
FactoryController::addNode("Slave", "show", [$serverId, $param[1] ?? '']);
