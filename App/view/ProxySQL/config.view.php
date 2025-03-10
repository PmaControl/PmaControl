<?php


\Glial\Synapse\FactoryController::addNode("ProxySQL", "menu", $data['param']);

echo '&nbsp;&nbsp;&nbsp;'; 
echo '<div class="btn-group" role="group" aria-label="Default button group">';

foreach ($data['menu'] as $elems => $sql)
{
  $key_menu = str_replace(' ', '_', $elems);

  $active = "";
  if ($key_menu == $data['current']){
    $active = " active";
  }
  echo '<a href="'.LINK.'ProxySQL/config/'.$data['id_proxysql_server'].'/'.$key_menu.'" type="button" class="btn btn-primary'.$active.'">'.ucfirst(strtolower($elems)).'</a>';
}

$current = str_replace('_', ' ', $data['current']);

echo '</div>';
echo "<br><br>";

echo '<div style="padding:20px; background:rgb(64,64,64); border-radius: 4px;">';
echo '<div class="row">';
echo '<div class="col-md-2">';
echo '<div class="box"><i class="fa fa-hdd-o fa-fw"></i> '.__('Disk').'</div>';
echo '</div>';

echo '<div class="col-md-3">';


echo '<div style="padding-top:10px;">';

echo '<div class="arrow-container" style="background:rgb(92, 184, 92); text-align:center">
    <a href="'.LINK.'ProxySQL/update/'.$data['id_proxysql_server'].'/SAVE/'.$data['current'].'/DISK" class="btn btn-success btn-custom">SAVE '.$current.' TO DISK</a>
    <div class="arrow-left" style="border-color:transparent rgb(92, 184, 92) transparent transparent"></div>
    <div class="arrow-left-tail" style="border-color: rgb(92, 184, 92)  transparent  rgb(92, 184, 92) rgb(92, 184, 92);"></div>
</div>';
echo "<br>";
echo '<div class="arrow-container" style="background:rgb(91, 192, 222); text-align:center">
    <div class="arrow-right-tail" style="border-color: rgb(91, 192, 222) rgb(91, 192, 222)    rgb(91, 192, 222) transparent;"></div>
    <div class="arrow-right" style="border-color: transparent rgb(91, 192, 222)  transparent  rgb(91, 192, 222);"></div>
    <a href="'.LINK.'ProxySQL/update/'.$data['id_proxysql_server'].'/LOAD/'.$data['current'].'/MEMORY" class="btn btn-info btn-custom">LOAD '.$current.' TO MEMORY</a>
</div>';
echo '</div>';

echo '</div>';


echo '<div class="col-md-2">';
echo '<div class="box"><i class="pve-grid-fa fa fa-fw fa-microchip"></i>'.__('Memory').'</div>';
echo '</div>';

echo '<div class="col-md-3">';

echo '<div style="padding-top:10px;">';
echo '<div class="arrow-container" style="background:rgb(240, 173, 78); text-align:center">
    <a href="'.LINK.'ProxySQL/update/'.$data['id_proxysql_server'].'/SAVE/'.$data['current'].'/MEMORY" class="btn btn-warning">SAVE '.$current.' TO MEMORY</a>
    <div class="arrow-left" style="border-color:transparent rgb(240, 173, 78) transparent transparent"></div>
    <div class="arrow-left-tail" style="border-color: rgb(240, 173, 78)  transparent  rgb(240, 173, 78) rgb(240, 173, 78);"></div>
</div>';
echo "<br>";
echo '<div class="arrow-container" style="background:rgb(217, 83, 79); text-align:center">
    <a href="'.LINK.'ProxySQL/update/'.$data['id_proxysql_server'].'/LOAD/'.$data['current'].'/RUNTIME" class="btn btn-danger">LOAD '.$current.' TO RUNTIME</a>
    <div class="arrow-gg" style="border-color:rgb(217, 83, 79) rgb(217, 83, 79) rgb(217, 83, 79) transparent"></div>
    <div class="arrow-right" style="border-color: transparent  #ff0000  transparent rgb(217, 83, 79);"></div>
</div>';
echo '</div>';

echo '</div>';

echo '<div class="col-md-2">';
echo '<div class="box"><i class="fa fa-cogs"></i> '.__('Runtime').'</div>';
echo '</div>';
echo '</div>';

echo '</div>';

echo "<br>";

foreach ($data['table'] as $table_name)
{

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    

    echo '<div class="panel panel-primary" style="overflow:auto">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">'.$table_name.' 
    <a style="float:right; margin-top:-8px" href="'.LINK.'ProxySQL/addline/'.$data['id_proxysql_server'].'/'.$data['current'].'/" class="active btn btn-primary">
    <span class="glyphicon glyphicon-plus"></span> Add a line</a></h3>';
    echo '</div>';

    if (count($data['tables'][$table_name]) > 0)
    {
      echo '<table class="table table-condensed table-bordered table-striped" id="table">';
      $keys = array_keys(end($data['tables'][$table_name]));
      
      echo '<tr>';
      foreach($keys as $key) {
          echo '<th>'.$key.'</th>';
      }
      echo '</tr>';

      foreach($data['tables'][$table_name] as $line)
      {
          echo '<tr>';
          foreach($line as $field => $elem) {
              echo '<td>'.$elem.'</td>'; 
          }
          echo '</tr>';
      }
      echo '</table>';
    }

    
    echo '</div>';
  

    echo '</div>';
    echo '<div class="col-md-6">';
    

    echo '<div class="panel panel-primary" style="overflow:auto;">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">runtime_'.$table_name.'</h3>';
    echo '</div>';


    if (count($data['tables']['runtime_'.$table_name]) > 0)
    {
      echo '<table class="table table-condensed table-bordered table-striped" id="table">';
      $keys = array_keys(end($data['tables']['runtime_'.$table_name]));
      
      echo '<tr>';
      foreach($keys as $key) {
          echo '<th>'.$key.'</th>';
      }
      echo '</tr>';

      foreach($data['tables']['runtime_'.$table_name] as $line)
      {
          echo '<tr>';
          foreach($line as $field => $elem) {
              echo '<td>'.$elem.'</td>'; 
          }
          echo '</tr>';
      }
      echo '</table>';
    }


    echo '</div>';

    
    echo '</div>';
    echo '</div>';

}

