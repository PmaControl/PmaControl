<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
echo '<table class="table">';
echo '<tr>';
echo '<th>' . __('Top') . '</th>';
echo '<th>' . __('Date start') . '</th>';
echo '<th>' . __('Date end') . '</th>';
echo '<th>' . __('Time') . '</th>';
echo '<th>' . __('Commands deleted') . '</th>';
echo '</tr>';


foreach ($data['treatment'] as $treatment) {

    echo '<tr>';
    echo '<td><a href="'.LINK.'Cleaner/index/'.$data['id_cleaner'].'/detail/'.$treatment['id'].'">' . $treatment['id'] . '</a></td>';
    echo '<td>' . $treatment['date_start'] . '</td>';
    echo '<td>' . $treatment['date_end'] . '</td>';
    echo '<td>' . $treatment['time'] . '</td>';
    echo '<td>' . $treatment['item_deleted'] . '</td>';
    echo '</tr>';
}


echo '</table>';
