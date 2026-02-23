<?php

use \Glial\Synapse\FactoryController;

if (!function_exists('formatBytesToKbMbTb')) {
    function formatBytesToKbMbTb($bytes)
    {
        $bytes = (float) $bytes;

        if ($bytes >= 1099511627776) { // 1024^4
            return number_format($bytes / 1099511627776, 2).' TB';
        }
        
        if ($bytes >= 1073741824) { // 1024^3
            return number_format($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) { // 1024^2
            return number_format($bytes / 1048576, 2).' MB';
        }

        return number_format($bytes / 1024, 2).' KB';
    }
}

if (!function_exists('formatIntegerWithSpaceSeparator')) {
    function formatIntegerWithSpaceSeparator($value)
    {
        return number_format((float) $value, 0, '.', ' ');
    }
}

if (!function_exists('sortHeaderWithArrows')) {
    function sortHeaderWithArrows($column, $label, $currentSort, $currentOrder)
    {
        $query = $_GET;

        $query['sort'] = $column;
        $query['order'] = 'asc';
        $ascUrl = '?'.http_build_query($query);

        $query['order'] = 'desc';
        $descUrl = '?'.http_build_query($query);

        $ascStyle = ($currentSort === $column && $currentOrder === 'asc')
            ? 'font-weight:bold;color:#000;'
            : 'color:#666;';

        $descStyle = ($currentSort === $column && $currentOrder === 'desc')
            ? 'font-weight:bold;color:#000;'
            : 'color:#666;';

        return $label
            .' <a href="'.$ascUrl.'" style="text-decoration:none;'.$ascStyle.'">↑</a>'
            .' <a href="'.$descUrl.'" style="text-decoration:none;'.$descStyle.'">↓</a>';
    }
}


    ?>
    <div >
    <div style="float:left; padding-right:10px;"><?= FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
    <div style="float:left; padding-right:10px;"><?= FactoryController::addNode("MysqlDatabase", "menu", $data['param']); ?></div>
    <div style="float:left;"><?= FactoryController::addNode("MysqlTable", "menu", $data['param']); ?></div>
    </div> 
    <div style="clear:both"></div>
    <?php

echo "<br />";

?>
<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">

                <?= __("Database") ?> : <?= $data['table_schema'] ?>
                </h3>
        </div>
        <div>

<?php



echo '<table class="table table-condensed table-bordered table-striped" id="table">';
$currentSort = $data['sort'] ?? 'total';
$currentOrder = $data['order'] ?? 'desc';

echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.sortHeaderWithArrows('table', __("Table"), $currentSort, $currentOrder).'</th>';
echo '<th>'.__("Action").'</th>';

echo '<th style="text-align:right;">'.sortHeaderWithArrows('rows', __("Rows"), $currentSort, $currentOrder).'</th>';

echo '<th>'.__("Type").'</th>';
echo '<th>'.sortHeaderWithArrows('engine', __("Engine"), $currentSort, $currentOrder).'</th>';
echo '<th>'.sortHeaderWithArrows('row_format', __("Row format"), $currentSort, $currentOrder).'</th>';
echo '<th>'.sortHeaderWithArrows('collation', __("Collation"), $currentSort, $currentOrder).'</th>';
echo '<th style="text-align:right;">'.sortHeaderWithArrows('size', __("Size"), $currentSort, $currentOrder).'</th>';
echo '<th style="text-align:right;">'.sortHeaderWithArrows('index', __("Index"), $currentSort, $currentOrder).'</th>';
echo '<th style="text-align:right;">'.sortHeaderWithArrows('overhead', __("Overhead"), $currentSort, $currentOrder).'</th>';
echo '<th style="text-align:right;">'.sortHeaderWithArrows('total', __("Total"), $currentSort, $currentOrder).'</th>';
echo '</tr>';
$i=0;
foreach($data['table'] as $fk ){

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['TABLE_NAME'].'</td>';
    echo '<td></td>';
    echo '<td style="text-align:right;">'.formatIntegerWithSpaceSeparator($fk['TABLE_ROWS']).'</td>';
    echo '<td>'.$fk['TABLE_TYPE'].'</td>';
    
    echo '<td>'.$fk['ENGINE'].'</td>';
    echo '<td>'.$fk['ROW_FORMAT'].'</td>';
    echo '<td>'.$fk['TABLE_COLLATION'].'</td>';
    $totalLength = (float)($fk['TOTAL_LENGTH'] ?? ((float)$fk['DATA_LENGTH'] + (float)$fk['INDEX_LENGTH'] + (float)$fk['DATA_FREE']));

    echo '<td style="text-align:right;">'.formatBytesToKbMbTb($fk['DATA_LENGTH']).'</td>';
    echo '<td style="text-align:right;">'.formatBytesToKbMbTb($fk['INDEX_LENGTH']).'</td>';
    echo '<td style="text-align:right;">'.formatBytesToKbMbTb($fk['DATA_FREE']).'</td>';
    echo '<td style="text-align:right;">'.formatBytesToKbMbTb($totalLength).'</td>';

    echo '</tr>';

}

echo '</table>';



?>


</div></div>