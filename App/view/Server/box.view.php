<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


if (!empty($data['box'])) {
    echo '<table class="table table-condensed table-bordered table-striped" >';

    echo '<tr>';
    echo '<th>'.__("Server").'</th>';
    echo '<th>'.__("Date").'</th>';
    echo '<th>'.__("Error").'</th>';
    echo '<tr>';

    foreach ($data['box'] as $server) {

        echo '<tr>';
        echo '<td><a href="">'.$server['display_name'].'</a></td>';
        echo '<td>'.'2018-02-05'.'</td>';


        $class = "";
        $pos   = strpos($server['error'], "GLI-012");
        if ($pos !== false) {
            $class = "pma pma-danger";
        } else {
            $class = "pma pma-warning";
        }


        $pos = strpos($server['error'], "GLI-19");
        if ($pos !== false) {
            $class = "pma pma-primary";
        }


        echo '<td class="'.$class.'">'.$server['error'].'</td>';
        echo '<tr>';
    }


    echo '</table>';
}