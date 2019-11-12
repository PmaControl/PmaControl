<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th style="width: 10%">'.__('Top').'</th>';
echo '<th style="width: 10%">'.__('ID').'</th>';
echo '<th style="width: 20%">'.__('Libelle').'</th>';
echo '<th style="width: 20%">'.__('Key').'</th>';
echo '<th style="width: 20%">'.__('Class').'</th>';
echo '<th style="width: 20%">'.__('Letter').'</th>';
echo '</tr>';

$i =0;
foreach ($data['env'] as $env) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td><span class="label label-'.$env['class'].'">'.$env['id'].'</span></td>';
    echo '<td class="line-edit" data-name="libelle" data-pk="'.$env['id'].'" data-type="text" data-url="'.LINK.'environment/update" data-title="Enter Libelle">'.$env['libelle'].'</td>';
    echo '<td class="line-edit" data-name="key" data-pk="'.$env['id'].'" data-type="text" data-url="'.LINK.'environment/update" data-title="Enter key">'.$env['key'].'</td>';
    echo '<td class="line-edit" data-name="class" data-pk="'.$env['id'].'" data-type="text" data-url="'.LINK.'environment/update" data-title="Enter class">'.$env['class'].'</td>';
    echo '<td class="line-edit" data-name="letter" data-pk="'.$env['id'].'" data-type="text" data-url="'.LINK.'environment/update" data-title="Enter letter">'.$env['letter'].'</td>';
    echo '</tr>';
}


echo '</table>';

?>
<a href="<?= LINK ?>environment/add/NULL" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-triangle-plus" aria-hidden="true"></span> Add environment</a>