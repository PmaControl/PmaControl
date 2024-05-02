<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

//to move
function setColor($type)
{
    $hex = substr(sha1($type), 0, 2).substr(sha1($type), 20, 2).substr(md5($type), -2, 2);
    return hexToRgb($hex);
}

function hexToRgb($colorName)
{
    list($r, $g, $b) = array_map(
        function($c) {
        return hexdec(str_pad($c, 2, $c));
    }, str_split(ltrim($colorName, '#'), strlen($colorName) > 4 ? 2 : 1)
    );

    return array($r, $g, $b);
}

function getrgba($label, $alpha)
{
    list($r, $g, $b) = setColor($label);
    return "rgba(".$r.", ".$g.", ".$b.", ".$alpha.")";
}

function format($bytes, $decimals = 2)
{

    if (!is_numeric($bytes)) {
        return $bytes;
    }

    if (empty($bytes)) {
        return "";
    }
    $sz = ' KMGTPEZY';

    $factor = (int) floor(log($bytes) / log(1024));

    if ($factor === 0) {
        $unit = '';
    } else {
        $unit = " ".$sz[$factor].'o';
    }

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).$unit;
}

function onOff($string)
{

    if (strtolower($string) == 'on') {
        return '<span class="label label-success">ON</span>';
    } else
    if (strtolower($string) == 'off') {
        return '<span class="label label-warning">OFF</span>';
    } else {
        return $string;
    }


    //return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}
echo '<div class="well">';


echo '<div class="row">';
echo '<form method="POST" action="">';
echo '<div class="col-md-10">';
echo Form::select("mysql_cluster", "id", $data['grappe'], "", array("data-live-search" => "true", "class" => "selectpicker form-control"));
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" class="btn btn-primary">Check Result on this cluster</button>';
echo '</div>';
echo '</form>';
echo '</div>';


echo '<div class="row">';
echo '<br />';
echo '</div>';

echo '<div class="row">';
echo '<form method="POST" action="">';
echo '<div class="col-md-10">';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("multiple" => "multiple", "data-width" => "100%", "all_server"=>"true")));
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" class="btn btn-primary">Check Result on these servers</button>';
echo '</div>';

echo '</form>';
echo '</div>';

echo '</div>';

function isFloat($value)
{
    // test before => must be numeric first
    if (strstr($value, ".")) {
        return true;
    }
    return ((int) $value != $value); // problem avec PHP_INT_MAX
}

if (!empty($data['index'])) {

    echo '<p><a class="btn btn-info showdiff" role="button">Show only differences</a></p>';
    echo '<table class="table table-condensed table-bordered table-striped">';
    echo '<tr>';
    echo '<th>'.__('Top').'</th>';
    echo '<th>'.__('Diff').'</th>';
    if (!empty($data['show']) && $data['show']) {
        echo '<th>'.__("Variable_name").'</th>';
    }


    foreach ($data['mysql_server'] as $id_mysql_server => $title) {
        echo '<th>';

        echo $title;

        echo '</th>';
    }
    echo '</tr>';

    $length = 23 * 9;
//definit la taille max de chaque colone en fonction du nombre de serveurs
    if (!empty($data['mysql_server'])) {
        $length = 23 * 9 / count($data['mysql_server']);
    }

    $i = 0;
    $j = 0;
// cas SHOW
    foreach ($data['index'] as $index) {

        $i++;
// test egalité sur toutes les colones
        $test = array();
        foreach ($data['mysql_server'] as $id_mysql_server => $title) {

            if (isset($data['resultat'][$id_mysql_server][$index])) {
                $test[] = $data['resultat'][$id_mysql_server][$index];
            } else {
                $test[] = "N/A";
            }
        }

        $style = false;

        $hide = ' class="to_hide"';

        if (count(array_unique($test)) !== 1) {
            $style = true;
            $hide  = '';
            $j++;
        }

        if (true) {
            echo '<tr'.$hide.'>';
            echo '<td>'.$i.'</td>';
            echo '<td>'.$j.'</td>';

            //<span data-toggle="tooltip" data-placement="right" title="Le nombre naire maiset ont utilisées un  de la transaction.">

            echo '<td>';
            if ($style) {
                echo '<b>'.$index.'</b>';
            } else {
                echo ''.$index.'';
            }
            echo '</td>';

            foreach ($data['mysql_server'] as $id_mysql_server => $title) {

                if (isset($data['resultat'][$id_mysql_server][$index])) {
                    $var_long = $data['resultat'][$id_mysql_server][$index];
                    $var = $var_long;
              
                } else {
                    $var      = "<b>N/A</b>";
                    $var_long = "N/A";
                }

                $pos = strpos($index, "performance_schema");

                if ((substr($index, -5) === "_size" && $pos === false) || $index === "join_buffer_space_limit") {
                    $var = format($var, 0);
                }

                $color = "";

                if ($style) {
                    $color = "background: ".getrgba($var, 0.5);
                    $var   = "<b>".onOff($var)."</b>";
                }

                echo '<td style="'.$color.'" title="'.$var_long.'">';
                echo onOff($var);
                echo '</td>';
            }

            echo '</tr>';
        }
    }
    echo '</table>';

    echo '<p><a class="btn btn-info showdiff" role="button">Show only differences</a></p>';
}
//debug($data['resultat']);