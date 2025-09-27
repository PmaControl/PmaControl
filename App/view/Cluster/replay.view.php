<?php
use \Glial\Html\Form\Form;
?>



<div>
    <form method="post" action="<?= LINK ?>Cluster/history/<?= $data['id_mysql_server'] ?>">
    <?php

        $options = array_merge(array("data-live-search" => "true", "class" => "selectpicker", "all_selectable" => "true"), $data['options']);
        echo __('Date start').' ';
        echo Form::Select("dot3_cluster__mysql_server","date_min", $data['list_min'],$data['date_min'],$options);
        echo ' '.__('Date end').' ';
        echo Form::Select("dot3_cluster__mysql_server","date_max", $data['list_min'],$data['date_max'],$options);
    ?>

  <!-- Bouton Replay -->
  <button type="submit" class="btn btn-info" id="btn-replay">Replay</button>
  </form>
</div>


