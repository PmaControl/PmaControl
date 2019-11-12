<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('MySQL').'</th>';
echo '<th>'.__('SSH').'</th>';
echo '<th><input id="checkAll" type="checkbox" onClick="toggle(this)" /> '.__("Monitored").'</th>';

//echo '<th>'.__('Monitored').'</th>';
echo '<th>'.__('Client').'</th>';
echo '<th>'.__('Environment').'</th>';
echo '<th>'.__('Tags').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('Display name').'</th>';
echo '<th>'.__('IP').'</th>';
echo '<th>'.__('Port').'</th>';

echo '</tr>';


echo '</table>';