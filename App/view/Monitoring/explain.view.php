<?php
use App\Library\SysTooltips;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!empty($data['table'])) {


    $i = 0;
    echo '<table class="table table-condensed table-bordered table-striped">';
    foreach ($data['table'] as $line) {
        $i++;


        if ($i === 1) {

            echo '<tr>';

            echo '<th>'.__("Top").'</th>';
            foreach (array_keys($line) as $var) {
                echo SysTooltips::th($var);
            }
            echo '</tr>';
        }


        echo '<tr>';
        echo '<td>'.$i.'</td>';

        foreach ($line as $val) {
            echo '<td>'.htmlspecialchars((string) $val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</td>';
        }

        echo '</tr>';

    }
    echo "</table>";
} else {
    echo "<b>No data</b>";
}
