
<div >
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", array()); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlDatabase", "menu", array()); ?></div>
  <div style="float:left;"><?= \Glial\Synapse\FactoryController::addNode("MysqlTable", "menu", array()); ?></div>
</div> 
<div style="clear:both"></div>
<?php

echo "<br />";

$filename = $data['graph'];

?>

<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("Table").' : '.$data['table_schema'].'.'.$data['table_name'] ?></h3>
        </div>
        <div class="mpd">


        <?php


echo '<div id="svg">';

$filename = str_replace("png","svg", $filename);

$handle = fopen($filename, "r");
$remove = true;

if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        
        if ("<svg" != substr($buffer, 0,4) && $remove)
        {
            $remove = false;
            continue;
        }
        
        echo $buffer;
    }
    if (!feof($handle)) {
        echo "Erreur: fgets() a échoué\n";
    }
    fclose($handle);
}

echo '</div>';
echo '</div>';


echo '</div>';
