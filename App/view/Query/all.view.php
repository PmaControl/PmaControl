<?php

use \App\Library\Display;
function picosec_to_ms_formatted(float $picoseconds, int $precision = 2): string {
    return number_format($picoseconds / 1_000_000_000, $precision) . ' ms';
}

function highlight_mariadb_keywords(string $sql): string {
    // Liste des principaux mots-clés réservés MariaDB/MySQL (tu peux en ajouter si besoin)
    $keywords = [
        'SELECT','FROM','WHERE','INSERT','INTO','VALUES','UPDATE','SET','DELETE','JOIN',
        'LEFT','RIGHT','INNER','OUTER','ON','AS','AND','OR','NOT','NULL','IS','IN','EXISTS',
        'GROUP','BY','ORDER','HAVING','LIMIT','DISTINCT','CREATE','TABLE','ALTER','DROP',
        'DATABASE','INDEX','VIEW','TRIGGER','PROCEDURE','FUNCTION','PRIMARY','KEY','FOREIGN',
        'REFERENCES','AUTO_INCREMENT','DEFAULT','ENGINE','CHARSET','UNION','ALL','CASE','WHEN','THEN','ELSE','END'
    ];

    // Création du motif regex pour les mots complets insensibles à la casse
    $pattern = '/\b(' . implode('|', $keywords) . ')\b/i';

    // Remplacement par la mise en forme HTML
    return preg_replace_callback($pattern, function ($matches) {
        return '<b><span style="color:rgb(51, 122, 183)">' . strtoupper($matches[1]) . '</span></b>';
    }, $sql);
}

\Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $param);

echo '<br><br>';

echo '<div class="panel panel-primary">';
echo '<div class="panel-heading">';
echo '   <h3 class="panel-title">'. __("Queries").' : '.Display::srv($param[0]).'</h3>';
echo '</div>';
echo '<div id="queries">';


echo '<table class="table table-condensed table-bordered table-striped" id="table" style="margin-bottom:0px;table-layout: fixed; width: 100%  ;">';
echo '<tr>';


echo '<th style="width:150px;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;">'.__("Schema name").'</th>';
echo '<th width: auto; overflow: hidden; text-overflow: ellipsis;white-space: nowrap;>'.__("Queries").'</th>';
echo '<th style="width:200px; text-align:right">'.__("Count").'</th>';
echo '<th style="width:150px; text-align:right">'.__("Average  execution time").'</th>';
echo '<th style="width:200px; text-align:right">'.__("Load on total time").'</th>';
echo '</tr>';

$i = 0;

foreach($data['queries'] as $digest => $line){


    if (empty($digest)){
        continue;
    }
    $i++;

    if ($i === 1)
    {
        
        $total = $line['SUM_TIMER_WAIT'];
    }

    $percent = round($line['SUM_TIMER_WAIT'] / $total * 100, 0);

    echo '<tr>';
    echo '<td>'.$line['SCHEMA_NAME'].'</td>';
    echo '<td style=" width: auto; overflow: hidden; text-overflow: ellipsis;white-space: nowrap;">';

    if (!empty($line['DIGEST_TEXT']))
    {
        // display:block; height:100%;
        echo '<a href="'.LINK.'Query/digest/'.$param[0].'/'.$line['DIGEST'].'/" style="text-decoration:none; color:black; text-overflow: ellipsis;">';
        echo highlight_mariadb_keywords($line['DIGEST_TEXT']);
        echo '</a>';
    }
    
    echo '</td>';
    echo '<td style="text-align:right">'.number_format($line['COUNT_STAR'], 0, '', ' ').'</td>';
    echo '<td style="text-align:right">'.picosec_to_ms_formatted($line['AVG_TIMER_WAIT']).'</td>';
    echo '<td style="">
    <div class="glial-progress-bar" style="padding:1px">
        <div class="glial-progress" style="width: '.$percent.'%;"></div>
        </div>
        </td>';
    echo '</tr>';


}

echo '</table>';


echo '</div>';
echo '</div>';



if (empty($data['performance_schema']))
{
    echo '<p class="bg-warning" style="padding:10px">'.__('performance_schema is not activated on this server !').'</p>';
}
