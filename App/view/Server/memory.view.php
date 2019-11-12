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
  } */


function format($bytes, $decimals = 2) {
    $sz = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . "o";
}

//debug($data['status']);


echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';
echo '<th rowspan="2">' . __('Server') . '</th>';
echo '<th>' . 'key_buffer' . '</th>';
echo '<th>' . 'query_cache' . '</th>';
echo '<th>' . 'tmp_table' . '</th>';
echo '<th>' . 'innodb_buffer_pool' . '</th>';
echo '<th>' . 'innodb_add_mem_pool' . '</th>';
echo '<th>' . 'innodb_log_buffer' . '</th>';
echo '<th rowspan="2">' . 'max_connections' . '</th>';
echo '<th>' . 'sort_buffer' . '</th>';
echo '<th>' . 'read_buffer' . '</th>';
echo '<th>' . 'read_rnd_buffer' . '</th>';
echo '<th>' . 'join_buffer' . '</th>';
echo '<th rowspan="2">' . 'thread_stack' . '</th>';
echo '<th>' . 'binlog_cache' . '</th>';
echo '<th rowspan="2">' . __('Total') . '</th>';


echo '<th rowspan="2">' . 'used memory' . '</th>';
echo '<th rowspan="2">' . 'Max used connections' . '</th>';
echo '<th rowspan="2">' . __('Physical memory') . '</th>';


echo '</tr>';

echo '<tr>';
echo '<th colspan="6" style="text-align:center">' . 'size' . '</th>';
echo '<th colspan="4" style="text-align:center">' . 'size' . '</th>';
echo '<th style="text-align:center">' . 'size' . '</th>';
echo '</tr>';

$i = 0;

//debug($data['variables']);
if (! empty($data['variables'])) {
    foreach ($data['variables'] as $server => $var) {

        $variable = $var[''];

        /*
         * Si memoire utilisable par mysql > RAM => rouge
         * Si memoire utilisé (avec max user) > RAM => Noir
         */

        if (empty($variable['innodb_buffer_pool_size']))
            $variable['innodb_buffer_pool_size'] = 0;
        if (empty($variable['innodb_additional_mem_pool_size']))
            $variable['innodb_additional_mem_pool_size'] = 0;
        if (empty($variable['innodb_log_buffer_size']))
            $variable['innodb_log_buffer_size'] = 0;


        $totalmemorytest = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] 
            + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size']
            + $variable['max_connections'];

        $totalmemory = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size']
            + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_connections']
            * ( $variable['sort_buffer_size'] + $variable['read_buffer_size'] + $variable['read_rnd_buffer_size'] + $variable['join_buffer_size']
            + $variable['thread_stack'] + $variable['binlog_cache_size']
                );

        $totalmemoryused = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size']
            + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_used_connections']
            * ( $variable['sort_buffer_size'] + $variable['read_buffer_size'] + $variable['read_rnd_buffer_size'] + $variable['join_buffer_size']
            + $variable['thread_stack'] + $variable['binlog_cache_size']
                );


        if (empty($data['memory'][$server])) {
            $mem_kb = 'N/A';
        } else {
            $mem_kb = format($data['memory'][$server] * 1024);
        }



        $data['memory'][$server] = $data['memory'][$server] ?? 8*1024;
        
        $style = ($totalmemory > $data['memory'][$server] * 1024) ? "background:#d9534f; color:#fff" : "";
        $style2 = ($totalmemoryused > $data['memory'][$server] * 1024) ? "background:#000000; color:#fff" : "";
        $style3 = ($totalmemorytest > $data['memory'][$server] * 1024) ? "background:#000000; color:#fff" : "";




        echo '<tr>';
        echo '<td>' . str_replace("_", "-", $server) . '</td>';
        echo '<td style="' . $style3 . '">' . format($variable['key_buffer_size']) . '</td>';
        echo '<td style="' . $style3 . '">' . format($variable['query_cache_size']) . '</td>';
        echo '<td style="' . $style3 . '">' . format($variable['tmp_table_size']) . '</td>';
        echo '<td style="' . $style3 . '">' . format($variable['innodb_buffer_pool_size']) . '</td>';
        echo '<td style="' . $style3 . '">' . format($variable['innodb_additional_mem_pool_size']) . '</td>';
        echo '<td style="' . $style3 . '">' . format($variable['innodb_log_buffer_size']) . '</td>';
        echo '<td>' . $variable['max_connections'] . '</td>';
        echo '<td>' . format($variable['sort_buffer_size']) . '</td>';
        echo '<td>' . format($variable['read_buffer_size']) . '</td>';
        echo '<td>' . format($variable['read_rnd_buffer_size']) . '</td>';
        echo '<td>' . format($variable['join_buffer_size']) . '</td>';
        echo '<td>' . format($variable['thread_stack']) . '</td>';
        echo '<td>' . format($variable['binlog_cache_size']) . '</td>';

        echo '<td style="' . $style . '">' . format($totalmemory) . '</td>';

        echo '<td style="' . $style2 . '">' . format($totalmemoryused) . '</td>';


        //debug($data['status'][$server]);
        
        echo '<td>' . $data['status'][$server]['max_used_connections'] . '</td>';
        echo '<td>' . $mem_kb . '</td>';

        echo '</tr>';
        $i++;
    }
}

echo '</table>';

echo '<div class="well">';
echo '<b>' . __('Memory is calculed as follow :') . '</b>';

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
