<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!empty($data['table'])) {


    $table_sql = [];

    $i = 0;
    echo '<table class="table table-condensed table-bordered table-striped">';
    foreach ($data['table'] as $key => $line) {
        $i++;


        if ($i === 1) {

            echo '<tr>';

            echo '<th>'.__("Top").'</th>';
            $j = 0;
            foreach ($line as $var => $val) {
                echo '<th>'.$var.'</th>';

                if ($var == 'SQL_TEXT') {
                    $id_sql = $j;
                } else if ($var == 'CURRENT_SCHEMA') {
                    $id_schema = $j;
                }



                $j++;
            }
            echo '</tr>';
        }


        echo '<tr>';
        echo '<td>'.$i.'</td>';

        $k = 0;
        foreach ($line as $var => $val) {
            echo '<td>'.$val.'</td>';

            if ($k == $id_sql) {
                $table_sql[$line['CURRENT_SCHEMA']] = $val;
            }

            $k++;
        }

        echo '</tr>';

        
        //print_r($val);
    }
    echo "</table>";


    print_r($table_sql);
    // explain !!

    
} else {
    echo "<b>No data</b>";
}

