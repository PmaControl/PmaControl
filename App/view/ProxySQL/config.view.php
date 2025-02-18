
<div class="btn-group" role="group" aria-label="Default button group">

<?php
foreach ($data['table'] as $table_name)
{
    echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" type="button" class="btn btn-primary">'.$table_name.'</a>';
}


    


?>
</div>


<div class="panel-group" id="accordion">
  <div class="panel panel-default">
    


    <div id="collapseOne" class="panel-collapse collapse in">
      
    <div class="btn-group" role="group" aria-label="Default button group">
                <a href="/pmacontrol/fr/ProxySQL/config/6" type="button" class="btn btn-success">LOAD MYSQL SERVER FROM CONFIG;</a>
            </div>
            <div class="btn-group" role="group" aria-label="Default button group">
                <a href="/pmacontrol/fr/ProxySQL/config/6" type="button" class="btn btn-warning">SAVE MYSQL SERVER TO DISK;</a>
                <a href="/pmacontrol/fr/ProxySQL/config/6" type="button" class="btn btn-warning">LOAD MYSQL SERVER TO MEMORY;</a>
            </div>

            <div class="btn-group" role="group" aria-label="Default button group">
                <a href="/pmacontrol/fr/ProxySQL/config/6" type="button" class="btn btn-danger">SAVE MYSQL SERVER TO MEMORY;</a>
                <a href="/pmacontrol/fr/ProxySQL/config/6" type="button" class="btn btn-danger">LOAD MYSQL SERVER TO RUNTIME;</a>
            </div>


        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
      
    </div>
  </div>


  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Collapsible Group Item #2
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse">
      <div class="panel-body">
        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
          Collapsible Group Item #3
        </a>
      </h4>
    </div>
    <div id="collapseThree" class="panel-collapse collapse">
      <div class="panel-body">
        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
      </div>
    </div>
  </div>
</div>






<?php



foreach ($data['table'] as $table_name)
{

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    

    echo '<div class="panel panel-primary" style="overflow:auto">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">'.$table_name.'</h3>';
    echo '</div>';

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
    echo '</div>';
  

    echo '</div>';
    echo '<div class="col-md-6">';
    

    echo '<div class="panel panel-primary" style="overflow:auto">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">runtime_'.$table_name.'</h3>';
    echo '</div>';

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
    echo '</div>';

    
    echo '</div>';
    echo '</div>';

}

