<?php

use \App\Library\Display;

function format_latency(float $picoseconds, int $precision = 2): string
{
    // picoseconds -> milliseconds
    $ms = $picoseconds / 1_000_000_000;

    if ($ms < 1) return number_format($ms * 1000, $precision) . ' µs';
    if ($ms < 1000) return number_format($ms, $precision) . ' ms';

    $s = $ms / 1000;
    if ($s < 60) return number_format($s, $precision) . ' s';

    $m = $s / 60;
    if ($m < 60) return number_format($m, $precision) . ' m';

    $h = $m / 60;
    if ($h < 24) return number_format($h, $precision) . ' h';

    $d = $h / 24;
    if ($d < 7) return number_format($d, $precision) . ' d';

    return number_format($d / 7, $precision) . ' wk';
}

function format_count($n): string {
    $n = (float)$n;

    if ($n < 1_000) return (string)$n;
    if ($n < 1_000_000) return number_format($n / 1_000, 2) . "k";
    if ($n < 1_000_000_000) return number_format($n / 1_000_000, 2) . "M";
    if ($n < 1_000_000_000_000) return number_format($n / 1_000_000_000, 2) . "B";
    if ($n < 1_000_000_000_000_000) return number_format($n / 1_000_000_000_000, 2) . "T";
    return number_format($n / 1_000_000_000_000_000, 2) . "Q";
}

function format_count_decimals($n, int $precision = 2): string {
    $n = (float)$n;

    if ($n < 1_000) return number_format($n, $precision);
    if ($n < 1_000_000) return number_format($n / 1_000, $precision) . "k";
    if ($n < 1_000_000_000) return number_format($n / 1_000_000, $precision) . "M";
    if ($n < 1_000_000_000_000) return number_format($n / 1_000_000_000, $precision) . "B";
    if ($n < 1_000_000_000_000_000) return number_format($n / 1_000_000_000_000, $precision) . "T";
    return number_format($n / 1_000_000_000_000_000, $precision) . "Q";
}

function highlight_mariadb_keywords(string $sql): string {
    $keywords = ['SELECT','FROM','WHERE','INSERT','INTO','VALUES','UPDATE','SET','DELETE','JOIN','LEFT','RIGHT','INNER','OUTER','ON','AS','AND','OR','NOT','NULL','IS','IN','EXISTS','GROUP','BY','ORDER','HAVING','LIMIT','DISTINCT','CREATE','TABLE','ALTER','DROP','DATABASE','INDEX','VIEW','TRIGGER','PROCEDURE','FUNCTION','PRIMARY','KEY','FOREIGN','REFERENCES','AUTO_INCREMENT','DEFAULT','ENGINE','CHARSET','UNION','ALL','CASE','WHEN','THEN','ELSE','END'];
    return preg_replace_callback('/\b(' . implode('|', $keywords) . ')\b/i', function ($m) {
        return '<b><span style="color:rgb(51,122,183)">' . strtoupper($m[1]) . '</span></b>';
    }, $sql);
}

function human_time_diff_dec($date_start, $precision = 1) {
    $seconds = time() - strtotime($date_start);
    $seconds--;

    if ($seconds < 60) {
        return round($seconds, $precision) . 's';
    }

    $minutes = $seconds / 60;
    if ($minutes < 60) {
        return round($minutes, $precision) . 'm';
    }

    $hours = $minutes / 60;
    if ($hours < 24) {
        return round($hours, $precision) . 'h';
    }

    $days = $hours / 24;
    return round($days, $precision) . 'j';
}

function sort_link($column, $label, $current_sort, $current_order) {

    // Flèches directionnelles
    $arrow_up   = '↑';
    $arrow_down = '↓';

    $arrow_up_bold   = '⬆';
    $arrow_down_bold = '⬇';

    // La colonne actuelle est celle triée ?
    $is_active = ($current_sort === $column);

    // Si trié → on colore la flèche active et on indique le sens
    if ($is_active) {

        if ($current_order === 'asc') {
            $arrows = '<span style="font-weight:bold;color:#2c7be5">'.$arrow_up_bold.'</span> '.$arrow_down;
        } else {
            $arrows = $arrow_up.' <span style="font-weight:bold;color:#2c7be5">'.$arrow_down_bold.'</span>';
        }

        $next_order = ($current_order === 'asc') ? 'desc' : 'asc';

    } else {
        // Colonne non triée → flèches grisées pour montrer que c’est triable
        $arrows = '<span style="color:#ccc">'.$arrow_up.' '.$arrow_down.'</span>';
        $next_order = 'desc'; // on commence toujours par desc
    }

    return "<a href=\"?sort=$column&order=$next_order\" style=\"color:black;text-decoration:none\">$label $arrows</a>";
}

function age_color($date) {
    $seconds = time() - strtotime($date);
    if ($seconds < 60) return 'label-success';
    if ($seconds < 3600) return 'label-warning';
    return 'label-danger';
}

/**
 * Affichage humain de la taille de fenêtre (zoom)
 */
function human_window($seconds) {
    if ($seconds < 120) {
        return $seconds.'s';
    }
    if ($seconds < 3600) {
        return round($seconds / 60).'m';
    }
    if ($seconds < 86400) {
        return round($seconds / 3600).'h';
    }
    return round($seconds / 86400).'d';
}

\Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $param);

// Données passées par le contrôleur
$rows   = $query[$id_mysql_server]['@digest'] ?? [];
$window = $window ?? 86400;
$start_date = $start_date ?? null;
$end_date   = $end_date   ?? null;

// Date de référence pour "Last refresh"
if (!empty($end_date)) {
    $date = $end_date;
} elseif (!empty($rows)) {
    $date = max(array_column($rows, 'date'));
} else {
    $date = date('Y-m-d H:i:s');
}

?>

<style>
.table-container {
    height: calc(100vh - 230px);
    overflow-y: auto;
    overflow-x: hidden;
    border-top: 1px solid #ddd;
}

.fixed-header thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f7f7f7;
}

.fixed-header {
    table-layout: fixed;
    width: 100%;
}

/* Solo ellipsis uniquement sur la colonne Query */
.col-query {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.progress-wrap {
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:6px;
    white-space:nowrap;
}

.glial-progress-bar {
    width:100%;
    height:12px;
    background:#eee;
    border-radius:3px;
    overflow:hidden;
    padding:1px
}

.glial-progress {
    height:100%;
    transition:width 0.9s ease;
    background:rgb(124, 171, 209);
}

.progress-value {
    text-align:right;
    min-width:90px;
}

.qicon {
    margin-left: 3px;
}
</style>

<?php
// Préparation des stats max pour les progress bars
$max_requests    = max(array_column($rows, 'count_star') ?: [1]);
$max_total_time  = max(array_column($rows, 'sum_timer_wait') ?: [1]);
$max_rows_query  = max(array_column($rows, 'rows_query') ?: [1]);
$max_avg_latency = max(array_column($rows, 'avg_timer_wait') ?: [1]);

$max_rows_query_log = ($max_rows_query > 0) ? log($max_rows_query + 1) : 1;

$percent = fn($v,$m) => ($m>0 ? round($v/$m*100,1) : 0);

// Pour les liens de zoom
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
$qs      = $_GET;
?>

<br><br>

<div class="panel panel-primary">
    <div class="panel-heading">
       <h3 class="panel-title">
            <?= __("Queries") ?> : <?= Display::srv($param[0]) ?> - 
            <span class="label <?= age_color($date); ?>">
                <?= human_time_diff_dec($date,0); ?>
            </span>
            &nbsp;Last refresh : <?= $date ?>

            <?php if (!empty($start_date) && !empty($end_date)) : ?>
                &nbsp;&nbsp;
                <span class="label label-info">
                    <?= __('Window') ?> : <?= human_window($window); ?>
                    (<?= htmlspecialchars($start_date); ?> → <?= htmlspecialchars($end_date); ?>)
                </span>
            <?php endif; ?>

            &nbsp;&nbsp;
            <span style="font-size:11px;">
                <?php
                $windowsPresets = [
                    60     => '1m',
                    300    => '5m',
                    900    => '15m',
                    3600   => '1h',
                    21600  => '6h',
                    43200  => '12h',
                    86400  => '24h',
                    172800 => '48h'
                ];
                foreach ($windowsPresets as $w => $label) {
                    $qs['window'] = $w;
                    $link = $baseUrl.'?'.http_build_query($qs);
                    $bold = ($w == $window) ? 'font-weight:bold;text-decoration:underline;' : '';
                    echo '<a href="'.$link.'" style="color:#eee;'.$bold.';margin-right:4px;">'.$label.'</a>';
                }
                ?>
            </span>
       </h3>
    </div>

    <div class="table-container">

    <?php
    echo '<table class="table table-condensed table-bordered table-striped fixed-header" style="margin-bottom:0">';

    echo '<thead><tr>
    <th style="width:60px;text-align:left">Top</th>
    <th style="width:150px;">Schema</th>
    <th>'.__('Normalized query').'</th>
    <th style="width:200px;text-align:right">'.sort_link('count_star','Requests',$sort,$order).'</th>
    <th style="width:200px;text-align:right">'.sort_link('rows_query','Rows sent/Query',$sort,$order).'</th>
    <th style="width:200px;text-align:right">'.sort_link('avg_timer_wait','Avg Latency',$sort,$order).'</th>
    <th style="width:200px;text-align:right">'.sort_link('sum_timer_wait','Total Time',$sort,$order).'</th>
    </tr></thead>';

    echo '<tbody>';

    $i = 0;

    foreach ($rows as $id_digest_schema => $sql) {

        // $id_digest_schema = id_mysql_database__mysql_digest
        $line = $data['queries'][$id_digest_schema] ?? null;
        if (empty($line['digest_text'])) {
            continue;
        }

        $i++;
        if ($i > 200) {
            break;
        }

        $rq   = $sql['rows_query'];

        $p_req   = $percent($sql['count_star'], $max_requests);
        $p_rq    = ($max_rows_query_log > 0) ? round((log($rq + 1) / $max_rows_query_log) * 100, 1) : 0;
        $p_lat   = $percent($sql['avg_timer_wait'], $max_avg_latency);
        $p_total = $percent($sql['sum_timer_wait'], $max_total_time);

        echo "<tr>";
        echo "<td>$i</td>";

        // Gestion schema_name
        if (empty($line['schema_name'])) {
            $line['schema_name'] = "NULL";
        }

        // Icônes / flags
        $icons_q = '';

        if (!empty($sql['has_errors'])) {
            $perc = round($sql['errors_ratio'] * 100, 0).'%';
            $icons_q .= '<span class="qicon" title="Errors: '.$sql['sum_errors'].' ('.$perc.')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#d9534f">
                    <path d="M12 2L1 21h22L12 2zm0 14a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm1-8v6h-2V8h2z"/>
                </svg>
            </span>';
        }

        if (!empty($sql['has_warnings'])) {
            $perc = round($sql['warnings_ratio'] * 100, 0).'%';
            $icons_q .= '<span class="qicon" title="Warnings: '.$sql['sum_warnings'].' ('.$perc.')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#f0ad4e">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                </svg>
            </span>';
        }

        if (!empty($sql['has_no_index_used'])) {
            $perc = round($sql['no_index_ratio'] * 100, 0).'%';
            $icons_q .= '<span class="qicon" title="Full table scan (no_index_used: '.$sql['sum_no_index_used'].') ('.$perc.')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#5bc0de">
                    <path d="M3 3h18v2H3V3zm0 7h18v2H3v-2zm0 7h18v2H3v-2z"/>
                </svg>
            </span>';
        }

        if (!empty($sql['has_no_good_index_used'])) {
            $perc = intval(round($sql['no_good_index_ratio'] * 100)).'%';
            $icons_q .= '<span class="qicon" title="Full table scan (no_good_index_used: '.$sql['sum_no_index_used'].') ('.$perc.')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#31b0d5">
                    <path d="M3 3h18v2H3V3zm0 7h18v2H3v-2zm0 7h18v2H3v-2z"/>
                </svg>
            </span>';
        }

        if (!empty($sql['has_wrong_index'])) {
            $add_perc = !empty($sql['wrong_index_ratio']) ? round($sql['wrong_index_ratio'] * 100, 0).'%' : '';
            $icons_q .= '<span class="qicon" title="Wrong index: selectivity > 10% ('.$add_perc.')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#3d5875">
                    <path d="M13.414 10.586L12 12l1.414 1.414-.707.707L12 12.707l-.707.707.707.707L13.414 10.586z"/>
                </svg>
            </span>';
        }

        // Schema
        echo "<td>{$line['schema_name']}</td>";

        // Colonne requête
        echo '<td class="col-query" style="position:relative;">
                <a href="'.LINK.'Query/digest/'.$param[0].'/'.$line['schema_name'].'/'.$line['digest'].'/"
                    style="color:black;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    '.highlight_mariadb_keywords($line['digest_text']).'
                </a>
                <div style="position:absolute; top:0; right:0;">'.$icons_q.'</div>
            </td>';

        // Requests
        echo '<td>
                <div class="progress-wrap">
                    <span class="progress-value">'.format_count($sql['count_star']).'</span>
                    <div class="glial-progress-bar">
                        <div class="glial-progress" style="width:'.$p_req.'%;"></div>
                    </div>
                </div>
              </td>';

        // Rows sent / Query
        echo '<td>
                <div class="progress-wrap">
                    <span class="progress-value">'.format_count_decimals($rq, 2).'</span>
                    <div class="glial-progress-bar">
                        <div class="glial-progress" style="width:'.$p_rq.'%;"></div>
                    </div>
                </div>
              </td>';

        // Avg Latency
        echo '<td>
                <div class="progress-wrap">
                    <span class="progress-value">'.format_latency($sql['avg_timer_wait']).'</span>
                    <div class="glial-progress-bar">
                        <div class="glial-progress" style="width:'.$p_lat.'%;"></div>
                    </div>
                </div>
              </td>';

        // Total Time
        echo '<td>
                <div class="progress-wrap">
                    <span class="progress-value">'.format_latency($sql['sum_timer_wait']).'</span>
                    <div class="glial-progress-bar">
                        <div class="glial-progress" style="width:'.$p_total.'%;"></div>
                    </div>
                </div>
              </td>';

        echo "</tr>";
    }

    echo '</tbody></table>';

    echo '</div>'; // scroll container
    echo '</div>'; // panel

    if (empty($data['performance_schema'])) {
        echo '<p class="bg-warning" style="padding:10px">performance_schema is not activated on this server.</p>';
    }
    ?>
