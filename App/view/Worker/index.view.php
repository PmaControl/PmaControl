<?php

use \Glial\Synapse\FactoryController;

if (empty($_GET['ajax'])) {
    echo '<div id="daemon-index">';
 }

echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__("Name").'</th>';

echo '<th>'.__("Refresh time").'</th>';
//echo '<th>'.__("Queue number").'</th>';
//echo '<th>'.__("Queue msg").'</th>';

echo '<th>'.__("Queue number").'</th>';
echo '<th>'.__("Number msg waiting").'</th>';

echo '<th>'.__("Worker").'</th>';

echo '<th>'.__("Query").'</th>';
echo '</tr>';

foreach ($data['worker'] as $daemon) {

    echo '<tr class="alternate">';

    echo '<td>'.$daemon['name'].'</td>';
   // echo '<td class="line-edit" data-name="thread_concurency" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'daemon/update" data-title="Enter class">'.$daemon['thread_concurency'].'</td>';
  //  echo '<td>'.$daemon['max_delay'].'</td>';
    echo '<td class="line-edit" data-name="refresh_time" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'daemon/update" data-title="Enter class">'.$daemon['interval'].'</td>';
  //  echo '<td class="line-edit" data-name="queue_number" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'daemon/update" data-title="Enter class">'.$daemon['queue_number'].'</td>';
  //  echo '<td>'.$daemon['nb_msg'].'</td>';
    echo '<td>'.$daemon['queue_number'].'</td>';
    echo '<td>'.$daemon['queue_number'].'</td>';
    echo '<td>'.$daemon['worker_class'].'/'.$daemon['worker_method'].'</td>';
    echo '<td>';

    echo $daemon['query'];
    echo '</td>';
    echo '</tr>';
}
echo '</table>';