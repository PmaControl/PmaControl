<?php

use \Glial\Synapse\FactoryController;


 if (empty($_GET['ajax'])) {

  echo '
  <div class="panel panel-primary">
  <div class="panel-heading">
      <h3 class="panel-title">'.__('List of queue').'</h3>
  </div>';

  echo '<div id="worker-index">';
}


echo '<table class="table table-condensed table-bordered table-striped" style="margin-bottom:0px">';
echo '<tr>';
echo '<th>'.__("Name").'</th>';

echo '<th>'.__("Number of worker").'</th>';
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
    echo '<td class="line-edit" data-name="nb_worker" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'worker/update" data-title="Enter class">'.$daemon['nb_worker'].'</td>';
    echo '<td class="line-edit" data-name="queue_number" data-pk="'.$daemon['id'].'" data-type="text" data-url="'.LINK.'worker/update" data-title="Enter class">'.$daemon['queue_number'].'</td>';
  //  echo '<td>'.$daemon['nb_msg'].'</td>';
  //  echo '<td>'.$daemon['queue_number'].'</td>';

    $class="";

    echo '<td class="'.$class.'">';
    if ($daemon['msg_qnum'] != 0) // add error > superior of number of instance to monitor
    {
      echo '<span class="label label-warning" title="'.$daemon['msg_qnum'].'"><i class="glyphicon glyphicon-warning-sign"></i> '.$daemon['msg_qnum'].'</span>';
    }
    else
    {
      echo '<span class="label label-success" title="'.$daemon['msg_qnum'].'"> '.$daemon['msg_qnum'].' </span>';
      //echo $daemon['msg_qnum'];
    }
    echo '</td>';


    echo '<td>'.$daemon['worker_class'].'/'.$daemon['worker_method'].'</td>';
    echo '<td>';

    echo $daemon['query'];
    echo '</td>';
    echo '</tr>';
}
echo '</table>';


if (empty($_GET['ajax'])) {

  echo '</div>';
  echo '</div>';
}