<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




if ($data['pma_cli']) {
    echo '<div class="well">';
    echo '<div id="chart1" style="margin-top:20px; margin-bottom:20px; margin-left:20px; height:300px;"></div>';
    echo '<div id="chart2" style="margin-top:20px; margin-bottom:20px; margin-left:20px; height:300px;"></div>';
    echo '</div>';
}


if ($data['thread']) {
    echo '<div class="well">';
    echo '<table class="table">';
    echo '<tr>';
    echo '<th>Variable_name</th>';
    echo '<th>Variable_value</th>';
    echo '<tr>';

    foreach ($data['thread'] as $key => $value) {
        echo '<tr>';
        echo '<td>' . $key . '</td>';
        echo '<td>' . $value . '</td>';
        echo '<tr>';
    }



    echo '</table>';
    echo '</div>';
}