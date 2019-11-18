<table class="pmacontrol">
    <tr>
        <th><?= __('ID') ?> </th>
        <th><?= __('Daemon') ?> </th>
        <th><?= __('Refresh') ?> </th>
 
        <th><?= __('Tools') ?> </th>
        <th><?= __('Status') ?> </th>
        <th><?= __('Remove') ?> </th>

    </tr>

    <?php

    debug($data);

    if (!empty($data['daemon'])) {
        foreach ($data['daemon'] as $daemon) {


            echo '<tr data-id="'.$daemon['id'].'" data-href="'.LINK.'daemon/index/'.$daemon['id'].'">';
            echo '<td>'.$daemon['id_cleaner_main'].'</td>';
            echo '<td>'.$daemon['libelle'].'</td>';
            echo '<td>'.str_replace("_", "-", $daemon['mysql_server_name']).'</td>';
            echo '<td>'.$daemon['ip'].'</td>';
            echo '<td>'.$daemon['main_table'].'</td>';
            echo '<td>';
            echo ' <div class="btn-group" role="group" aria-label="Default button group">';
            echo '<a href="'.LINK.'cleaner/stop/'.$daemon['id_cleaner_main'].'" type="button" class="btn btn-primary" style="font-size:12px">'.' <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:13px"></span> '.__("Stop Daemon").'</a>';
            echo '<a href="'.LINK.'cleaner/start/'.$daemon['id_cleaner_main'].'" type="button" class="btn btn-primary" style="font-size:12px">'.' <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:13px"></span> '.__("Start Daemon").'</a>';
            echo '</div>';
            
            echo '</td>';
            echo '<td>';

            if (empty($daemon['pid'])) {
                echo ' <span class="label label-warning" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">'.__("Stopped").'</span>';
            } elseif (!empty($daemon['pid'])) {


                //put in controller, use anonymous function
                $cmd   = "ps -p ".$daemon['pid'];
                $alive = shell_exec($cmd);

                if (strpos($alive, $daemon['pid']) !== false) {
                    echo ' <span class="label label-success" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;" title="2014-10-29">'.__("Running").' (PID : '.$daemon['pid'].')</span>';
                } else {
                    echo ' <span class="label label-danger" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">'.__("Error").'</span>';
                }
            }

            echo '</td>';
            echo '<td>';

            echo ' <a href="'.LINK.'Cleaner/delete/'.$daemon['id_cleaner_main'].'" type="button" class="btn btn-danger" style="font-size:12px">'.' <span class="glyphicon glyphicon-remove aria-hidden="true" style="font-size:13px"></span> '.__("Delete cleaner").'</a>';

            echo '</td>';


            echo '</tr>';
        }
    }
    ?>



</table>