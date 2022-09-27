<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \App\Library\Format;
?>



<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __('old') ?> pt-osc</h3>
    </div>


    <?php
    echo '<table class="table table-condensed table-bordered table-striped" id="table">';

    echo '<tr>';
    echo '<th>'.__('Top').'</th>';
    echo '<th>'.__('Environment').'</th>';
    echo '<th>'.__('Server').'</th>';
    echo '<th>'.__('Database').'</th>';
    echo '<th>'.__('Table').'</th>';
    echo '<th>'.__('Rows').'</th>';

    echo '<th>'.__('Data size').'</th>';
    echo '<th>'.__('Index size').'</th>';
    echo '<th>'.__('Free size').'</th>';
    echo '<th>'.__('Creation date').'</th>';
    echo '<th>'.__('Since').'</th>';
    echo '<th>'.__('Action').'</th>';
    echo '</tr>';
    $i = 0;

    $total_data  = 0;
    $total_index = 0;
    $total_free  = 0;

    foreach ($data['ptosc_old'] as $table) {

        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>';
        echo '<big><span class="label label-'.$table['class'].'">'.$table['libelle'].'</span></big>';
        echo '</td>';
        echo '<td>'.$table['display_name'].'</td>';
        echo '<td>'.$table['table_schema'].'</td>';
        echo '<td>'.$table['table_name'].'</td>';
        echo '<td>'.$table['table_rows'].'</td>';
        echo '<td>'.Format::bytes($table['data_length']).'</td>';
        echo '<td>'.Format::bytes($table['index_length']).'</td>';
        echo '<td>'.Format::bytes($table['data_free']).'</td>';
        echo '<td>'.$table['create_time'].'</td>';
        echo '<td><span class="label label-warning">'.$table['days'].' '.__('days').'</span></td>';
        echo '<td><a href="'.LINK.'percona/delOldOscTable/'.$table['id'].'" class="label label-danger">'.__('Drop table').'</a></td>';
        echo '</tr>';
        $total_row   += $table['table_rows'];
        $total_data  += $table['data_length'];
        $total_index += $table['index_length'];
        $total_free  += $table['data_free'];
    }


    if ($i > 0) {
        echo '<tr>';
        echo '<th colspan="5">'.__('Total').'</th>';

        echo '<th>'.$total_row.'</th>';
        echo '<th>'.Format::bytes($total_data).'</th>';
        echo '<th>'.Format::bytes($total_index).'</th>';
        echo '<th>'.Format::bytes($total_free).'</th>';
        echo '<th colspan="3"></th>';

        echo '</tr>';
    }

    echo '</table>';
    ?>
</div>



<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __('currently running') ?></h3>
    </div>


    <?php
    echo '<table class="table table-condensed table-bordered table-striped" id="table">';

    echo '<tr>';
    echo '<th>'.__('Top').'</th>';
    echo '<th>'.__('Environment').'</th>';
    echo '<th>'.__('Server').'</th>';
    echo '<th>'.__('Database').'</th>';
    echo '<th>'.__('Table').'</th>';
    echo '<th>'.__('Rows').'</th>';

    echo '<th>'.__('Data size').'</th>';
    echo '<th>'.__('Index size').'</th>';
    echo '<th>'.__('Free size').'</th>';
    echo '<th>'.__('Creation date').'</th>';
    echo '<th>'.__('Since').'</th>';
    echo '<th>'.__('Action').'</th>';
    echo '</tr>';
    $i = 0;
    foreach ($data['ptosc_new'] as $table) {

        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>';
        echo '<big><span class="label label-'.$table['class'].'">'.$table['libelle'].'</span></big>';
        echo '</td>';
        echo '<td>'.$table['display_name'].'</td>';
        echo '<td>'.$table['table_schema'].'</td>';
        echo '<td>'.$table['table_name'].'</td>';
        echo '<td>'.$table['table_rows'].'</td>';
        echo '<td>'.$table['data_length'].'</td>';
        echo '<td>'.$table['index_length'].'</td>';
        echo '<td>'.$table['data_free'].'</td>';
        echo '<td>'.$table['create_time'].'</td>';
        echo '<td><span class="label label-warning">'.$table['days'].' '.__('days').'</span></td>';
        echo '<td><a class="label label-danger">'.__('Drop table').'</a></td>';
        echo '</tr>';
    }

    echo '</table>';
    ?>
</div>