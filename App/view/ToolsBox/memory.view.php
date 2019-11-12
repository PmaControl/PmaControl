<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//debug($data);
/*
function format($var)
{
    return round($var/1024/1024/1024,2)  . ' Go';
}*/


function format($bytes, $decimals = 2)
{
    $sz = ' KMGTP';
    $factor = floor((strlen($bytes) -1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " ".@$sz[$factor] . "o";
}





echo '<table class="table">';

echo '<tr>';

echo '<th>' . __('Server') . '</th>';
echo '<th>' . __('key_buffer_size') . '</th>';
echo '<th>' . __('query_cache_size') . '</th>';
echo '<th>' . __('tmp_table_size') . '</th>';
echo '<th>' . __('innodb_buffer_pool_size') . '</th>';
echo '<th>' . __('innodb_add_mem_pool_size') . '</th>';
echo '<th>' . __('innodb_log_buffer_size') . '</th>';
echo '<th>' . __('max_connections') . '</th>';
echo '<th>' . __('sort_buffer_size') . '</th>';
echo '<th>' . __('read_buffer_size') . '</th>';
echo '<th>' . __('read_rnd_buffer_size') . '</th>';
echo '<th>' . __('join_buffer_size') . '</th>';
echo '<th>' . __('thread_stack') . '</th>';
echo '<th>' . __('binlog_cache_size') . '</th>';
echo '<th>' . __('Total') . '</th>';


echo '</tr>';

foreach ($data['variables'] as $server => $variable) {
    echo '<tr>';

    echo '<td>' . str_replace("_","-",$server) . '</td>';
    echo '<td>' . format($variable['key_buffer_size'])  . '</td>';
    echo '<td>' . format($variable['query_cache_size'])  . '</td>';
    echo '<td>' . format($variable['tmp_table_size'])  . '</td>';
    echo '<td>' . format($variable['innodb_buffer_pool_size'])  . '</td>';
    echo '<td>' . format($variable['innodb_additional_mem_pool_size'])  . '</td>';
    echo '<td>' . format($variable['innodb_log_buffer_size'])  . '</td>';
    echo '<td>' . $variable['max_connections']  . '</td>';
    echo '<td>' . format($variable['sort_buffer_size'])  . '</td>';
    echo '<td>' . format($variable['read_buffer_size'])  . '</td>';
    echo '<td>' . format($variable['read_rnd_buffer_size'])  . '</td>';
    echo '<td>' . format($variable['join_buffer_size'])  . '</td>';
    echo '<td>' . format($variable['thread_stack'])  . '</td>';
    echo '<td>' . format($variable['binlog_cache_size'])  . '</td>';
    
    
    $totalmemory = 
            $variable['key_buffer_size']
            + $variable['query_cache_size']
            + $variable['tmp_table_size']
            + $variable['innodb_buffer_pool_size']
            + $variable['innodb_additional_mem_pool_size']
            + $variable['innodb_log_buffer_size']
            + $variable['max_connections']
            * ( $variable['sort_buffer_size']
                + $variable['read_buffer_size']
                + $variable['read_rnd_buffer_size']
                + $variable['join_buffer_size']
                + $variable['thread_stack']
                + $variable['binlog_cache_size']
            );
    
    echo '<td>' . format($totalmemory)  . '</td>';



    echo '</tr>';
}

echo '</table>';

echo '<div class="well">';
echo '<b>'.__('Memory is calculed as follow :').'</b>';

echo '<br /><br />';
echo 'key_buffer_size <br />'
. '+ query_cache_size<br />'
        . '+tmp_table_size<br />'
        . '+innodb_buffer_pool_size<br />'
        . '+innodb_additional_mem_pool_size<br />'
        . '+innodb_log_buffer_size<br />'
        . '+max_connections<br />'
        . '* (sort_buffer_size<br />'
        . ' + read_buffer_size<br />'
        . ' + read_rnd_buffer_size<br />'
        . ' + join_buffer_size<br />'
        . ' + thread_stack<br />'
        . ' + binlog_cache_size)<br />';


echo '</div>';
