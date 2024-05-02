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

            <!--
            <th><?= __("Tables") ?></th>
            <th><?= __("Rows") ?></th>
            <th><?= __("Size (data)") ?></th>
            <th><?= __("Size (index)") ?></th>
            <th><?= __("Size (free)") ?></th>
-->

        </tr>

        <?php
        $total_data  = array();
        $total_index = array();
        $total_free  = array();
        $total_table = array();
        $total_row   = array();

        $i = 1;
        foreach ($data['database'] as  $elems) {

            echo '<tr>';
            echo '<td>'.$i++.'</td>';
            echo '<td>'.Display::srv($elems['id_mysql_server']).'</td>';
            echo '<td><a href="'.LINK.'mysqlDatabase/mpd/'.$elems['id_mysql_server'].'/'.$elems['schema_name'].'/">'.$elems['schema_name'].'</a></td>';
            echo '<td>'.$elems['character_set_name'].'</td>';
            echo '<td>'.$elems['collation_name'].'</td>';
            
            /*
            echo '<td>'.$elems['tables'].'</td>';
            echo '<td>'.$elems['rows'].'</td>';
            echo '<td>'.$elems['data_length'].'</td>';
            echo '<td>'.$elems['data_free'].'</td>';
            echo '<td>'.$elems['index_length'].'</td>';
            */
            echo '<tr>';

        }

?>
<!--
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
        </tr>-->
    </table>
</div>


<?php

//\Glial\Synapse\FactoryController::addNode("database", "empty", array());
