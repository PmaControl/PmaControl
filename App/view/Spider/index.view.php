<?php


echo '<form action="" method="post" class="form-inline">';
echo '<div class="form-group">';


\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array());

echo ' <button type="submit" class="btn btn-primary">Submit</button>';

echo '</div>';
echo '</form>';

echo '<br />';

if (!empty($_POST['mysql_server']['id'] ))
{
	\Glial\Synapse\FactoryController::addNode("Spider", "testIfSpiderExist", array($_POST['mysql_server']['id']));
	\Glial\Synapse\FactoryController::addNode("Spider", "Server", array($_POST['mysql_server']['id']));
}

