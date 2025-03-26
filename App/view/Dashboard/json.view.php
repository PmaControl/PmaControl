<?php
use Glial\Html\Form\Form;
?>
<form method="post">
<?php

//debug($data);


echo '<div class="row">';
echo '<div class="col-md-7">';

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
  <div class='input-group date'>
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
$j = 0;
foreach($data['json'] as $elem)
{
  $j++;
  echo '<div class="panel panel-primary" style="margin-bottom:5px">';
    echo '<div class="panel-heading">';
        echo '<h3 class="panel-title">';
        echo "#".$j." - ". $elem['date'];
          
        echo '</h3>';
  echo '</div>';
  echo '<div class="mpd">';

  
  //debug($elem);

  echo '<table class="table table-condensed table-bordered table-striped" id="table" style="margin-bottom:0px">';


    // Générer la table HTML
    echo "<tr>";

    // Afficher les en-têtes dynamiquement
    $headers = array_keys($elem['value'][0]); // Récupère les clés du premier élément
    echo "<th>#</th>";
    foreach ($headers as $header) {
        echo "<th>" . htmlspecialchars($header) . "</th>";
    }

    echo "</tr>";

    // Afficher les lignes de données


    if (!empty($elem['value'][0]['TIME_MS']))
    {
      \usort($elem['value'], function ($a, $b) {
        return $b['TIME_MS'] <=> $a['TIME_MS']; // Tri décroissant
      });
    }

    //debug($elem['value'] );
    $i=0;
    foreach ($elem['value'] as $row) {
      $i++;
      if (!empty($row['INFO_BINARY']))
      {
        unset($row['INFO_BINARY']);
      }

        echo "<tr>";
        echo "<td>".$i."</td>";
        foreach ($headers as $header) {
            $value = isset($row[$header]) ? $row[$header] : ''; // Gérer les valeurs nulles

            /*
            if ($header == 'INFO') {
              if (mb_strlen($value)  > 64)
              {
                $value = substr($value,0,64);
              }
            }*/

            if ($header == 'waiting_query' || $header == 'blocking_query') {
              $value = str_replace('<pre', '<pre style="max-width:300px"', \SqlFormatter::format($value));
            }

            $td = '';
            if ($header == "INFO")
            {
              $td = 'style="min-width:1000px"';
            }

            
            echo "<td $td>" . $value . "</td>";
        }
        echo "</tr>";
    }

    echo '</table>';

  echo '</div>';
  echo '</div>';
}

