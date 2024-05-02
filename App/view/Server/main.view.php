<?php

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;
use App\Library\Format;

if (empty($_GET['ajax'])){
    echo '<div class="well">';
    FactoryController::addNode("Common", "displayClientEnvironment", array());
    echo '</div>';
    echo '<div id="servermain">';
}

$converter = new AnsiToHtmlConverter();

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Available").'</th>';
echo '<th>'.__("Client").'</th>';
echo '<th>'.__("Environment").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>';

echo __('Tags');

echo '</th>';
echo '<th>'.__("IP").'</th>';
echo '<th>'.__("Port").'</th>';
echo '<th>'.__("User").'</th>';
echo '<th>'.__("Password").'</th>';
//echo '<th>'.__("Hostname").'</th>';
echo '<th>'.__("Version").'</th>';
echo '<th>'.__("Latency AVG").'</th>';
echo '<th>'."G_L".'</th>';
echo '<th>'."P_S".'</th>';
echo '<th>'.__("Date refresh").'</th>';
echo '<th>'.__("Ping").'</th>';

echo '<th style="max-width:400px">'.__("Error").'</th>';
echo '<th>'.__("Acknowledge").'</th>';
echo '</tr>';

$i = 0;

if (!empty($data['servers'])) {

    foreach ($data['servers'] as $server) {
        $i++;

        $style = "";

        $extra = array();
        if (!empty($data['extra'][$server['id']][''])) {
            $extra = $data['extra'][$server['id']][''];
        }
        else{
            $style = 'background-color:rgb(150, 150, 150, 0.7); color:#ffffff';
        }

        //$style = 'background-color:#EEE; color:#000';
        // cas des erreur
        if (empty($extra['mysql_available']) && ($server['is_monitored'] === "1" && $server['client_monitored'] === "1" )) {
            
            if (empty($style))
            {
                $style = 'background-color:rgb(217, 83, 79,0.7); color:#000';
            }
        }

        // cas des warning
        if (!empty($extra['mysql_available']) && $extra['mysql_available'] === "2" && ($server['is_monitored'] === "1" && $server['client_monitored'] === "1" )) {
            $style = 'background-color:rgb(240, 202, 78, 0.7); color:#000000'; //f0ad4e   FCF8E3
            //$style = 'gg';
        }

        // acknoledge GREEN
        if ($server['is_acknowledged'] !== "0") {
            $style = 'background-color:rgb(92, 184, 92, 0.7); color:#666666';
        }

        // serveur non monitoré   BLUE
        if (empty($server['is_monitored']) || empty($server['client_monitored'])) {
            $style = 'background-color:rgb(91, 192, 222, 0.7);  color:#666666';
        }

        $alternate = 'alternate';

        if (!empty($style)) {
            $alternate = '';
            $style     .= "; border-bottom:#fff 1px solid; border-top:#fff 1px solid;";
        }

        echo '<tr class="'.$alternate.'">';
        echo '<td style="'.$style.'">'.$i.'</td>';
        echo '<td style="'.$style.'">'.$server['id'].'</td>';
        echo '<td style="'.$style.'">';
        echo '<span class="glyphicon '.(empty($server['is_monitored']) ? "glyphicon-question-sign" : (isset($extra['mysql_available']) && $extra['mysql_available'] == 1 ? "glyphicon-ok-sign" : "glyphicon-remove-sign")).'" aria-hidden="true"></span>';
        echo '</td>';

        echo '<td style="'.$style.'">'.$server['client'].'</td>';
        echo '<td style="'.$style.'">';
        echo '<big><span class="label label-'.$server['class'].'">'.$server['environment'].'</span></big>';
        echo '</td>';
        echo '<td style="'.$style.'"><a href="'.LINK.'server/id/mysql_server:id:'.$server['id'].'/ts_variable:name:com_select/ts_variable:date:1-hour/ts_variable:derivate:1">';

        echo $server['display_name'];
        echo '</a></td>';
        echo '<td style="'.$style.'">';

        if (!empty($data['tag'][$server['id']])) {
            foreach ($data['tag'][$server['id']] as $tag) {
                echo '<span title="'.$tag['name'].'" class="label" style="color:'.$tag['color'].'; background:'.$tag['background'].'">'.$tag['name'].'</span> ';
            }
        }
        echo '</td>';

        echo '<td style="'.$style.'">'.$server['ip'];

        if (!empty($extra['read_only']) && $data['extra'][$server['id']]['']['read_only'] === "ON") {
            echo ' <span title="'.__('READ ONLY').'" class="label" style="color:#ffffff; background:green">R</span> ';
        }

        echo '</td>';
        echo '<td style="'.$style.'">'.$server['port'].'</td>';
        echo '<td style="'.$style.'">'.$server['login'].'</td>';
        echo '<td style="'.$style.'" title="">';

        \Glial\Synapse\FactoryController::addNode("Server", "passwd", array($server['passwd']));

        echo '</td>';
        echo '<td style="'.$style.'">';

        $is_proxysql = (empty($data['extra'][$server['id']]['']['is_proxysql'])) ? 0 : $data['extra'][$server['id']]['']['is_proxysql'];

        if (!empty($data['extra'][$server['id']]['']['version'])) {
            echo Format::mysqlVersion($data['extra'][$server['id']]['']['version'], $data['extra'][$server['id']]['']['version_comment'], $is_proxysql);
        }

        if (!empty($data['extra'][$server['id']]['']['wsrep_on']) && $data['extra'][$server['id']]['']['wsrep_on'] === "ON") {
            echo '&nbsp;<img title="Galera Cluster" alt="Galera Cluster" height="12" width="12" src="'.IMG.'/icon/logo.svg"/>';
        }

        echo '</td>';
        echo '<td style="'.$style.'">';
        if (!empty($data['extra'][$server['id']]['']['query_latency_µs_95'])) {
            echo round($data['extra'][$server['id']]['']['query_latency_µs_95']/1000, 3)." ms";
        }
        echo '</td>';


        echo '<td style="'.$style.'">';

        if (!empty($data['extra'][$server['id']]['']['general_log'])) {


            $checked = array();

            if ($data['extra'][$server['id']]['']['general_log'] === "ON") {
                $checked = array("checked" => "checked");
            }
            ?>
            <div class="form-group" style="margin: 0">
                <div class="checkbox checbox-switch switch-success" style="margin: 0">
                    <label>
                        <?php
                        $computed = array_merge(array("data-id" => $server['id'], "class" => "form-control general_log", "type" => "checkbox", "title" => "Monitored"),
                            $checked);

                        echo Form::input("check", "all", $computed);
                        ?>
                        <span></span>
                    </label>
                </div>
            </div>

            <?php
        }

        echo '</td>';

        echo '<td style="'.$style.'">';
        //echo $data['extra'][$server['id']]['']['performance_schema'];

        if (!empty($extra['performance_schema'])) {

            $checked = array();

            if ($extra['performance_schema'] === "ON") {
                $checked = array("checked" => "checked");
            }
            ?>
            <div class="form-group" style="margin: 0">
                <div class="checkbox checbox-switch switch-success" style="margin: 0">
                    <label>
                        <?php
                        $computed = array_merge(array("data-id" => $server['id'], "class" => "form-control performance_schema", "disabled" => "true", "type" => "checkbox",
                            "title" => "Monitored"), $checked);
                        echo Form::input("check", "all", $computed);
                        ?>
                        <span></span>
                    </label>
                </div>
            </div>

            <?php
        }

        echo '</td>';
        echo '<td style="'.$style.'">';

        if (!empty($data['last_date'][$server['id']]['date'])) {
            echo $data['last_date'][$server['id']]['date'];
        }
        
        echo '</td>';

        echo '<td style="'.$style.'">';

        if (!empty($extra['mysql_ping'])) {
            echo Format::ping($extra['mysql_ping']);
        }

        echo '</td>';
        echo '<td style="max-width:400px;'.$style.'" class="">';

        if (isset($extra['mysql_available']) && $extra['mysql_available']==="0") {
            echo $extra['mysql_error'] .' <span class="label label-primary">Last online : '.$extra['date'].'</span>';
        }

        //debug($data['last_date'][$server['id']]);
        $data['last_date'][$server['id']]['date'] = $data['last_date'][$server['id']]['date'] ?? "";

        $date1   = strtotime($data['last_date'][$server['id']]['date']);

        //debug($date1);

        $date2   = time();
        $subTime = intval($date2 - $date1);


        $d = $subTime / (60 * 60 * 24);
        $h = intval(((int)$subTime / (60 * 60))) % 24;
        $m = (int)($subTime / 60) % 60;

    //debug($data['processing']);

        if (!empty($data['processing'][$server['id']])) {
            echo ' <span class="label label-warning" title="">'.__("Processing").' : '.$data['processing'][$server['id']]['time'].' '.__("seconds")
            .' - pid : '.$data['processing'][$server['id']]['pid'].'</span>';
        }


        if ($d >= 1) {
            echo ' <span class="label label-danger" title="'.$data['last_date'][$server['id']]['date'].'">'.round($d, 0).' '.__("Days").'</span>';
        } else if ($subTime < 60) {
            //echo ' <span class="label label-success" title="'.$data['last_date'][$server['id']]['date'].'">'.__("OK").'</span>';
        } else if ($subTime >= 60 && $subTime < 3600) {
            echo ' <span class="label label-warning" title="'.$data['last_date'][$server['id']]['date'].'"><i class="glyphicon glyphicon-warning-sign"></i> '.$m.' '.__("Minutes").'</span>';
        } else {
            echo ' <span class="label label-warning" title="'.$data['last_date'][$server['id']]['date'].'">'.$h.' '.__("hours").'</span>';
        }

        if (!empty($extra['mysql_available']) && $extra['mysql_available'] === "2") {
            echo '&nbsp;'.$server['warning'];
            echo '&nbsp;<span class="label label-warning" style="cursor:pointer;">'.__('Kill').'</span>';
        }

        echo '</td>';
        echo '<td style="'.$style.'">';

        if (empty($extra['mysql_available']) && $server['is_monitored'] === "1" && $server['client_monitored'] === "1" && $server['is_acknowledged'] === "0") {
            echo '<a href="'.LINK.'server/acknowledge/'.$server['id'].'" type="submit" class="btn btn-primary btn-xs"><span class=" glyphicon glyphicon-star" aria-hidden="true"></span> acknowledge</button>';
        }
        echo '</td>';
        echo '</tr>';
    }
}
echo '</table>';


if (empty($_GET['ajax'])){
    echo '</div>';
}



