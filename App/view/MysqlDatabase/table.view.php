<?php

use \Glial\Synapse\FactoryController;


    ?>
    <div >
    <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
    <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlDatabase", "menu", $data['param']); ?></div>
    <div style="float:left;"><?= \Glial\Synapse\FactoryController::addNode("MysqlTable", "menu", $data['param']); ?></div>
    </div> 
    <div style="clear:both"></div>
    <?php

echo "<br />";

?>
<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">

                <?= __("Database") ?> : <?= $data['table_schema'] ?>
                </h3>
        </div>
        <div>

<?php



echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Table").'</th>';
echo '<th>'.__("Action").'</th>';

echo '<th>'.__("Rows").'</th>';

echo '<th>'.__("Type").'</th>';
echo '<th>'.__("Engine").'</th>';
echo '<th>'.__("Row format").'</th>';
echo '<th>'.__("Collation").'</th>';
echo '<th>'.__("Size").'</th>';
echo '<th>'.__("Index").'</th>';
echo '<th>'.__("Overhead").'</th>';
echo '</tr>';
$i=0;
foreach($data['table'] as $fk ){

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['TABLE_NAME'].'</td>';
    echo '<td></td>';
    echo '<td>'.$fk['TABLE_ROWS'].'</td>';
    echo '<td>'.$fk['TABLE_TYPE'].'</td>';
    
    echo '<td>'.$fk['ENGINE'].'</td>';
    echo '<td>'.$fk['ROW_FORMAT'].'</td>';
    echo '<td>'.$fk['TABLE_COLLATION'].'</td>';
    echo '<td>'.$fk['DATA_LENGTH'].'</td>';
    echo '<td>'.$fk['INDEX_LENGTH'].'</td>';
    echo '<td>'.$fk['DATA_FREE'].'</td>';

    echo '</tr>';

}

echo '</table>';



?>


</div></div>