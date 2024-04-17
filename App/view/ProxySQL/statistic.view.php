<?php

foreach($data['tables'] as $table_name => $table)
{
    echo '<div class="panel panel-primary">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">'.$table_name.'</h3>';
    echo '</div>';

    echo '<table class="table table-condensed table-bordered table-striped" id="table">';
    $keys = array_keys(end($table));
    
    echo '<tr>';
    foreach($keys as $key) {
        echo '<th>'.$key.'</th>';
    }
    echo '</tr>';

    foreach($table as $line)
    {
        echo '<tr>';
        foreach($line as $field => $elem) {


            if ($field === "Create Table")
            {
                echo '<td>'.\SqlFormatter::format($elem).'</td>';
            }
            else
            {
                echo '<td>'.$elem.'</td>';
            }
            
        }
        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';
}
