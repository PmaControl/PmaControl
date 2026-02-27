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




function dot_style_select($id, $selected_style, $dot_style_values)
{
    $id = (int) $id;
    $selected_style = (string) $selected_style;

    $html = '<select class="form-control input-sm" name="dot3_legend['.$id.'][style]">';

    foreach ($dot_style_values as $style) {
        $style = (string) $style;
        $selected = ($style === $selected_style) ? ' selected="selected"' : '';
        $escaped_style = htmlspecialchars($style, ENT_QUOTES, 'UTF-8');

        $html .= '<option value="'.$escaped_style.'"'.$selected.'>'.$escaped_style.'</option>';
    }

    $html .= '</select>';

    return $html;
}

echo '<form action="'.LINK.'Color/index/'.$_GET['type'].'/" method="post">';

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
    $id = (int) $elem['id'];
    $const = htmlspecialchars((string) $elem['const'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars((string) $elem['name'], ENT_QUOTES, 'UTF-8');

    echo '<tr>';
    echo '<td>'.$const.'</td>';
    echo '<td>'.$name.'</td>';

    $font = htmlspecialchars(strtoupper((string) $elem['font']), ENT_QUOTES, 'UTF-8');
    echo '<td style="padding:0">'
        .'<div class="colorpicker input-group colorpicker-component">'
        .'<input type="text" name="dot3_legend['.$id.'][font]" value="'.$font.'" class="form-control" />'
        .'<span class="input-group-addon"><i></i></span>'
        .'</div>'
        .'</td>';

    $color = htmlspecialchars(strtoupper((string) $elem['color']), ENT_QUOTES, 'UTF-8');
    echo '<td style="padding:0">'
        .'<div class="colorpicker input-group colorpicker-component">'
        .'<input type="text" name="dot3_legend['.$id.'][color]" value="'.$color.'" class="form-control" />'
        .'<span class="input-group-addon"><i></i></span>'
        .'</div>'
        .'</td>';

    $background = htmlspecialchars(strtoupper((string) $elem['background']), ENT_QUOTES, 'UTF-8');
    echo '<td style="padding:0">'
        .'<div class="colorpicker input-group colorpicker-component">'
        .'<input type="text" name="dot3_legend['.$id.'][background]" value="'.$background.'" class="form-control" />'
        .'<span class="input-group-addon"><i></i></span>'
        .'</div>'
        .'</td>';

    echo '<td>'.dot_style_select($id, $elem['style'], $data['dot_style_values'] ?? array()).'</td>';

    
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

echo '<div style="text-align:left">';
echo '<button type="submit" class="btn btn-primary">'.__("Update").'</button>';
echo '</div>';

echo '</form>';


