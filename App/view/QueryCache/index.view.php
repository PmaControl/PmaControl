<?php

use App\Library\Available;
use App\Library\Mysql;
use App\Library\Display;


function format($bytes, $decimals = 2)
{
    $sz     = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__('Server').'</th>';
echo '<th>query_cache_type</th>';
echo '<th>query_cache_size</th>';
echo '<th>query_cache_limit</th>';
echo '<th>query_cache_wlock_invalidate</th>';
echo '</tr>';


if (!empty($data['variable'])) {
    foreach ($data['variable'] as $id_mysql_server => $variable) {


        $style = "";
        if (strtolower($variable['query_cache_type']) === "off")
        {
            $style= "background:#e0e0e0; ";
        }
        
        echo '<tr>';
        echo '<td style="'.$style.'">'.Display::srv($id_mysql_server).'</td>';
        echo '<td style="'.$style.'">'.$variable['query_cache_type'].'</td>';
        echo '<td style="'.$style.'">'.format($variable['query_cache_size']).'</td>';
        echo '<td style="'.$style.'">'.format($variable['query_cache_limit']).'</td>';
        echo '<td style="'.$style.'">'.$variable['query_cache_wlock_invalidate'].'</td>';
        echo '</tr>';
    }
}

echo '</table>';


////////////////////////////




echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__('Server').'</th>';
echo '<th>com_select</th>';
echo '<th>qcache_free_blocks</th>';
echo '<th>qcache_free_memory</th>';
echo '<th>qcache_hits</th>';
echo '<th>qcache_inserts</th>';
echo '<th>qcache_lowmem_prunes</th>';
echo '<th>qcache_not_cached</th>';
echo '<th>qcache_queries_in_cache</th>';
echo '<th>qcache_total_blocks</th>';
echo '<th>usage</th>';
echo '<th>'.__("Percentage").'</th>';
echo '</tr>';

if (!empty($data['cache'])) {
    foreach ($data['cache'] as $id_mysql_server => $variable) {

        if (empty($data['variable'][$id_mysql_server]['query_cache_type'])){
            continue;
        }

        if (strtolower($data['variable'][$id_mysql_server]['query_cache_type']) === "off"){
            continue;
        }

        $style = 'text-align:right';

        echo '<tr>';
        echo '<td>'.Display::srv($id_mysql_server).'</td>';
        echo '<td style="'.$style.'">'.$variable['com_select'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_free_blocks'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_free_memory'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_hits'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_inserts'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_lowmem_prunes'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_not_cached'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_queries_in_cache'].'</td>';
        echo '<td style="'.$style.'">'.$variable['qcache_total_blocks'].'</td>';


        $percent = $variable['qcache_hits'] / ($variable['qcache_hits'] + $variable['qcache_inserts'] + $variable['qcache_not_cached']);

        echo '<td>'.round($percent * 100, 2).'%</td>';


        $percent = round($percent * 100);

        if ($percent >= 80) {
            $color = "progress-bar-success";
        } elseif ($percent >= 45) {
            $color = "progress-bar-warning";
        } else {
            $color = "progress-bar-danger";
        }


        echo '<td>';
        echo '<div class="progress" style="margin-bottom:0">
  <div class="progress-bar '.$color.'" role="progressbar" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$percent.'%">
    <span class="sr-only">'.$percent.'% Complete (success)</span>
  </div>
</div>';
        echo '</td>';

        echo '</tr>';
    }
}

echo '</table>';