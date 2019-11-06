<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Cli\Table;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Cleaner", "menu", array($data['id_cleaner']));
echo '</div>';

function explain($explain)
{

    echo '<table>';
    $ths = array_keys(end($explain));

    echo '<tr>';
    foreach ($ths as $th) {
        echo '<th style="padding:4px;border:1px solid #fff;">'.$th.'</th>';
    }

    foreach ($explain as $line) {
        echo '<tr>';
        foreach ($line as $key => $td) {
            echo '<td style="padding:4px; border:1px solid #fff; background:#000">'.$td.'</td>';
        }
        echo '</tr>';
    }
    echo "</table>";
}
echo "<h3>".__("General's informations")."</h3>";
echo '<table class="table table-condensed table-bordered table-striped">';
?>
<tr>
    <th><?= __('Variables') ?> </th>
    <th><?= __('Values') ?> </th>
</tr>
<tr>
    <td><?= __("Cleaner's name") ?> </td>
    <td><?= $data['libelle'] ?> </td>
</tr>
<tr>
    <td><?= __("Server") ?> </td>
    <td><?= $data['display_name'] ?> (<?= $data['ip'] ?>)</td>
</tr>
<tr>
    <td><?= __("Database") ?> </td>
    <td><?= $data['database'] ?></td>
</tr>
<tr>
    <td><?= __("Table") ?> </td>
    <td><?= $data['main_table'] ?></td>
</tr>

<tr>
    <td><?= __("Query genered") ?> </td>
    <td><?= $data['query'] ?></td>
</tr>
<tr>
    <td><?= __("Number max of line delete in one time") ?> </td>
    <td><?= $data['limit'] ?></td>
</tr>
<tr>
    <td><?= __("Time to wait in second between between each run") ?> </td>
    <td><?= $data['wait_time_in_sec'] ?></td>
</tr>


<tr>
    <td><?= __("Database use for cleaning") ?> </td>
    <td><?= $data['cleaner_db'] ?></td>
</tr>

<tr>
    <td><?= __("Prefix for tables used for clean") ?> </td>
    <td><?= $data['prefix'] ?></td>
</tr>
<tr>
    <td><?= __("Query generated") ?> </td>
    <td><?= $data['sql'] ?></td>
</tr>
<tr>
    <td><?= __("Explain") ?> </td>
    <td><pre style="background:#000; color:#fff"><?= explain($data['explain']) ?></pre></td>
</tr>
<tr>
    <td><?= __("Percent to clean") ?> </td>
    <td>
        ( <?= $data['nb_line_to_purge'] ?> / <?= $data['nb_line_total'] ?> ) <?= $data['percent'] ?>% of table to delete
        <div class="progress" style="background: #ccc;" >
            <div class="progress-bar" role="progressbar" aria-valuenow="70"
                 aria-valuemin="0" aria-valuemax="100" style="width:<?= floor($data['percent']) ?>%">
                <?= $data['percent'] ?>%
            </div>
        </div>
    </td>
</tr>

<tr>
    <td><?= __("Estimation time to delete all rows") ?> </td>
    <td><?= $data['estimation'] ?> <?= __("seconds") ?></td>
</tr>

<?php
echo '</table>';
