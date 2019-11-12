<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//debug($data['box']);

echo '<table class="table table-condensed table-bordered table-striped" >';

echo '<tr>';

echo '<th>'.__("Master").'</th>';
echo '<th>'.__("Slave").'</th>';
echo '<th>'.__("IO").'</th>';
echo '<th>'.__("SQL").'</th>';
echo '<th>'.__("Seconds").'</th>';
echo '<tr>';



foreach ($data['box'] as $line) {
    echo '<tr>';


    $class_m = "";
    if ($line['master']['is_available'] === "0") {
        $class_m = "pma pma-danger";
    }

    $class_s = "";
    if ($line['slave']['is_available'] === "0") {
        $class_s = "pma pma-danger";
    }


    echo '<td class="'.$class_m.'">'.$line['master']['display_name'].'</td>';
    echo '<td class="'.$class_s.'">'.$line['slave']['display_name'].'</td>';


    $class_io = "";
    if ($line['slave_io_running'] == "No") {
        $class_io = "pma-primary";
    }
    if (!empty($line['slave_io_errno'])) {
        $class_io = "pma pma-danger";
    }

    $class_sql = "";
    if ($line['slave_sql_running'] == "No") {
        $class_sql = "pma-primary";
    }
    if (!empty($line['slave_sql_errno'])) {
        $class_sql = "pma pma-danger";
    }

    echo '<td class="'.$class_io.'">'.$line['slave_io_running'];
    if (!empty($line['slave_io_errno'])) {
        echo ' ('.$line['slave_io_errno'].')';
    }


    echo '</td>';
    echo '<td class="'.$class_sql.'">'.$line['slave_sql_running'];

    if (!empty($line['slave_sql_errno'])) {
        echo ' ('.$line['slave_sql_errno'].')';
    }

    echo '</td>';


    $class = "";
    if ($line['seconds'] !== "0") {
        $class = "pma pma-warning";
    }


    echo '<td class="'.$class.'">'.$line['seconds'].'</td>';
    echo '<tr>';
}



echo '</table>';