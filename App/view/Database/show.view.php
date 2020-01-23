<?php

use App\Library\Display
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
            <th><?= __("Tables") ?></th>
            <th><?= __("Rows") ?></th>
            <th><?= __("Collations") ?></th>
        </tr>

        <?php
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
                            echo '<td>'.$details['size_data'].'</td>';
                            echo '<td>'.$details['size_index'].'</td>';
                            echo '<td>'.$details['size_free'].'</td>';
                            echo '<td>'.$details['tables'].'</td>';
                            echo '<td>'.$details['rows'].'</td>';
                            echo '<td>'.$details['table_collation'].'</td>';
                            echo '<tr>';
                        }
                    }
                }
            }
        }
        ?>
    </table>
</div>