<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<table class="table table-condensed table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>'.__("Schema").'</th>';
echo '<th>'.__("Table").'</th>';
echo '<th>'.__("Field").'</th>';
echo '<th>'.__("ref_schema").'</th>';
echo '<th>'.__("ref_table").'</th>';
echo '<th>'.__("ref_field").'</th>';
echo '<th>'.__("Operation").'</th>';

echo '</tr>';

foreach($data['fks'] as $fk)
{
    echo '<tr>';
    echo '<td>'.$fk['constraint_schema'].'</td>';
    echo '<td>'.$fk['constraint_table'].'</td>';
    echo '<td>'.$fk['constraint_column'].'</td>';
    echo '<td>'.$fk['referenced_schema'].'</td>';
    echo '<td>'.$fk['referenced_table'].'</td>';
    echo '<td>'.$fk['referenced_column'].'</td>';
    echo '<td>'
    . '<big><span class="label label-success">Add foreign key</span></big>'
            ."&nbsp;"
            
    . '<big><span class="label label-danger">Remove foreign key</span></big>'
            ."&nbsp;"
    . '<big><span href="wqdfgdfg" class="label label-primary cursor">Remove virtual foreign key</span></big>'
            . '  <label href="wg" class="btn-small btn-primary">
    Option 2
  </label>'
    . '</td>';
    echo '</tr>';
    
}

echo '</table>';