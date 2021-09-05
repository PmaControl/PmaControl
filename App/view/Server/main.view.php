<?php

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Glial\Html\Form\Form;
use App\Library\Format;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


$converter = new AnsiToHtmlConverter();

echo '<form action="" method="POST">';

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Available").'</th>';

/*
  echo '<th>';
  ?>
  <div class="form-group">
  <div class="checkbox checbox-switch switch-success">
  <label>
  <?php
  $computed = array("class" => "form-control", "type" => "checkbox", "title" => "Monitored");
  echo Form::input("check", "all", $computed);
  ?>
  <span></span>
  <b>Monitored</b>
  </label>
  </div>
  </div>

  <?php
  //<input id="checkAll" type="checkbox" onClick="toggle(this)" /> '.__("Monitored").'

  echo '</th>';
 *
 */
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
echo '<th>'."general_log".'</th>';
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

        //$style = 'background-color:#EEE; color:#000';
        // cas des erreur
        if (empty($server['is_available']) && ($server['is_monitored'] === "1" && $server['client_monitored'] === "1" )) {
            $style = 'background-color:rgb(217, 83, 79,0.7); color:#000';
        }

        // cas des warning
        if ($server['is_available'] == -1 && ($server['is_monitored'] === "1" && $server['client_monitored'] === "1" )) {
            $style = 'background-color:rgb(240, 202, 78, 0.7); color:#000000'; //f0ad4e   FCF8E3
            //$style = 'gg';
        }

        // acknoledge
        if ($server['is_acknowledged'] !== "0") {
            $style = 'background-color:rgb(92, 184, 92, 0.7); color:#666666';
        }

        // serveur non monitoré
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
        echo '<span class="glyphicon '.(empty($server['is_monitored']) ? "glyphicon-question-sign" : ($server['is_available'] == 1 ? "glyphicon-ok-sign" : "glyphicon-remove-sign")).'" aria-hidden="true"></span>';
        echo '</td>';


        echo '<td style="'.$style.'">'.$server['client'].'</td>';
        echo '<td style="'.$style.'">';
        echo '<big><span class="label label-'.$server['class'].'">'.$server['environment'].'</span></big>';
        echo '</td>';
        echo '<td style="'.$style.'"><a href="'.LINK.'server/id/mysql_server:id:'.$server['id'].'/ts_variable:name:com_select/ts_variable:date:1 hour/ts_variable:derivate:1">';

        echo $server['display_name'];
        echo '</a></td>';
        echo '<td style="'.$style.'">';

        if (!empty($data['tag'][$server['id']])) {
            foreach ($data['tag'][$server['id']] as $tag) {
                echo '<span title="'.$tag['name'].'" class="label" style="color:'.$tag['color'].'; background:'.$tag['background'].'">'.$tag['name'].'</span> ';
            }
        }
        echo '</td>';

        echo '<td style="'.$style.'">'.$server['ip'].'</td>';
        echo '<td style="'.$style.'">'.$server['port'].'</td>';
        echo '<td style="'.$style.'">'.$server['login'].'</td>';
        echo '<td style="'.$style.'" title="">';

        \Glial\Synapse\FactoryController::addNode("Server", "passwd", array($server['passwd']));

        echo '</td>';
        echo '<td style="'.$style.'">';

        if (!empty($data['extra'][$server['id']]['']['version'])) {
            echo Format::mysqlVersion($data['extra'][$server['id']]['']['version'], $data['extra'][$server['id']]['']['version_comment']);
        }

        if (!empty($data['extra'][$server['id']]['']['wsrep_on']) && $data['extra'][$server['id']]['']['wsrep_on'] === "ON") {
            echo '&nbsp;<img title="Galera Cluster" alt="Galera Cluster" height="12" width="12" src="'.IMG.'/icon/logo.svg"/>';
        }


        if (!empty($data['extra'][$server['id']]['']['is_proxy']) && $data['extra'][$server['id']]['']['is_proxy'] === "1") {

            echo '&nbsp;<img title="Galera Cluster" alt="ProxySQL" height="16" width="16" src="'.IMG.'/icon/proxysql.png"/>';
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
            $computed = array_merge(array("data-id" => $server['id'], "class" => "form-control general_log", "type" => "checkbox", "title" => "Monitored"), $checked);



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

        if (!empty($server['is_available'])) {
            if (!empty($data['extra'][$server['id']]['']['date'])) {
                echo $data['extra'][$server['id']]['']['date'];
            }
        } else {
            echo $server['date_refresh'];
        }
        echo '</td>';


        echo '<td style="'.$style.'">';

        if (!empty($data['extra'][$server['id']]['']['ping'])) {
            echo Format::ping($data['extra'][$server['id']]['']['ping']);
        }


        echo '</td>';



        echo '<td style="max-width:400px;'.$style.'" class="">';

        if (strstr($server['error'], '[0m')) {
            $converter = new AnsiToHtmlConverter();
            $html      = $converter->convert($server['error']);

            echo '<pre style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;">'.$html.'</pre>';

            if (!empty($data['last_date'][$server['id']]['date'])) {
                echo "<br>Last online : ".$data['last_date'][$server['id']]['date'];
            }

//$server['error'];
        } else if (strstr($server['error'], 'Call Stack:')) {
            //echo end(explode("\n", $server['error']));
            preg_match_all("/\[[\s0-9:_-]+\]\[ERROR\](.*)/", $server['error'], $output_array);

            if (!empty($output_array[0][0])) {
                echo $output_array[0][0];
            }

            if (!empty($data['last_date'][$server['id']]['date'])) {
                echo '<br><span class="label label-primary">Last online : '.$data['last_date'][$server['id']]['date']."</span>";
                //echo "<br>Last online : ".$data['last_date'][$server['id']]['date'];
            }
            //echo $server['error'];
        } else {
            echo str_replace("\n", '<br>', trim($server['error']));





            if (!empty(trim($server['error']))) {
                if (!empty($data['last_date'][$server['id']]['date'])) {
                    echo '<br><span class="label label-primary">Last online : '.$data['last_date'][$server['id']]['date']."</span>";
                }
            }



            /*
              echo "   -   ".$y." years\n";
              echo $d." days\n";
              echo $h." hours\n";
              echo $m." minutes\n"; */
        }


        $data['last_date'][$server['id']]['date'] = $data['last_date'][$server['id']]['date'] ?? "";

        $date1   = strtotime($data['last_date'][$server['id']]['date']);
        $date2   = time();
        $subTime = $date2 - $date1;

        $d = ($subTime / (60 * 60 * 24));
        $h = ($subTime / (60 * 60)) % 24;
        $m = ($subTime / 60) % 60;

        if (!empty($data['processing'][$server['id']])) {
            echo ' <span class="label label-warning" title="">'.__("Processing").' : '.$data['processing'][$server['id']]['time'].' '.__("seconds").'</span>';
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

        if ($server['is_available'] == -1) {
            echo '&nbsp;'.$server['warning'];
            echo '&nbsp;<span class="label label-warning" style="cursor:pointer;" title="'.__('Kill').'">'        .__('Kill').'</span>';
        }

        echo '</td>';
        echo '<td style="'.$style.'">';

        if (empty($server['is_available']) && $server['is_monitored'] === "1" && $server['client_monitored'] === "1" && $server['is_acknowledged'] === "0") {
            echo '<a href="'.LINK.'server/acknowledge/'.$server['id'].'" type="submit" class="btn btn-primary btn-xs"><span class=" glyphicon glyphicon-star" aria-hidden="true"></span> acknowledge</button>';
        }
        echo '</td>';
        echo '</tr>';
    }
}
echo '</table>';

echo '<input type="hidden" name="is_monitored" value="1" />';
echo '<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Update</button>';
echo '</form>';
