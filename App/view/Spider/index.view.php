<?php


echo '<form action="" method="get" class="form-inline">';
echo '<div class="form-group">';


\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array());

echo ' <button type="submit" class="btn btn-primary">Submit</button>';

echo '</div>';
echo '</form>';

echo '<br />';

if (!empty($data['id_mysql_server']))
{
	\Glial\Synapse\FactoryController::addNode("Spider", "testIfSpiderExist", array($data['id_mysql_server']));
	\Glial\Synapse\FactoryController::addNode("Spider", "Server", array($data['id_mysql_server']));
}
