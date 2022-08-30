<?php

use App\Library\Display;

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Variable').'</th>';
echo '<th>'.__('Value').'</th>';
echo '<th>'.__('Date').'</th>';

echo '</tr>';

foreach ($data['variable'] as $elem) {


    echo '<tr>';
    echo '<td>'.Display::srv($elem['id_mysql_server'], true, LINK."variable/index/id_mysql_server:".$elem['id_mysql_server']).'</td>';
    echo '<td><a href="'.LINK.'variable/index/id_mysql_server:'.$elem['id_mysql_server'].'/variable:'.$elem['variable'].'">'.$elem['variable'].'</a></td>';
    echo '<td>'.$elem['value'].'</td>';
    echo '<td>'.$elem['ROW_START'].'</td>';

    echo '</tr>';
}


echo '</table>';
