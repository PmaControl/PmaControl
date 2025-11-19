<?php

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;
use App\Library\Format;
use App\Library\Debug;

function human_time_diff_dec($date_start, $precision = 1) {
    $seconds = time() - strtotime($date_start);
    $seconds--;

    if ($seconds < 60) {
        return round($seconds, $precision) . 's';
    }

    $minutes = $seconds / 60;
    if ($minutes < 60) {
        return round($minutes, $precision) . 'm';
    }

    $hours = $minutes / 60;
    if ($hours < 24) {
        return round($hours, $precision) . 'h';
    }

    $days = $hours / 24;
    return round($days, $precision) . 'j';
}

function isoToFlag(string $iso): string {
    // Chaque lettre est convertie en Regional Indicator Symbol
    $flag = '';
    $iso = strtoupper($iso);
    if (strlen($iso) === 2) {
        $flag .= mb_chr(127397 + ord($iso[0]));
        $flag .= mb_chr(127397 + ord($iso[1]));
    }
    return $flag;
}


function format_time_ps_with_label($ps, $precision = 2)
{
    if (!is_numeric($ps)) {
        return '<span class="badge bg-secondary">-</span>';
    }

    // Unités
    $units = [
        "ps" => 1,
        "ns" => 1_000,
        "µs" => 1_000_000,
        "ms" => 1_000_000_000,
        "s"  => 1_000_000_000_000,
    ];

    // Conversion
    foreach ($units as $unit => $factor) {
        if ($ps < $factor * 1000) {
            $value = $ps / $factor;
            $formatted = round($value, $precision) . " " . $unit;
            break;
        }
    }

    // Si pas encore défini, on est en secondes
    if (!isset($formatted)) {
        $value = $ps / 1_000_000_000_000;
        $formatted = round($value, $precision) . " s";
    }

    // Détermination du type de label
    // - success : ≤ 1 µs
    // - warning : < 10 ms
    // - danger  : ≥ 10 ms

    $label = "danger"; 

    if ($ps < 2_000_000_000) { 
        $label = "warning";     // < 10 ms
    } 
    if ($ps <= 1_000_000_000) { 
        $label = "success";     // ≤ 1 µs

    } 
    if ($ps <= 450_000_000) { 
        $label = "primary";     // ≤ 1 µs
    } 
    if ($ps <= 250_000_000) { 
        $label = "default";     // ≤ 1 µs
    } 
    return '<span class="label label-' . $label . '">' . $formatted . '</span>';
}


if (empty($_GET['ajax'])){
    echo '<div class="well">';
    FactoryController::addNode("Common", "displayClientEnvironment", array());
    echo '</div>';

    echo '<div width="100%">';


    echo __("Refresh each :")."&nbsp;";
    echo '<div class="btn-group">';
    echo '<a onclick="setRefreshInterval(1000)" type="button" class="btn btn-primary">1 sec</a>';
    echo '<a onclick="setRefreshInterval(2000)" type="button" class="btn btn-primary">2 sec</a>';
    echo '<a onclick="setRefreshInterval(5000)" type="button" class="btn btn-primary">5 sec</a>';
    echo '<a onclick="setRefreshInterval(10000)" type="button" class="btn btn-primary">10 sec</a>';
    echo '<a onclick="stopRefresh()" type="button" class="btn btn-primary">Stop</a>';
    
        echo '<div class="col-md-2" style="text-align: right">';
echo '<a href="'.LINK.'mysql/add/" class="btn btn-primary" style="font-size:12px"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a MySQL server</a>';
echo '</div>';
    
    echo '</div><br /><br />';
    
    echo '<div id="servermain">';
}

$converter = new AnsiToHtmlConverter();


echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
//echo '<th>'.__("Available").'</th>';
echo '<th>'.__("Organizations").'</th>';
echo '<th>'.__("Environment").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>';

echo __('Tags');

echo '</th>';
echo '<th>'.__("IP").':'.__("Port").'</th>';
echo '<th>'.__("SSL").'</th>';
echo '<th>'.__("User").'</th>';
echo '<th>'.__("Password").'</th>';
//echo '<th>'.__("Hostname").'</th>';
echo '<th>'.__("Version").'</th>';
echo '<th>'.__("Latency AVG").'</th>';
echo '<th>'."G_L".'</th>';
echo '<th>'."P_S".'</th>';
echo '<th>'.__("Last refresh").'</th>';
echo '<th>'.__("Ping").'</th>';

echo '<th style="max-width:400px">'.__("Error").'</th>';
//echo '<th>'.__("Acknowledge").'</th>';
echo '</tr>';

$i = 0;

if (!empty($data['servers'])) {

    foreach ($data['servers'] as $server) {
        $i++;

        $style = "";

        $IS_AVAILABLE = true;
        $IS_ACKNOWLEDGE = false;

        if ($i % 2 === 1){
            $intensity = "0.7";
        }
        else{
            $intensity = "0.6";
        }

        $extra = array();
        if (!empty($data['extra'][$server['id']])) {
            $extra = $data['extra'][$server['id']];
        }
        else{
            $style = 'background-color:rgb(150, 150, 150, '.$intensity.'); color:#ffffff';
        }

        // cas des warning
        if (!empty($extra['mysql_available']) )
        {
            if ($extra['mysql_available'] === "2" && ($server['is_monitored'] === "1" && $server['client_monitored'] === "1" )) {
                $style = 'background-color:rgb(240, 202, 78,'.$intensity.'); color:#000000'; //f0ad4e   FCF8E3
            //$style = 'gg';
            }
        }

        //node non primary
        if (!empty($extra['wsrep_on']) && !empty($extra['wsrep_cluster_status']) && $extra['wsrep_on'] === "ON" && $extra['wsrep_cluster_status'] !== "Primary") {
            $style = 'background-color:rgb(240, 202, 78, '.$intensity.'); color:#000000'; //f0ad4e   FCF8E3
            $error_extra = "Galera node is ".$extra['wsrep_cluster_status'];
        }


        //$style = 'background-color:#EEE; color:#000';
        // cas des erreur
        if (empty($extra['mysql_available']) && ($server['is_monitored'] === "1" && $server['client_monitored'] === "1" )) {
            $IS_AVAILABLE = false;
            $style = 'background-color:rgb(217, 83, 79,'.$intensity.'); color:#000';
        }

        // acknoledge GREEN
        if ($server['is_acknowledged'] !== "0") {
            $style = 'background-color:rgb(92, 184, 92, '.$intensity.'); color:#666666';
            $IS_ACKNOWLEDGE = true;
        }

        // serveur non monitoré   BLUE
        if (empty($server['is_monitored']) || empty($server['client_monitored'])) {
            $style = 'background-color:rgb(91, 192, 222, '.$intensity.');  color:#666666';
        }

        $alternate = 'alternate';

        if (!empty($style)) {
            $alternate = '';
            $style     .= "; border-bottom:#fff 1px solid; border-top:#fff 1px solid;";
        }

        echo '<tr class="'.$alternate.'">';
        echo '<td style="'.$style.'">'.$i.'</td>';
        echo '<td style="'.$style.'">'.$server['id'].'</td>';
        //echo '<td style="'.$style.'">';
        //echo '<span class="glyphicon '.(empty($server['is_monitored']) ? "glyphicon-question-sign" : (isset($extra['mysql_available']) && $extra['mysql_available'] == 1 ? "glyphicon-ok-sign" : "glyphicon-remove-sign")).'" aria-hidden="true"></span>';
        //echo '</td>';

        echo '<td style="'.$style.'">'.$server['client'].'</td>';
        echo '<td style="'.$style.'">';
        echo '<big><span class="label label-'.$server['class'].'">'.$server['environment'].'</span></big>';
        echo '</td>';
        echo '<td style="'.$style.'"><a href="'.LINK.'server/id/mysql_server:id:'.$server['id'].'/ts_variable:name:com_select/ts_variable:date:1-hour/ts_variable:derivate:1">';

        echo '<span class="glyphicon '.(empty($server['is_monitored']) ? "glyphicon-question-sign" : (isset($extra['mysql_available']) && $extra['mysql_available'] == 1 ? "glyphicon-ok-sign" : "glyphicon-remove-sign")).'" aria-hidden="true"></span> ';
        
        echo $server['display_name'];
        echo '</a></td>';
        echo '<td style="'.$style.'">';

        if (!empty($data['tag'][$server['id']])) {
            foreach ($data['tag'][$server['id']] as $tag) {
                echo '<span title="'.$tag['name'].'" class="label" style="color:'.$tag['color'].'; background:'.$tag['background'].'">'.$tag['name'].'</span> ';
            }
        }
        echo '</td>';

        echo '<td style="'.$style.'">';

        $flag = '';
        if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // C'est une IPv4 valide
            try {
                $reader = new \GeoIp2\Database\Reader(ROOT.'/data/GeoLite2-Country.mmdb');
                $record = $reader->country($server['ip']);
                $flag = isoToFlag($record->country->isoCode); 
            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                // IP non trouvée, on continue avec un flag vide ou neutre
                $flag = "🌐"; // drapeau par défaut ou vide ""
            }
        } else {
            // Ce n'est pas une IPv4
            $is_ipv4 = false;
        }



        echo $flag."&nbsp;";
        
        echo $server['ip'].":".$server['port'];

        if (!empty($server['is_ssl']) && strtolower($server['is_ssl']) === "1")
        {
            echo "🔒";
        }

        if (!empty($extra['read_only']) && $extra['read_only'] === "ON") {
            echo ' <span title="'.__('READ ONLY').'" class="label" style="color:#ffffff; background:green">R</span> ';
        }

        echo '</td>';

        
        echo '<td style="'.$style.'">';
        if (!empty($extra['have_ssl']) && strtolower($extra['have_ssl']) === "yes")
        {
            echo __("Yes")." 🔒";
        }
        echo '</td>';
        echo '<td style="'.$style.'">'.$server['login'].'</td>';
        echo '<td style="'.$style.'" title="">';

        FactoryController::addNode("Server", "passwd", array($server['passwd']));

        echo '</td>';
        echo '<td style="'.$style.'">';

        $is_proxysql = (empty($extra['is_proxysql'])) ? 0 : $extra['is_proxysql'];

        if (!empty($extra['version'])) {
            echo Format::mysqlVersion($extra['version'], $extra['version_comment'], $is_proxysql);
        }

        if (!empty($extra['wsrep_on']) && $extra['wsrep_on'] === "ON") {
            echo '&nbsp;<img title="Galera Cluster" alt="Galera Cluster" height="12" width="12" src="'.IMG.'/icon/logo.svg"/>';
        }

        echo '</td>';
        echo '<td style="'.$style.'">';
        if (!empty($extra['avg_latency'])) {

            echo format_time_ps_with_label($extra['avg_latency'],2);


            echo format_time_ps_with_label($extra['delta_sum_timer_wait'],2);
            echo format_time_ps_with_label($extra['delta_sum_lock_time'],2);
            
            //echo round($extra['avg_latency'], 3)." ps";
        }
        echo '</td>';


        echo '<td style="'.$style.'">';

        if (!empty($extra['general_log'])) {


            $checked = array();

            if ($extra['general_log'] === "ON") {
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
        //echo $extra['performance_schema'];

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
            //echo $data['last_date'][$server['id']]['date'];

            echo human_time_diff_dec($data['last_date'][$server['id']]['date'],2);
        }
        
        echo '</td>';

        echo '<td style="'.$style.'">';

        if (!empty($extra['mysql_ping'])) {
            echo Format::ping($extra['mysql_ping']);
        }

        echo '</td>';
        echo '<td style="max-width:300px;'.$style.'" class="">';

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

        if ($IS_AVAILABLE === true)
        {
            if (! empty($error_extra))
            {
                echo "<br />";
                echo $error_extra;
                
                if ($extra['wsrep_cluster_status'] !== "Primary") {
                    echo ' <a href="'.LINK.'GaleraCluster/setNodeAsPrimary/'.$server['id'].'" type="submit" class="btn btn-danger btn-xs"><span class=" glyphicon glyphicon-play" aria-hidden="true"></span> SET PRIMARY</button>';
                }
            }
        }
        unset($error_extra);

        if (Debug::$debug){
            echo " pmacontrol Aspirateur tryMysqlConnection {$server['name']} {$server['id']} --debug";
        }
        
        if (empty($extra['mysql_available']) && $server['is_monitored'] === "1" && $server['client_monitored'] === "1" && $server['is_acknowledged'] === "0") {
            echo ' <a href="'.LINK.'server/acknowledge/'.$server['id'].'" type="submit" class="btn btn-primary btn-xs"><span class=" glyphicon glyphicon-star" aria-hidden="true"></span> acknowledge</button>';
        }

        if ($IS_ACKNOWLEDGE === true) {
            echo ' <a href="'.LINK.'server/retract/'.$server['id'].'" type="submit" class="btn btn-primary btn-xs"><span class=" glyphicon glyphicon-star" aria-hidden="true"></span> Retract</button>';
        }

        echo '</td>';

        /*
        echo '<td style="'.$style.'">';
        if (empty($extra['mysql_available']) && $server['is_monitored'] === "1" && $server['client_monitored'] === "1" && $server['is_acknowledged'] === "0") {
            echo '<a href="'.LINK.'server/acknowledge/'.$server['id'].'" type="submit" class="btn btn-primary btn-xs"><span class=" glyphicon glyphicon-star" aria-hidden="true"></span> acknowledge</button>';
        }

        if ($IS_ACKNOWLEDGE === true) {
            echo '<a href="'.LINK.'server/retract/'.$server['id'].'" type="submit" class="btn btn-primary btn-xs"><span class=" glyphicon glyphicon-star" aria-hidden="true"></span> Retract</button>';
        }
        echo '</td>'; */
        echo '</tr>';
    }
}
echo '</table>';


if (empty($_GET['ajax'])){
    echo '</div>';
}