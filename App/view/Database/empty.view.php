<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<div class="row">';

foreach ($data['empty'] as $server_name => $dbs) {

    echo '<div class="col-md-3">';


    echo '<table class="table table-striped table-bordered">';
    echo '<tr>';
    echo '<th>'.$server_name.'</th>';

    echo '</tr>';
    foreach ($dbs as $db) {
        echo '<tr>';
        echo '<td>'.$db['SCHEMA_NAME'].'</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '</div>';
}
echo '</div>';
