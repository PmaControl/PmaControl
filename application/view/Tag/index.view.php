<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>#</th>';
echo '<th>'.__('Id').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('Color').'</th>';
echo '<th>'.__('Background').'</th>';
echo '<th>'.__('Display').'</th>';
echo '</tr>';

$i = 0;
foreach ($data['tags'] as $tag) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$tag['id'].'</td>';
    echo '<td class="line-edit" data-name="name" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'tag/update" data-title="Enter Libelle">'.$tag['name'].'</td>';
    echo '<td class="line-edit" data-name="color" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'tag/update" data-title="Enter Color">'.$tag['color'].'</td>';
    echo '<td class="line-edit" data-name="background" data-pk="'.$tag['id'].'" data-type="text" data-url="'.LINK.'tag/update" data-title="Enter Color">'.$tag['background'].'</td>';

    echo '<td><span class="label" style="color:'.$tag['color'].'; background:'.$tag['background'].' ;">'.$tag['name'].'</span></td>';
    echo '</tr>'."\n";
}
echo '</table>';


//echo '<input id="demo" type="text" class="form-control" value="rgb(255, 128, 0)" />';



echo '<a href="'.LINK.'tag/add" class="btn btn-primary">'.__("Add a tag").'</a>';