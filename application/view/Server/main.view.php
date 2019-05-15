<?php

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Synapse\FactoryController;
use \Glial\Security\Crypt\Crypt;
use Glial\Html\Form\Form;

function formatVersion($version)
{
    if (strpos($version, "-")) {
        $number = explode("-", $version)[0];
        $fork   = explode("-", $version)[1];
    } else {
        $number = $version;
    }

    switch (strtolower($fork)) {
        case 'mariadb':
            $name = '<span class="geek">&#xF130;</span> MariaDB';
            break;

        case 'percona':
            $name = 'percona';
            break;

        default:
            $name = '<span class="geek">&#xF137;</span> MySQL';
    }

    return $name." ".$number;
}

function format_ping($microtime, $precision = 2)
{
    $units = array('ms', 's');

    $microtime = $microtime * 1000;

    if ($microtime > 1000) {
        $microtime = $microtime / 1000;
        $pow       = 1;
    } else {
        $pow = 0;
    }


    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));


    return round($microtime, $precision).' '.$units[$pow];
}
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
        if (empty($server['is_available']) && $server['is_monitored'] === "1") {
            $style = 'background-color:#F2DEDE; color:#000';
        }

        // cas des warning
        if ($server['is_available'] == -1 && $server['is_monitored'] === "1") {
            $style = 'background-color:#FCF8E3; color:#000000';
        }

        // acknoledge
        if ($server['is_acknowledged'] !== "0") {
            $style = 'background-color:#DFF0D8; color:#999999';
        }

        // serveur non monitoré
        if (empty($server['is_monitored'])) {
            $style = 'background-color:#D9EDF7; color:#999';
        }

        if (!empty($style)) {
            $style .= "; border-bottom:#fff 1px solid; border-top:#fff 1px solid;";
        }

        echo '<tr>';
        echo '<td style="'.$style.'">'.$i.'</td>';
        echo '<td style="'.$style.'">'.$server['id'].'</td>';
        echo '<td style="'.$style.'">';
        echo '<span class="glyphicon '.(empty($server['is_monitored']) ? "glyphicon-question-sign" : ($server['is_available'] == 1 ? "glyphicon-ok-sign" : "glyphicon-remove-sign")).'" aria-hidden="true"></span>';
        echo '</td>';

        /*
          echo '<td style="'.$style.'">';
          ?>
          <div class="form-group">
          <div class="checkbox checbox-switch switch-success">
          <label>
          <?php
          if ($server['is_monitored'] === "1") {
          $computed = array("class" => "form-control", "type" => "checkbox", "checked" => "checked");
          } else {
          $computed = array("class" => "form-control", "type" => "checkbox");
          }

          echo Form::input("monitored", $server['id'], $computed);
          ?>
          <span></span>

          </label>
          </div>
          </div>

          <?php
          //.'<input type="checkbox" name="monitored['.$server['id'].']" '.($server['is_monitored'] == 1 ? 'checked="checked"' : '').'" />'.
          echo '</td>';
          /* */

        echo '<td style="'.$style.'">'.$server['client'].'</td>';
        echo '<td style="'.$style.'">';
        echo '<big><span class="label label-'.$server['class'].'">'.$server['environment'].'</span></big>';
        echo '</td>';
        echo '<td style="'.$style.'"><a href="'.LINK.'server/listing/id/mysql_server:id:'.$server['id'].'/ts_variable:name:com_select/ts_variable:date:1 hour/ts_variable:derivate:1">';

        echo $server['display_name'];
        //echo $data['extra'][$server['id']]['']['hostname'];

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
        //echo '<td style="'.$style.'">'.$server['hostname'].'</td>';
        echo '<td style="'.$style.'">';

        if (!empty($data['extra'][$server['id']]['']['version'])) {



            echo formatVersion($data['extra'][$server['id']]['']['version']);
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
            echo format_ping($data['extra'][$server['id']]['']['ping']);
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

        $date1   = strtotime($data['last_date'][$server['id']]['date']);
        $date2   = time();
        $subTime = $date2 - $date1;

        $d = ($subTime / (60 * 60 * 24));
        $h = ($subTime / (60 * 60)) % 24;
        $m = ($subTime / 60) % 60;

        if ($d >= 1) {
            echo ' <span class="label label-danger" title="'.$data['last_date'][$server['id']]['date'].'">'.round($d, 0).' '.__("Days").'</span>';
        } else if ($subTime < 60) {
            //echo ' <span class="label label-success" title="'.$data['last_date'][$server['id']]['date'].'">'.__("OK").'</span>';
        } else if ($subTime >= 60 && $subTime < 3600) {
            echo ' <span class="label label-warning" title="'.$data['last_date'][$server['id']]['date'].'"><i class="glyphicon glyphicon-warning-sign"></i> '.$m.' '.__("Minutes").'</span>';
        } else {
            echo ' <span class="label label-warning" title="'.$data['last_date'][$server['id']]['date'].'">'.$h.' '.__("hours").'</span>';
        }



        echo '</td>';
        echo '<td style="'.$style.'">';

        if (empty($server['is_available']) && $server['is_monitored'] === "1" && $server['is_acknowledged'] === "0") {
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
