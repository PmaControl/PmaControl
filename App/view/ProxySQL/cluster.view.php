<?php

use \Glial\Synapse\FactoryController;

if (empty($_GET['ajax'])) {
    FactoryController::addNode("ProxySQL", "menu", $data['param']);
    echo "<br />";
    echo "<br />";

    echo '<div class="panel panel-primary">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">'.__("Cluster").'</h3>';
    echo '</div>';
    echo '<div class="mpd">';

    echo '<div id="svg">';
    echo '<div style="float:right; border:#000 0px solid">';
    FactoryController::addNode("Dot3", "legend", array());
    echo '</div>';

    echo '<div id="graph" style="float:left; border:#000 0px solid">';
}

if (!empty($data['svg'])) {
    echo $data['svg'];
} elseif (!empty($_GET['ajax'])) {
    // no-op: avoid clearing when ajax refresh returns empty
} else {
    $downloadLink = LINK.'dot3/download/';
    echo '<div style="margin:20px;" class="alert alert-info" role="alert">';
    if (empty($data['id_mysql_server'])) {
        echo __("This ProxySQL server is not linked to a MySQL server yet. Please associate it to see the cluster view.");
    } else {
        echo __("This server does not seem to be part of a cluster if the latter were to be part of it you can download the json with the link below");
        echo '<br /><br /><a href="'.$downloadLink.'" class="btn btn-primary" role="button"></span> '
            .__("Download").' <span class="glyphicon glyphicon-download-alt"></span></a><br /><br />';
        echo __("Then you can post your file at this address for debug :")
            . '&nbsp;<a href="https://github.com/PmaControl/PmaControl/issues/new" class="btn btn-success" role="button">
            <span class="glyphicon glyphicon-plus"></span> '.__("Add an issue").'</a>';
    }
    echo '</div>';
}

if (empty($_GET['ajax'])) {
    echo '</div>';
    echo '<div style="clear:both"></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
