<?php

use App\Library\Security\CsrfRender;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function getUnit($bytes, $format = false)
{
    if (empty($bytes)) {
        return "0";
    }
    $sz     = ' KMGTPE';
    $factor = (int) floor(log($bytes) / log(1024));

    $res = array();

    $res['factor']  = $factor;
    $res['unit']    = $sz[$factor];
    $res['value']   = $bytes / pow(1024, $factor);
    $res['arrondi'] = ceil($res['value']) * pow(1024, $factor);

    if ($format) {
        return $res['value'].$res['unit'];
    }

    return $res;
}
$databaseSizeUpdateCsrfAttributes = CsrfRender::attributes($data, 'database_size_update');

echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>#</th>';
echo '<th>'.__('Id').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('Min').'</th>';
echo '<th>'.__('Max').'</th>';
echo '<th>'.__('Color').'</th>';
echo '<th>'.__('Background').'</th>';
echo '<th>'.__('Display').'</th>';
echo '</tr>';

$i = 0;
foreach ($data['color'] as $tag) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$tag['id'].'</td>';
    echo '<td class="line-edit" data-name="label" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'database/sizeUpdate" data-title="Enter Libelle"'.$databaseSizeUpdateCsrfAttributes.'>'.$tag['label'].'</td>';
    echo '<td class="line-edit" data-name="min" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'database/sizeUpdate" data-title="Enter Min"'.$databaseSizeUpdateCsrfAttributes.'>'.getUnit($tag['min'], true).'</td>';
    echo '<td class="line-edit" data-name="max" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'database/sizeUpdate" data-title="Enter Max"'.$databaseSizeUpdateCsrfAttributes.'>'.getUnit($tag['max'], true).'</td>';
    echo '<td class="line-edit" data-name="color" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'database/sizeUpdate" data-title="Enter Color"'.$databaseSizeUpdateCsrfAttributes.'>'.$tag['color'].'</td>';
    echo '<td class="line-edit" data-name="background" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'database/sizeUpdate" data-title="Enter Color"'.$databaseSizeUpdateCsrfAttributes.'>'.$tag['background'].'</td>';

    echo '<td><span class="label" style="color:'.$tag['color'].'; background:'.$tag['background'].' ;">'.$tag['label'].'</span></td>';
    echo '</tr>'."\n";
}
echo '</table>';


//echo '<input id="demo" type="text" class="form-control" value="rgb(255, 128, 0)" />';



//echo '<a href="'.LINK.'tag/add" class="btn btn-primary">'.__("Add a tag").'</a>';
