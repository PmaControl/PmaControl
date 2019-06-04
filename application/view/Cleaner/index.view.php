<?php

use App\Library\Display;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';
?>
<table class="table table-condensed table-bordered table-striped">
    <tr>
        <th><?= __('ID') ?> </th>
        <th><?= __('Name') ?> </th>
        <th><?= __('Server') ?> </th>
        <th><?= __('Database') ?> </th>
        <th><?= __('Main table') ?> </th>
        <th><?= __('Tools') ?> </th>
        <th><?= __('Status') ?> </th>
        <th><?= __('Remove') ?> </th>
    </tr>

<?php
foreach ($data['cleaner_main'] as $cleaner) {

    //$hightlight = ($cleaner['id_cleaner_main'] === $data['id_cleaner']) ? "highlight_row" : "";
    echo '<tr class="cleaner_main" data-id="'.$cleaner['id_cleaner_main'].'" data-href="'.LINK.'cleaner/index/'.$cleaner['id_cleaner_main'].'">';
    echo '<td>'.$cleaner['id_cleaner_main'].'</td>';
    echo '<td>'.$cleaner['name_cleaner'].'</td>';
    echo '<td>'.Display::srv($cleaner['id']).'</td>';
    //echo '<td>' . $cleaner['ip'] . '</td>';
    echo '<td>'.$cleaner['db'].'</td>';
    echo '<td>'.$cleaner['main_table'].'</td>';
    echo '<td>';


    \Glial\Synapse\FactoryController::addNode("Cleaner", "menu", array($cleaner['id_cleaner_main']));


    echo ' <div class="btn-group" role="group" aria-label="Default button group"> ';
    echo '</div>';

    echo ' <div class="btn-group" role="group" aria-label="Default button group">';
    echo '<a href="'.LINK.'cleaner/stop/'.$cleaner['id_cleaner_main'].'" type="button" class="btn btn-primary" style="font-size:12px">'.' <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:13px"></span> '.__("Stop Daemon").'</a>';
    echo '<a href="'.LINK.'cleaner/start/'.$cleaner['id_cleaner_main'].'" type="button" class="btn btn-primary" style="font-size:12px">'.' <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:13px"></span> '.__("Start Daemon").'</a>';
    //echo '<a href="' . LINK . '" type="button" class="btn btn-primary" style="font-size:12px">' . ' <span class="glyphicon glyphicon-refresh aria-hidden="true" style="font-size:13px"></span> ' . __("Restart Daemon") . '</a>';
    echo '</div>';

    echo '</td>';
    echo '<td>';
    // . '<span class="label label-success" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;" title="2014-10-29">Running</span>'
    // . ' <span class="label label-danger" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">Error</span>'

    if (empty($cleaner['pid'])) {
        echo ' <big><big><span class="label label-warning">'.__("Stopped").'</span></big></big>';
    } elseif (!empty($cleaner['pid'])) {

        //put in controller, use anonymous function
        $cmd   = "ps -p ".$cleaner['pid'];
        $alive = shell_exec($cmd);

        if (strpos($alive, $cleaner['pid']) !== false) {
            echo ' <span class="label label-success" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;" title="2014-10-29">'.__("Running").' (PID : '.$cleaner['pid'].')</span>';
        } else {
            echo ' <span class="label label-danger" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">'.__("Error").'</span>';
        }
    }

    echo '</td>';
    echo '<td>';
    echo ' <a href="'.LINK.'Cleaner/delete/'.$cleaner['id_cleaner_main'].'" type="button" class="btn btn-danger" style="font-size:12px">'.' <span class="glyphicon glyphicon-remove aria-hidden="true" style="font-size:13px"></span> '.__("Delete cleaner").'</a>';
    echo '</td>';
    echo '</tr>';
}
?>

</table>

<a href='<?= LINK ?>cleaner/add/' id="add" class="btn btn-primary"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a cleaner</a>

