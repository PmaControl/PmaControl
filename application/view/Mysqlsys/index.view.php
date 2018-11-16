<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

echo '<div class="well">';


\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

function remove($array)
{
    $params = explode("/", trim($_GET['url'], "/"));

    //print_r($params);


    foreach ($params as $key => $param) {
        foreach ($array as $var) {
            if (strstr($param, $var.':')) {
                unset($params[$key]);
            }
        }
    }
    $ret = implode('/', $params);

    return $ret;
}
echo '<br />';
echo '<form action="" method="POST">';
echo __("Server")." : ";

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto")));
//echo Form::select("mysql_server", "id", $data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
echo ' ';


echo '<button type="submit" class="btn btn-primary">Filter</button>';

if (!empty($_GET['mysql_server']['id'])) {
    echo ' ';
    echo '<a href="'.LINK.'Mysqlsys/reset/'.$_GET['mysql_server']['id'].'" class="btn btn-warning" role="button" title="This option will truncate all table of PERFORMANCE_SCHEMA to reset statistiques in MySQL-sys" aria-pressed="true">Reset Statistics</a>';
    echo ' ';
    echo '<a href="'.LINK.'Mysqlsys/drop/'.$_GET['mysql_server']['id'].'" class="btn btn-danger" role="button" aria-pressed="true" title="This will DROP DATABASE `sys`; after this you can reinstall Mysql-sys for new version for example">Uninstall MySQL-sys</a>';
}


echo '</form>';
echo '</div>';

//debug($data['innodb']);

if (!empty($_GET['mysql_server']['id'])) {

    if (!empty($data['view_available']) && count($data['view_available']) > 0 && !empty($data['innodb'])) {
        ?>
        <div class="row">
            <div class="col-md-2">

                <?php
                echo '<table class="table table-condensed table-bordered table-striped">';

                echo '<tr>';
                echo '<th>'.__("Reporting").'</th>';
                echo '</tr>';


                echo '<tr>';
                echo '<td>';
                foreach ($data['view_available'] as $view) {
                    //$url = $_GET['url'];

                    $url = remove(array("mysqlsys"));

                    if (!empty($_GET['mysqlsys']) && $view == $_GET['mysqlsys']) {
                        echo '<a href="'.LINK.$url.'/mysqlsys:'.$view.'"><b>'.$view.'</b></a><br/>';
                    } else {
                        echo '<a href="'.LINK.$url.'/mysqlsys:'.$view.'">'.$view.'</a><br/>';
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

                            echo '<th>'.__("Top").'</th>';
                            foreach ($line as $var => $val) {
                                echo '<th>'.$var.'</th>';
                            }
                            echo '</tr>';
                        }

                        echo '<tr>';
                        echo '<td>'.$i.'</td>';
                        foreach ($line as $var => $val) {

                            echo '<td>';
                            if ($var == "query") {


                                echo '<button class="btn btn-default btn-xs" type="button" data-toggle="collapse" data-target="#collapseExample'.$i.'"><i class="fa fa-plus"></i></button>';
                                echo ' <span>';

                                if (strlen($val) > 64) {
                                    echo substr($val, 0, 32)."...".substr($val, -32);
                                } else {
                                    echo $val;
                                }
                                echo '</span>';
                            } else {
                                echo $val;
                            }


                            echo '<div class="collapse" id="collapseExample'.$i.'">
                            
                             '.SqlFormatter::format($val).'
                            
                          </div>';

                            echo '</td>';
                        }

                        echo '</tr>';

                        //print_r($val);
                    }
                    echo "</table>";
                } else {
                    echo "<b>No data</b>";
                }
                ?>

            </div>
        </div>
        <?php
    } elseif (version_compare($data['variables'], "5.6", "<=")) {

        echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

        echo "This version of MySQL / MariaDB / Percona Server is not compatible with mysql-sys !<br />"
        ." mysql-sys require version of MySQL / MariaDB / Percona Server 5.6 (<b>".$data['variables']."</b>) at minimum.";

        echo '</div>';
    } elseif (empty($data['innodb'])) {
        echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

        echo "InnoDB must be activated to install or use MySQL-sys<br />";
        echo '</div>';
    } else {

        echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Install:</b></p>';

        echo 'Your version of MySQL / MariaDB / Percona Server: <b>'.$data['variables']."</b><br />";
        echo 'mysql-sys is not yet installed on this server, do you want to install it ? ';
        echo '<a href="'.LINK.'mysqlsys/install/mysql_server:id:'.$_GET['mysql_server']['id'].'" role="button" class="btn btn-primary">Install MySQL-sys</a>';

        echo '</div>';
    }
} else {
    echo '<div class="well" style="border-left-color: #5cb85c;   border-left-width: 10px;">
            <p><b>Error :</b></p>';

    echo "Select the server";

    echo '</div>';
}