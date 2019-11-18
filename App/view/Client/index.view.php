<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';

echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('Libelle ').'</th>';
echo '<th>'.__('Date').'</th>';
echo '<th>'.__('Logo').'</th>';
echo '</tr>';


$i = 0;

if (!empty($data['client'])) {
    foreach ($data['client'] as $client) {

        $i++;
        echo '<tr>';


        echo '<td>'.$i.'</td>';
        echo '<td>'.$client['id'].'</td>';
        echo '<td>'.$client['libelle'].'</td>';
        echo '<td>'.$client['date'].'</td>';
        echo '<td>'.$client['logo'].'</td>';

        echo '</tr>';
    }
}

echo '</table>';

echo '<div style="float:left" class="btn-group" role="group" aria-label="Default button group"> '
. '<a href="'.LINK.'/client/add/" class="btn btn-primary" style="font-size:12px">'
    . '<span class="glyphicon glyphicon-plus" style="font-size:12px"></span> '
    .__('Add a client'). '</a> </div>';