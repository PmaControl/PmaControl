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

        foreach ($data['database'] as $id_mysql_server => $elems) {
            foreach ($elems as $databases) {
                $dbs = json_decode($databases['databases'], true);

                foreach ($dbs as $schema => $db_attr) {
                    foreach ($db_attr['engine'] as $engine => $row_formats) {
                        foreach ($row_formats as $row_format => $details) {
                            echo '<tr>';
                            echo '<td>'.Display::srv($id_mysql_server).'</td>';
                            echo '<td><a href="'.LINK.'mysql/mpd/'.$id_mysql_server.'/'.$schema.'/">'.$schema.'</a></td>';
                            echo '<td>'.$db_attr['charset'].'</td>';
                            echo '<td>'.$db_attr['collation'].'</td>';
                            echo '<td>'.$engine.'</td>';
                            echo '<td>'.$row_format.'</td>';
                            echo '<td style="text-align:right;">'.format($details['size_data']).'</td>';
                            echo '<td>'.format($details['size_index']).'</td>';
                            echo '<td>'.format($details['size_free']).'</td>';
                            echo '<td>'.Database::getTagSize($details['size_index'] + $details['size_data']).'</td>';
                            echo '<td style="text-align:right;">'.$details['tables'].'</td>';
                            echo '<td style="text-align:right;">'.$details['rows'].'</td>';
                            echo '<td>'.$details['table_collation'].'</td>';
                            echo '<tr>';

                            $total_data[]  = $details['size_data'];
                            $total_index[] = $details['size_index'];
                            $total_free[]  = $details['size_free'];
                            $total_table[] = $details['tables'];
                            $total_row[]   = $details['rows'];
                        }
                    }
                }
            }
        }
        ?>
        <tr>
            <th><?= __("TOTAL") ?></th>
            <th><?= __("Database") ?></th>
            <th><?= __("Charset") ?></th>
            <th><?= __("Collation") ?></th>
            <th><?= __("Engine") ?></th>
            <th><?= __("Row format") ?></th>
            <th><?= format(array_sum($total_data)) ?></th>
            <th><?= format(array_sum($total_index)) ?></th>
            <th><?= format(array_sum($total_free)) ?></th>
            <th><?= Database::getTagSize(array_sum($total_data) + array_sum($total_index)) ?></th>
            <th style="text-align:right;"><?= array_sum($total_table) ?></th>
            <th style="text-align:right;"><?= array_sum($total_row) ?></th>
            <th><?= __("Collations") ?></th>
        </tr>
    </table>
</div>


<?php

//\Glial\Synapse\FactoryController::addNode("database", "empty", array());
