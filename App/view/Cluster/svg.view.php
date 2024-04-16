<?php

use \Glial\Synapse\FactoryController;


    ?>
    <div >
    <div style="float:left; padding-right:10px;"><?= FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
    </div> 
    <div style="clear:both"></div>
    <?php

echo "<br />";

?>

<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">

                <?= __("Cluster") ?>
                </h3>
        </div>
        <div class="mpd">


        <?php


echo '<div id="svg">';
echo '<div style="float:right; border:#000 0px solid">';
\Glial\Synapse\FactoryController::addNode("Dot3", "legend", array());
echo '</div>';

echo '<div style="float:left; border:#000 0px solid">';
echo $data['svg'];
echo '</div>';

echo '<div style="clear:both"></div>';
echo '</div>';
echo '</div>';


echo '</div>';
