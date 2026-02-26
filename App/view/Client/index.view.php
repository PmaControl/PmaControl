<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
use App\Library\Format;

echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';

echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('Monitored').'</th>';
echo '<th>'.__('Libelle').'</th>';
echo '<th>'.__('Servers').'</th>';
echo '<th>'.__('Date').'</th>';
echo '<th>'.__('Logo').'</th>';
echo '</tr>';


$i = 0;

if (!empty($data['client'])) {
    foreach ($data['client'] as $client) {

        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.$client['id'].'</td>';
        echo '<td>';

        $checked = array();
        if ($client['is_monitored'] === "1") {
            $checked = array("checked" => "checked");
        }
        ?>
        <div class="form-group" style="margin: 0">
            <div class="checkbox checbox-switch switch-success" style="margin: 0">
                <label>
                    <?php
                    $computed = array_merge(array("data-id" => $client['id'], "class" => "form-control is_monitored", "type" => "checkbox", "title" => "Monitored"), $checked);
                    echo Form::input("check", "all", $computed);
                    ?>
                    <span></span>
                </label>
            </div>
        </div>

        <?php
        //<input type="checkbox" name="mysql_server['.$client['id'].'][is_monitored]" '.$checked.' />'.


        echo '</td>';
        echo '<td>'.$client['libelle'].'</td>';
        echo '<td>';

        $online = (int) ($client['online_servers'] ?? 0);
        $offline = (int) ($client['offline_servers'] ?? 0);
        $nonMonitored = (int) ($client['non_monitored_servers'] ?? 0);
        $total = (int) ($client['total_servers'] ?? 0);

        echo '<div style="display:flex;  gap:3px; min-width:170px">';

        echo '<span title="'.__('Online').'">'
            .'<svg width="12" height="12" viewBox="0 0 24 24" style="vertical-align:middle; margin-right:4px" aria-hidden="true">'
            .'<circle cx="12" cy="12" r="9" fill="#28a745"></circle>'
            .'</svg>'
            .__('Online').' : <strong>'.$online.'</strong>'
            .'</span>';

        echo '<span title="'.__('Offline').'">'
            .'<svg width="12" height="12" viewBox="0 0 24 24" style="vertical-align:middle; margin-right:4px" aria-hidden="true">'
            .'<circle cx="12" cy="12" r="9" fill="#dc3545"></circle>'
            .'</svg>'
            .__('Offline').' : <strong>'.$offline.'</strong>'
            .'</span>';

        echo '<span title="'.__('Non monitored').'">'
            .'<svg width="12" height="12" viewBox="0 0 24 24" style="vertical-align:middle; margin-right:4px" aria-hidden="true">'
            .'<circle cx="12" cy="12" r="9" fill="#6c757d"></circle>'
            .'<path d="M7 12h10" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>'
            .'</svg>'
            .__('Non monitored').' : <strong>'.$nonMonitored.'</strong>'
            .'</span>';

        echo '<span style="color:#666">'.__('Total').' : '.$total.'</span>';
        echo '</div>';

        echo '</td>';
        echo '<td>'.$client['date'].'</td>';
        echo '<td>'.$client['logo'].'</td>';

        echo '</tr>';
    }
}

echo '</table>';

echo '<div style="float:left" class="btn-group" role="group" aria-label="Default button group"> '
.'<a href="'.LINK.'/client/add/" class="btn btn-primary" style="font-size:12px">'
.'<span class="glyphicon glyphicon-plus" style="font-size:12px"></span> '
.__('Add a client').'</a> </div>';
