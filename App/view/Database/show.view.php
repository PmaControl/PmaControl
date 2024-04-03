<?php

use App\Library\Display;
use App\Library\Database;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

function format($bytes, $decimals = 2)
{
    $sz     = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Databases') ?></h3>
    </div>
    <table class="table table-condensed table-bordered table-striped" id="table">
        <tr>
            <th>#</th>
            <th><?= __("Server") ?></th>
            <th><?= __("Database") ?></th>
            <th><?= __("Charset") ?></th>
            <th><?= __("Collation") ?></th>
            <th><?= __("Engine") ?></th>
            <th><?= __("Row format") ?></th>
            <th><?= __("Size (data)") ?></th>
            <th><?= __("Size (index)") ?></th>
            <th><?= __("Size (free)") ?></th>
            <th><?= __("Tag") ?></th>
            <th><?= __("Tables") ?></th>
            <th><?= __("Rows") ?></th>
            <th><?= __("Collations") ?></th>
        </tr>

        <?php
        $total_data  = array();
        $total_index = array();
        $total_free  = array();
        $total_table = array();
        $total_row   = array();

        $i = 1;
        foreach ($data['database'] as $id_mysql_server => $elems) {
            foreach ($elems as $databases) {
                if (!empty($databases['is_proxysql']) && $databases['is_proxysql'] === "1") {
                    continue;
                }

                //add for bug with mysql 5.7
                if (empty($databases['databases'])) {
                    continue;
                }
                $dbs = json_decode($databases['databases'], true);

                foreach ($dbs as $schema => $db_attr) {
                    
                    $size_data = 0;
                    $size_index = 0;
                    $tables = 0;
                    $size_free = 0;
                    $rows = 0;

                    $charset = array();
                    $all_engine = array();
                    $collation = array();
                    $table_collation = array();
                    $all_row_format = array();
                    
                    foreach ($db_attr['engine'] as $engine => $row_formats) {
                        
                        $charset[] = $db_attr['charset'];
                        $all_engine[] = $engine;
                        
                        foreach ($row_formats as $row_format => $details) {

                            $size_data += $details['size_data'];
                            $size_index += $details['size_index'];
                            $tables += $details['tables'];
                            $size_free += $details['size_free'];
                            $rows += $details['rows'];

                            $collation = array_merge($collation,explode(",",$db_attr['collation']));
                            $table_collation = array_merge($table_collation,explode(",",$details['table_collation']));
                            $all_row_format = array_merge($all_row_format,explode(",",$row_format));
                            
                            $total_data[]  = $details['size_data'];
                            $total_index[] = $details['size_index'];
                            $total_free[]  = $details['size_free'];
                            $total_table[] = $details['tables'];
                            $total_row[]   = $details['rows'];
                        }
                    }
                    
                    echo '<tr>';
                    echo '<td>'.$i++.'</td>';
                    echo '<td>'.Display::srv($id_mysql_server).'</td>';
                    echo '<td><a href="'.LINK.'mysqlDatabase/mpd/'.$id_mysql_server.'/'.$schema.'/">'.$schema.'</a></td>';
                    echo '<td>'.implode(',',array_unique($charset)).'</td>';
                    echo '<td>'.implode(',',array_unique($collation)).'</td>';
                    echo '<td>'.implode(",",array_unique($all_engine)).'</td>';
                    echo '<td>'.implode(",",array_unique($all_row_format)).'</td>';
                    echo '<td style="text-align:right;">'.format($size_data).'</td>';
                    echo '<td style="text-align:right;">'.format($size_index).'</td>';
                    echo '<td style="text-align:right;">'.format($size_free).'</td>';
                    echo '<td>'.Database::getTagSize($size_index + $size_data).'</td>';
                    echo '<td style="text-align:right;">'.$tables.'</td>';
                    echo '<td style="text-align:right;">'.number_format($rows, 0, ".", " ").'</td>';
                    echo '<td>'.implode(',',array_unique($table_collation)).'</td>';
                    echo '<tr>';

                }
            }
        }
        ?>
        <tr>
            <th></th>
            <th><?= __("TOTAL") ?></th>
            <th><?= __("Database") ?></th>
            <th><?= __("Charset") ?></th>
            <th><?= __("Collation") ?></th>
            <th><?= __("Engine") ?></th>
            <th><?= __("Row format") ?></th>
            <th style="text-align:right;"><?= format(array_sum($total_data)) ?></th>
            <th style="text-align:right;"><?= format(array_sum($total_index)) ?></th>
            <th style="text-align:right;"><?= format(array_sum($total_free)) ?></th>
            <th><?= Database::getTagSize(array_sum($total_data) + array_sum($total_index)) ?></th>
            <th style="text-align:right;"><?= array_sum($total_table) ?></th>
            <th style="text-align:right;"><?= number_format(array_sum($total_row), 0, ".", " ") ?></th>
            <th><?= __("Collations") ?></th>
        </tr>
    </table>
</div>


<?php

//\Glial\Synapse\FactoryController::addNode("database", "empty", array());
