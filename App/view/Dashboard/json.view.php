<?php
use Glial\Html\Form\Form;
?>
<form method="post">
<?php

echo '<div class="row">';
echo '<div class="col-md-6">';

echo __("Server : ");
echo ' ';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto","all_selectable"=> "true")));


echo "&nbsp;&nbsp;&nbsp;". __("Json : ");
echo ' ';
\Glial\Synapse\FactoryController::addNode("Common", "getTsVariableJson", array("ts_variable", "id", array("data-width" => "auto")));

echo '</div>';
?>

<div class="col-md-2">
  <div class="form-group required">
    <div class="input-group datepick">
      <?php echo Form::input("date", "date", array("class"=>"form-control required","readonly"=>"readonly", "placeholder" => date("Y-m-d"))); ?>
      <!--<input type="text" class="form-control required" name="date[date]" id="frmSaveOffice_startdt" required readonly>-->
      <div class="input-group-addon">
        <span class="glyphicon glyphicon-calendar"></span>
      </div>
    </div>
  </div>
</div>

<div class="col-md-2">
  <div class='input-group date' id='datetimepicker3'>
  <?php echo Form::input("date", "time", array("class"=>"form-control required", "placeholder" => date("H:i:s"))); ?>
      <!--<input name="date[time]" type='text' class="form-control" />-->

      <span class="input-group-addon">
      <span class="glyphicon glyphicon-time"></span>
      </span>
  </div>
</div>

<div class="col-md-1">
  <button type="submit" class="btn btn-primary">Submit</button>
</div>

</div>
</form>




<?php 


//debug($data);

echo '<table class="table table-condensed table-bordered table-striped" id="table">';



foreach($data['json'] as $key => $arr)
{
  if (empty($arr['value']))
  {
    continue;
  }


  $count = count($arr['value']);
  $keys = array_keys($arr['value']);
  $values = array_values($arr['value']);



  echo '<tr>';
  echo '<th colspan="'.$count.'">';
  echo $arr['date'];
  echo '</th>';
  echo '</tr>';

  echo '<tr>';

  foreach($keys as $key)
  {
    echo '<th>';
    echo $key;
    echo '</th>';
  
  }
  echo '</tr>';


  echo '<tr>';
  foreach($values as $value)
  {
    if (!empty($value) && mb_strlen($value) > 1000)
    {
      $value = substr($value, 0,1000). '...';
    }

    echo '<td>';
    echo $value;
    echo '</td>';
  
  }
  echo '</tr>';

} 

echo '</table>';