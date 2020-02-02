<?php

use App\Library\Display;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


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
//https://matthiasnoback.nl/2014/07/descriptive-unit-tests/
//https://www.toptal.com/qa/how-to-write-testable-code-and-why-it-matters
//https://medium.com/@sameernyaupane/php-test-driven-development-part-2-unit-testing-c327ba3fbf14
//https://stackify.com/unit-testing-basics-best-practices/
//https://www.cloudways.com/blog/getting-started-with-unit-testing-php/  

function it($m, $p) {
    echo ($p ? '✔︎' : '✘') . " It $m\n";
    if (!$p) {
        $GLOBALS['f'] = 1;
    }
}

function done() {
    if (@$GLOBALS['f'])
        die(1);
}

function format($bytes, $decimals = 2) {
    if (empty($bytes)) {
        return "";
    }
    $sz = ' KMGTP';

    $factor = (int) floor(log($bytes) / log(1024));

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . "o";
}

function getUnit($bytes, $format = false) {
    if (empty($bytes)) {
        return "";
    }
    $sz = ' KMGTP';
    $factor = (int) floor(log($bytes) / log(1024));

    $res = array();

    $res['factor'] = $factor;
    $res['unit'] = $sz[$factor];
    $res['value'] = $bytes / pow(1024, $factor);
    $res['arrondi'] = ceil($res['value']) * pow(1024, $factor);

    if ($format) {
        return $res['value'] . $res['unit'];
    }

    return $res;
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
if (!empty($data['variables'])) {
    foreach ($data['variables'] as $server => $var) {

        $variable = $var[''];

        /*
         * Si memoire utilisable par mysql > RAM => rouge
         * Si memoire utilisé (avec max user) > RAM => Noir
         */



        $variable['innodb_buffer_pool_size'] = $variable['innodb_buffer_pool_size'] ?? 0;
        $variable['innodb_additional_mem_pool_size'] = $variable['innodb_additional_mem_pool_size'] ?? 0;
        $variable['innodb_log_buffer_size'] = $variable['innodb_log_buffer_size'] ?? 0;


        $totalmemorytest = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_connections'];

        $totalmemory = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_connections'] * ( $variable['sort_buffer_size'] + $variable['read_buffer_size'] + $variable['read_rnd_buffer_size'] + $variable['join_buffer_size'] + $variable['thread_stack'] + $variable['binlog_cache_size']
                );

        $totalmemoryused = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_used_connections'] * ( $variable['sort_buffer_size'] + $variable['read_buffer_size'] + $variable['read_rnd_buffer_size'] + $variable['join_buffer_size'] + $variable['thread_stack'] + $variable['binlog_cache_size']
                );




        if (empty($variable['memory_total'])) {
            $mem_kb = 'N/A';
            $style = '';
            $style2 = '';
            $style3 = '';
            $style4 = 'background:rgb(67, 132, 199); color:#fff';
        } else {
            $mem_kb = format($variable['memory_total']);
            $style = ($totalmemory > $variable['memory_total'] ) ? "background:#d9534f; color:#fff" : "";
            $style2 = ($totalmemoryused > $variable['memory_total'] ) ? "background:#000000; color:#fff" : "";
            $style3 = ($totalmemorytest > $variable['memory_total'] ) ? "background:#000000; color:#fff" : "";
            $style4 = '';
        }

        echo '<tr>';
        echo '<td>' . Display::srv($server, false) . '</td>';
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

        echo '<td>' . $variable['max_used_connections'] . '</td>';
        echo '<td style="' . $style4 . '">' . $mem_kb . '</td>';

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





echo '<table class="table table-condensed table-bordered table-striped">';



echo '<tr>';
echo '<th rowspan="3">' . __('Server') . '</th>';


echo '<th colspan="' . (count($data['database']['engine']) * 3) . '" style="text-align:center">' . 'Memories used' . '</th>';


echo '<th rowspan="3">' . 'Suggested parameters' . '</th>';


echo '<th rowspan="3">' . __('Max used connections') . '</th>';
echo '<th rowspan="3">' . 'max_connections' . '</th>';
echo '<th rowspan="3">' . __('Total memory') . '</th>';
echo '<th rowspan="3">' . __('Used memory') . '</th>';


echo '<th rowspan="3">' . __('Physical memory') . '</th>';
echo '</tr>';



echo '<tr>';


foreach ($data['database']['engine'] as $engine) {
    echo '<th colspan="3">' . $engine . '</th>';
}
echo '</tr>';



echo '<tr>';
foreach ($data['database']['engine'] as $engine) {
    echo '<th>' . __('data') . '</th>';
    echo '<th>' . __('index') . '</th>';
    echo '<th>' . __('free') . '</th>';
}
echo '</tr>';





foreach ($data['variables'] as $server => $var) {

    $variable = $var[''];

    $variable['innodb_buffer_pool_size'] = $variable['innodb_buffer_pool_size'] ?? 0;
    $variable['innodb_additional_mem_pool_size'] = $variable['innodb_additional_mem_pool_size'] ?? 0;
    $variable['innodb_log_buffer_size'] = $variable['innodb_log_buffer_size'] ?? 0;
    /*
     * Si memoire utilisable par mysql > RAM => rouge
     * Si memoire utilisé (avec max user) > RAM => Noir
     */


    echo '<tr>';
    echo '<td>' . Display::srv($server, false) . '</td>';



    foreach ($data['database']['engine'] as $engine) {

        $size_data = $data['database']['server'][$server][$engine]['size_data'] ?? 0;
        $size_index = $data['database']['server'][$server][$engine]['size_index'] ?? 0;
        $size_free = $data['database']['server'][$server][$engine]['size_free'] ?? 0;

        echo '<td>' . format($size_data) . '</td>';
        echo '<td>' . format($size_index) . '</td>';
        echo '<td>' . format($size_free) . '</td>';

        if (!empty($data['database']['server'][$server][$engine])) {
            $mem_engine[strtolower($engine)] = $data['database']['server'][$server][$engine];
        }
    }

    $parameter = array();

    //Suggestion pour InnoDB
    if (!empty($mem_engine['innodb'])) {
        $innodb_mem = $mem_engine['innodb']['size_data'] + $mem_engine['innodb']['size_index'];
        if ($innodb_mem > 0) {
            $tmp_buff = 1.1 * $innodb_mem;

            //echo "$tmp_buff = 1.1 * $innodb_mem; ( -- " . format($innodb_mem, 2) . " vs " . format($tmp_buff) . ")<br />";
            //entre 512M et 1G => buffer_pool_size = 1G
            if ($tmp_buff < 1024 * 1024 * 1024 && $tmp_buff >= 1024 * 1024 * 512) {
                $tmp_buff = 1024 * 1024 * 1024;
            }

            //entre 128M et 512M => buffer_pool_size = 512
            if ($tmp_buff < 1024 * 1024 * 512 && $tmp_buff >= 1024 * 1024 * 128) {
                $tmp_buff = 1024 * 1024 * 512;
            }

            //inf à 128M => buffer_pool_size = 128
            if ($tmp_buff < 1024 * 1024 * 128) {
                $tmp_buff = 1024 * 1024 * 128;
            }

            $res = getUnit($tmp_buff);
            //$innodb_buffer_pool_size_arraondi = ceil($res['value']);

            $innodb_buffer_pool_size = $res['arrondi'];

            //debug($variable);
            $ibps = getUnit($variable['innodb_buffer_pool_size'], true);


            $parameter[] = "innodb_buffer_pool_size=" . getUnit($innodb_buffer_pool_size, true) . " #before : " . $ibps;


            //set innodb_buffer_pool_instances
            if ($innodb_buffer_pool_size < 1024 * 1024 * 1024) {
                $innodb_buffer_pool_instances = 1;
            } elseif ($innodb_buffer_pool_size >= 1024 * 1024 * 1024 * 8) {
                $innodb_buffer_pool_instances = 8;
            } else {
                $innodb_buffer_pool_instances = $innodb_buffer_pool_size / (1024 * 1024 * 1024);
            }
            $parameter[] = "innodb_buffer_pool_instances=" . $innodb_buffer_pool_instances . " #before : " . $variable['innodb_buffer_pool_instances'];


            //set  innodb_buffer_pool_chunk_size
            if ($innodb_buffer_pool_instances === 1) {
                $innodb_buffer_pool_chunk_size = $innodb_buffer_pool_size;
            } else {
                $innodb_buffer_pool_chunk_size = $innodb_buffer_pool_size / $innodb_buffer_pool_instances;
            }

            $parameter[] = "innodb_buffer_pool_chunk_size=" . $innodb_buffer_pool_chunk_size . " #with unit : " . getUnit($innodb_buffer_pool_chunk_size, true) . " - before : " . getUnit($variable['innodb_buffer_pool_chunk_size'], true);
            
            $parameter[] = "innodb_flush_log_at_trx_commit = 2";
        }
    }

    //suggestion pour MyISAM
    if (!empty($mem_engine['myisam'])) {
        $myisam_mem = $mem_engine['myisam']['size_index'];

        if ($myisam_mem > 0) {
            $tmp_buff = 1.1 * $myisam_mem;

            //echo "$tmp_buff = 1.1 * $myisam_mem; ( -- " . format($myisam_mem, 2) . " => " . format($tmp_buff, 2) . ")<br />";
            //entre 512M et 1G => buffer_pool_size = 1G
            if ($tmp_buff < 1024 * 1024 * 1024 && $tmp_buff >= 1024 * 1024 * 512) {
                $tmp_buff = 1024 * 1024 * 1024;
            }

            //entre 128M et 512M => buffer_pool_size = 512
            if ($tmp_buff < 1024 * 1024 * 512 && $tmp_buff >= 1024 * 1024 * 128) {
                $tmp_buff = 1024 * 1024 * 512;
            }

            //inf à 128M => buffer_pool_size = 128
            if ($tmp_buff < 1024 * 1024 * 128) {
                $tmp_buff = 1024 * 1024 * 128;
            }

            $res = getUnit($tmp_buff);
            $buffer_pool = ceil($res['value']);

            //debug($variable);
            $ibps = getUnit($variable['key_buffer_size'], true);
            $parameter[] = "key_buffer_size=" . $buffer_pool . $res['unit'] . " #before : " . $ibps;
        }
    }


    $parameter[] = '<span style="text-decoration: line-through;"> slave-skip-errors   = 126,1062,1032,1051</span> # très important !';



    $parameter[] = "read_rnd_buffer_size = 8M";
    $parameter[] = "query_cache_type = OFF";
    $parameter[] = "query_cache_size = 0";
    $parameter[] = "table_open_cache = 10000 #corriger le ulimit";
    
    $parameter[] = "tmp_table_size       = 64M";
    $parameter[] = "max_heap_table_size  = 64M #moteur memory utilisé pour les tables temporaires";



    $parameter_with_font = preg_replace('/(.*)(#.*)/', '$1 <span style="color:#aaa">$2</span>', $parameter);
    $parameters = implode("<br>", $parameter_with_font);

    echo '<td>' . $parameters . '</td>';





    $totalmemorytest = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_connections'];

    $totalmemory = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_connections'] * ( $variable['sort_buffer_size'] + $variable['read_buffer_size'] + $variable['read_rnd_buffer_size'] + $variable['join_buffer_size'] + $variable['thread_stack'] + $variable['binlog_cache_size']
            );

    $totalmemoryused = $variable['key_buffer_size'] + $variable['query_cache_size'] + $variable['tmp_table_size'] + $variable['innodb_buffer_pool_size'] + $variable['innodb_additional_mem_pool_size'] + $variable['innodb_log_buffer_size'] + $variable['max_used_connections'] * ( $variable['sort_buffer_size'] + $variable['read_buffer_size'] + $variable['read_rnd_buffer_size'] + $variable['join_buffer_size'] + $variable['thread_stack'] + $variable['binlog_cache_size']
            );



    if (empty($variable['memory_total'])) {
        $mem_kb = 'N/A';
        $style = '';
        $style2 = '';
        $style3 = '';
        $style4 = 'background:rgb(67, 132, 199); color:#fff';
    } else {
        $mem_kb = format($variable['memory_total']);
        $style = ($totalmemory > $variable['memory_total'] ) ? "background:#d9534f; color:#fff" : "";
        $style2 = ($totalmemoryused > $variable['memory_total'] ) ? "background:#000000; color:#fff" : "";
        $style3 = ($totalmemorytest > $variable['memory_total'] ) ? "background:#000000; color:#fff" : "";
        $style4 = '';
    }


    echo '<td>' . $variable['max_used_connections'] . '</td>';
    echo '<td>' . $variable['max_connections'] . '</td>';

    echo '<td style="' . $style . '">' . format($totalmemory) . '</td>';



    echo '<td  style="' . $style2 . '">' . format($totalmemoryused) . '</td>';


    //debug($data['status'][$server]);




    echo '<td style="' . $style4 . '">' . $mem_kb . '</td>';

    echo '</tr>';
    $i++;
}

echo '</table>';
