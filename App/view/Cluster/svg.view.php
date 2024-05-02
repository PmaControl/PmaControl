<?php

use \Glial\Synapse\FactoryController;
use phpseclib3\File\ASN1\Maps\ExtKeyUsageSyntax;
if (empty($_GET['ajax'])){

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

    echo '<div id="graph" style="float:left; border:#000 0px solid">';
}

if (! empty($data['svg']))
{
    echo $data['svg'];
}
elseif (! empty($_GET['ajax']))
{
    // some time we get nothing from DB like the link are not pushed in same time need to add it in transaction
    // for qucik fix we not refresh html if empty
}
else {
    echo '<div style="margin:20px;" class="alert alert-info" role="alert">';
    echo __("This server does not seem to be part of a cluster if the latter were to be part of it you can download the json with the link below");
    echo '<br /><br /><a href="'.LINK.'dot3/download/" class="btn btn-primary" role="button"></span> '
    .__("Download").' <span class="glyphicon glyphicon-download-alt"></span></a><br /><br />';
    echo __("Then you can post your file at this address for debug :")
    . '&nbsp;<a href="https://github.com/PmaControl/PmaControl/issues/new" class="btn btn-success" role="button">
    <span class="glyphicon glyphicon-plus"></span> '.__("Add an issue").'</a>';
    echo '</div>';
}
if (empty($_GET['ajax'])){
    echo '</div>';
    echo '<div style="clear:both"></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}