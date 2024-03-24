<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 
echo '<table class="table table-condensed table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Schema").'</th>';
echo '<th>'.__("Table").'</th>';
echo '<th>'.__("Field").'</th>';
echo '<th>'.__("ref_schema").'</th>';
echo '<th>'.__("ref_table").'</th>';
echo '<th>'.__("ref_field").'</th>';
echo '<th>'.__("Operation").'</th>';

echo '</tr>';

$i=0;
foreach($data['real_fk'] as $fk)
{
    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['constraint_schema'].'</td>';
    echo '<td>'.$fk['constraint_table'].'</td>';
    echo '<td>'.$fk['constraint_column'].'</td>';
    echo '<td>'.$fk['referenced_schema'].'</td>';
    echo '<td>'.$fk['referenced_table'].'</td>';
    echo '<td>'.$fk['referenced_column'].'</td>';
    echo '<td>'
    . '<big><span class="label label-danger">Remove foreign key</span></big>'
    . '</td>';
    echo '</tr>';
    
}


foreach($data['virtual_fk'] as $key => $fk )
{

    if (in_array($key, $data['real_fk']))
    {
        continue;
    }


    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['constraint_schema'].'</td>';
    echo '<td>'.$fk['constraint_table'].'</td>';
    echo '<td>'.$fk['constraint_column'].'</td>';
    echo '<td>'.$fk['referenced_schema'].'</td>';
    echo '<td>'.$fk['referenced_table'].'</td>';
    echo '<td>'.$fk['referenced_column'].'</td>';
    echo '<td>'
    . '<a href="'.LINK.'virtualForeignKey/addForeignKey/'.$fk['id'].'"><big><span class="label label-success">Add foreign key</span></big></a>'
            ."&nbsp;"
    . '<big><span href="wqdfgdfg" class="label label-primary cursor">Remove virtual foreign key</span></big>'
    . '</label>'
    . '</td>';
    echo '</tr>';

}


echo '</table>';