<div class="container" style="width:100%">
  <div class="row">
    <div class="col-md-6">
      <?php
        echo \SqlFormatter::format($data['query']['DIGEST_TEXT']);
        ?>
    </div>
    <div class="col-md-6" style="background:#eee">
      <?php
        echo \SqlFormatter::format($data['sql_text']);
        ?>
    </div>

  </div>

  <div class="row">

  <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?= __("Explain") ?></h3>
            </div>
            <div>

    <?php

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>id</th>';
echo '<th >select_type</th>';
echo '<th>table</th>';
echo '<th>possible_keys</th>';
echo '<th>key</th>';
echo '<th>key_len</th>';
echo '<th>ref</th>';
echo '<th>rows</th>';
echo '<th>filtered</th>';
echo '<th>extra</th>';
echo '</tr>';


foreach($data['explain'] as $explain)
{
    if (! empty($data['alias'][$explain['table']]['table']))
    {
      $alias = $data['alias'][$explain['table']]['table'] ." (".$explain['table'].")";
    }
    else{
      $alias =  $explain['table'];
    }

    

    echo '<tr>';
    echo '<td>'.$explain['id'].'</td>';
    echo '<td>'.$explain['select_type'].'</td>';
    echo '<td>'.$alias.'</td>';
    echo '<td>'.$explain['possible_keys'].'</td>';
    echo '<td>'.$explain['key'].'</td>';
    echo '<td>'.$explain['key_len'].'</td>';
    echo '<td>'.$explain['ref'].'ref</td>';
    echo '<td>'.$explain['rows'].'</td>';
    echo '<td>'.$explain['filtered'].'</td>';
    echo '<td>'.$explain['Extra'].'</td>';
    echo '</tr>';
}

echo '</table>';
    ?>

  </div>
</div>


