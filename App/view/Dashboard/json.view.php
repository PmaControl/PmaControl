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
      <input type="text" class="form-control required" name="date[date]" id="frmSaveOffice_startdt" required readonly>
      <div class="input-group-addon">
        <span class="glyphicon glyphicon-calendar"></span>
      </div>
    </div>
  </div>
</div>

<div class="col-md-2">
  <div class='input-group date' id='datetimepicker3'>
      <input name="date[time]" type='text' class="form-control" />
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