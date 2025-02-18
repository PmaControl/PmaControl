<?php


use \Glial\Synapse\FactoryController;

if (empty($_GET['ajax'])) {
    echo '<div id="daemon-index">';
}

echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.'PID'.'</th>';
echo '<th>'.__('Date').'</th>';
//echo '<th>'.__("Thread concurrency").'</th>';
//echo '<th>'.__("Maximum Delay").'</th>';
echo '<th>'.__("Refresh time").'</th>';
//echo '<th>'.__("Queue number").'</th>';
//echo '<th>'.__("Queue msg").'</th>';
echo '<th>'.__("Path").'</th>';
echo '<th>'.__("Command").'</th>';
echo '</tr>';

foreach ($data['daemon'] as $daemon) {

    echo '<tr class="alternate">';
    echo '<td>'.$daemon['id'].'</td>';
    echo '<td>'.$daemon['name'].'</td>';
    echo '<td>'.$daemon['pid'].'</td>';
    echo '<td>'.$daemon['date'].'</td>';
   // echo '<td class="line-edit" data-name="thread_concurency" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'daemon/update" data-title="Enter class">'.$daemon['thread_concurency'].'</td>';
  //  echo '<td>'.$daemon['max_delay'].'</td>';
    echo '<td class="line-edit" data-name="refresh_time" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'daemon/update" data-title="Enter class">'.$daemon['refresh_time'].'</td>';
  //  echo '<td class="line-edit" data-name="queue_number" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'daemon/update" data-title="Enter class">'.$daemon['queue_number'].'</td>';
  //  echo '<td>'.$daemon['nb_msg'].'</td>';
    echo '<td>'.$daemon['class'].'/'.$daemon['method'].' '.$daemon['params'].'</td>';
    echo '<td>';

    echo ' <div style="float:right" class="btn-toolbar btn-group btn-group-xs" role="group" aria-label="Default button group">';
    echo '&nbsp;<a href="'.LINK.'Agent/stop/'.$daemon['id'].'" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:12px"></span> Stop Daemon</a>';
    echo '<a href="'.LINK.'Agent/start/'.$daemon['id'].'" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:12px"></span> Start Daemon</a>';
    if (empty($daemon['pid'])) {
        echo '<a href="'.LINK.'Server/listing/logs" type="button" class="btn btn-warning" style="font-size:12px"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style="font-size:13px"></span> Stopped</a>';
    } else {
        $cmd   = "ps -p ".$daemon['pid'];
        $alive = shell_exec($cmd);

        if (strpos($alive, $daemon['pid']) !== false) {
            echo '<a type="button" class="btn btn-success" style="font-size:12px"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="font-size:12px"></span> Running (PID : '.$daemon['pid'].')</a>';
        } else {
            echo '<a href="'.LINK.'Server/listing/logs" type="button" class="btn btn-danger" style="font-size:12px"><span class="glyphicon glyphicon-remove" aria-hidden="true" style="font-size:12px"></span> Error</a>';
        }
    }

    echo '&nbsp;<a href="'.LINK.'Agent/logs/'.$daemon['id'].'" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon glyphicon-book" aria-hidden="true" style="font-size:12px"></span> Logs</a>';
    echo '</div>';
    echo '</td>';
    echo '</tr>';
}
echo '</table>';


if (empty($_GET['ajax'])) {
    echo '</div>';

    echo ' <div class="btn-group" role="group" aria-label="Default button group">';
    echo '&nbsp;<a href="'.LINK.'Daemon/stopAll/" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:12px"></span> Stop All Daemons</a>';
    echo '<a href="'.LINK.'Daemon/startAll" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:12px"></span> Start All Daemons</a>';
    echo '</div>';
    echo '<a href="'.LINK.'Daemon/refresh" type="button" title="Use this if there are troubles after crash of server, can take several seconds" class="btn btn-warning" style="font-size:12px"> <span class="glyphicon glyphicon-refresh" aria-hidden="true" style="font-size:12px"></span> Refresh all</a>';
    echo '<br /><br />';

    FactoryController::addNode("Worker", "index", array());


    echo '<div class="row" style="height: 100%;">';
    echo '<div class="col-md-6" style="height: 100%;">';
    FactoryController::addNode("Worker", "list", array());
    echo '</div>';

    echo '<div class="col-md-6" style="height:100%;">';
    FactoryController::addNode("Worker", "file", array());
    echo '</div>';

    echo '</div>';


    FactoryController::addNode("Listener", "status", array());
}
