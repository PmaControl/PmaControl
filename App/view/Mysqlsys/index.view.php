<?php

use App\Library\Security\CsrfRender;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
use App\Library\SysTooltips;

$data = $data ?? array();
$selectedMysqlServerId = $data['selected_mysql_server_id'] ?? null;
$selectedMysqlServerFound = !empty($data['selected_mysql_server_found']);
$mysqlsysVersion = htmlspecialchars((string) ($data['variables'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$mysqlsysVersionUnsupported = !empty($data['mysqlsys_version_unsupported']);
$mysqlsysUpdateConfigCsrfAttributes = CsrfRender::attributes($data, 'mysqlsys_update_config');

echo '<div class="well">';

\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

function remove($array, $selectedMysqlServerId = null) {
    $params = explode("/", trim($_GET['url'] ?? '', "/"));
    $hasMysqlServer = false;
    $removeMysqlServer = in_array('mysql_server', $array, true);

    foreach ($params as $key => $param) {
        if (strstr($param, 'mysql_server:')) {
            $hasMysqlServer = true;
        }

        foreach ($array as $var) {
            if (strstr($param, $var . ':')) {
                unset($params[$key]);
            }
        }
    }

    if ($selectedMysqlServerId !== null && !$hasMysqlServer && !$removeMysqlServer) {
        $params[] = 'mysql_server:id:' . (int) $selectedMysqlServerId;
    }

    $ret = implode('/', $params);

    return $ret;
}

echo '<br /><br />';
echo '<form action="" method="get">';
echo __("Server") . " : ";

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto")));
//echo Form::select("mysql_server", "id", $data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
echo ' ';


echo '<button type="submit" class="btn btn-primary">Filter</button>';

if ($selectedMysqlServerId !== null && $selectedMysqlServerFound) {
    echo ' ';
    echo '<a href="' . LINK . 'Mysqlsys/reset/' . (int) $selectedMysqlServerId . '" class="btn btn-warning" role="button" title="This option will truncate all table of PERFORMANCE_SCHEMA to reset statistiques in MySQL-sys" aria-pressed="true">Reset Statistics</a>';
    echo ' ';
    echo '<a href="' . LINK . 'Mysqlsys/drop/' . (int) $selectedMysqlServerId . '" class="btn btn-danger" role="button" aria-pressed="true" title="This will DROP DATABASE `sys`; after this you can reinstall Mysql-sys for new version for example">Uninstall MySQL-sys</a>';
}

echo '</form>';
echo '</div>';

//debug($data['innodb']);

if ($selectedMysqlServerId !== null && $selectedMysqlServerFound) {

    if (!empty($data['view_available']) && count($data['view_available']) > 0 && !empty($data['innodb'])) {
        ?>
        <div class="row">
            <div class="col-md-2">

                <?php
                echo '<table class="table table-condensed table-bordered table-striped">';

                echo '<tr>';
                echo '<th>' . __("Reporting") . '</th>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>';

                $i = 0;
                foreach ($data['view_available'] as $view) {
                    //$url = $_GET['url'];

                    $i++;
                    $url = remove(array("mysqlsys"), $selectedMysqlServerId);

                    if (!empty($_GET['mysqlsys']) && $view == $_GET['mysqlsys']) {
                        echo '<a href="' . LINK . $url . '/mysqlsys:' . $view . '"><b>' . $i . '. ' . $view . '</b></a><br/>';
                    } else {
                        echo '<a href="' . LINK . $url . '/mysqlsys:' . $view . '">' . $i . '. ' . $view . '</a><br/>';
                    }
                }

                echo '</td>';
                echo '</tr>';
                echo '</table>';
                ?>

            </div>
            <div class="col-md-10">
                <?php
                $i = 0;

                if (!empty($data['table'])) {

                    echo '<table class="table table-condensed table-bordered table-striped">';
                    foreach ($data['table'] as $key => $line) {
                        $i++;

                        if ($i === 1) {
                            echo '<tr>';

                            echo '<th>' . __("Top") . '</th>';
                            foreach ($line as $var => $val) {
                                echo SysTooltips::th($var);
                            }
                            echo '</tr>';
                        }

                        echo '<tr>';
                        echo '<td>' . $i . '</td>';
                        foreach ($line as $var => $val) {


                            if ($data['name_table'] == 'sys_config' && $var == "value") {

                                echo '<td class="line-edit" data-name="' . $line['variable'] . '" data-pk="' . (int) $selectedMysqlServerId . '" data-type="text" data-url="' . LINK . 'mysqlsys/updateConfig" data-title="Enter Libelle"' . $mysqlsysUpdateConfigCsrfAttributes . '>';
                            } else {
                                echo '<td>';
                            }

                            if ($var == "query") {
                                $query = (string)($val ?? '');
                                $queryExcerpt = $query;

                                echo '<button class="btn btn-default btn-xs" type="button" data-toggle="collapse" data-target="#collapseExample' . $i . '">'
                                . '<i class="fa fa-plus"></i></button>';
                                echo ' <span>';

                                echo '<div class="collapse" id="collapseExample' . $i . '">' . SqlFormatter::format($query) . '</div>';

                                $nb_length = mb_strlen($query, 'UTF-8');

                                if ($nb_length > 64) {
                                    $queryExcerpt = mb_substr($query, 0, 32, 'UTF-8') . "..." . mb_substr($query, -32, 32, 'UTF-8');
                                }
                                echo htmlspecialchars($queryExcerpt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                echo '</span>';

                            } else if ($var == "digest")
                            {
                                echo '<a href="'.LINK.'/Query/byDigest/'.$val.'/'.(int) $selectedMysqlServerId.'/'.$line['db'].'/">'.$val.'</a>';
                            }
                            else if ($var == "percent" || $var == "auto_increment_ratio") {
                                $percent = round($val * 100, 2);
                                $with = ceil($val * 100);

                                echo '<div class="progress" style="width:100px">';
                                echo '<div class="progress-bar progress-bar-striped progress-bar-warning" role="progressbar" style="width: ' . $with . '%; color:#000" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">' . $percent . '&nbsp;%</div>';
                                echo '</div>';
                            } else {
                                echo $val;
                            }



                            echo '</td>';
                        }

                        echo '</tr>';

                        //print_r($val);
                    }
                    echo "</table>";

                    echo '<a href="'.LINK.'mysqlsys/export/'.(int) $selectedMysqlServerId.'/'.$_GET['mysqlsys'].'" class="btn btn-primary active" role="button">Export Dokuwiki</a>';

                    
                } else {
                    echo "<b>No data</b>";
                }
                ?>

            </div>
        </div>
        <?php
    } elseif ($mysqlsysVersionUnsupported) {

        echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

        echo "This version of MySQL / MariaDB / Percona Server is not compatible with mysql-sys !<br />"
        . " mysql-sys require version of MySQL / MariaDB / Percona Server 5.6 (<b>" . $mysqlsysVersion . "</b>) at minimum.";

        echo '</div>';
    } elseif (empty($data['innodb'])) {
        echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

        echo __("InnoDB must be activated to install for use MySQL-sys<br />");
        echo '</div>';
    } else {

        echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Install:</b></p>';

        echo 'Your version of MySQL / MariaDB / Percona Server: <b>' . $mysqlsysVersion . "</b><br />";
        echo 'mysql-sys is not yet installed on this server, do you want to install it ? ';
        echo '<a href="' . LINK . 'mysqlsys/install/mysql_server:id:' . (int) $selectedMysqlServerId . '" role="button" class="btn btn-primary">Install MySQL-sys</a>';

        echo '</div>';
    }
} else {
    echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

    echo "Select the server";
    echo '</div>';
}
