<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \App\Library\Display;


echo __ ("The goal there is to ignore the prefix of table to establish the mapping between tables.")."<br />";
echo __("example :")."<br />";

echo "MYTABLE.id_MYTABLE_2 => PREFIX_MYTABLE_2.id_MYTABLE_2<br/>";
echo __("To be able to match the system have to know witch prefix are used on your database")."<br />";


echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>#</th>';
echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Database').'</th>';
echo '<th>'.__('Prefix').'</th>';
echo '<th>'.__('Action').'</th>';
echo '</tr>';

$i = 0;
foreach ($data['prefix'] as $prefix) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.Display::srv($prefix['id_mysql_server']).'</td>';
    echo '<td>'.$prefix['database_name'].'</td>';
    echo '<td>'.$prefix['prefix'].'</td>';
    echo '<td><small><a class="btn-xs btn btn-danger" href="'.LINK.'ForeignKey/remove/'.$prefix['id'].'">'.__("Remove").'</a></small></td>';
    echo '</tr>'."\n";
}
echo '</table>';


//echo '<input id="demo" type="text" class="form-control" value="rgb(255, 128, 0)" />';



echo '<a href="'.LINK.'ForeignKey/add" class="btn btn-primary">'.__("Add a prefix").'</a>';