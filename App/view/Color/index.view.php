<?php

echo '<div class="btn-group">';
foreach($data['type'] as $type)
{
    $active = "";
    if ($type == $_GET['type'])
    {
        $active = "active";
    }
    echo '<a href="'.LINK.'Color/index/'.$type.'/" type="button" class="btn btn-primary '.$active.'">'.$type.'</a>';
}  
echo '</div><br><br>';




function color_picker($const,$color)
{

    $color = strtoupper($color);
        return '<div class="colorpicker input-group colorpicker-component">
      <input type="text" value="'.$color.'" class="form-control" />
      <span class="input-group-addon"><i></i></span>
    </div>';


}


echo '<div class="panel panel-primary">';
echo '<div class="panel-heading">';
echo '<h3 class="panel-title">'.$_GET['type'].'</h3>';
echo '</div>';
echo '<div>';


echo '<div class="row">';
echo '<div class="col-md-8">'; //probleme avec alignement color picker




echo '<table class="table table-bordered table-striped" style="margin-bottom:0">';
echo '<tr>';
echo '<th>Constant</th>';
echo '<th>Description</th>';
echo '<th>Font color</th>';
echo '<th>Color</th>';
echo '<th>Background</th>';
echo '<th>Style</th>';
echo '</tr>';


foreach($data['legend'][$_GET['type']] as $elem)
{
    echo '<tr>';
    echo '<td>'.$elem['const'].'</td>';
    echo '<td>'.$elem['name'].'</td>';
    echo '<td style="padding:0">'.color_picker($elem['const'], $elem['font']).'</td>';
    echo '<td style="padding:0">'.color_picker($elem['const'], $elem['color']).'</td>';
    echo '<td style="padding:0">'.color_picker($elem['const'], $elem['background']).'</td>';
    echo '<td>'.$elem['style'].'</td>';

    
    echo '</tr>';
}


echo '</table>';

echo '</div>'; //fin colone 8
echo '<div class="col-md-4" style="text-align:center">';

switch($_GET['type'])
{
    case 'REPLICATION':
        echo '<div style="border:#000 0px solid">';
        \Glial\Synapse\FactoryController::addNode("Dot3", "legend", array());
        echo '</div>';
        break;

}


echo '</div>'; //fin colone 4
echo '</div>'; //fin row


echo '</div></div>';


