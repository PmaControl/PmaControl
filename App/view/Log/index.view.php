<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Html\Form\Form;

echo '<div class="well">';

echo '<form action="" method="get">';

echo '<div class="row">';
echo '<div class="col-md-2">';
echo __('Select servers:');
echo '</div>';

echo '<div class="col-md-10">';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%", "multiple" => "multiple")));
echo '</div>';
echo '</div>';


echo '<div class="row">';
echo '&nbsp;';
echo '</div>';



echo '<div class="row">';
echo '<div class="col-md-2">';
echo __('Select variables:');
echo '</div>';

echo '<div class="col-md-10">';
\Glial\Synapse\FactoryController::addNode("Common", "getTsVariables", array("ts_variable", "id", array("data-width" => "100%", "multiple" => "multiple")));
echo '</div>';
echo '</div>';


echo '<div class="row">';
echo '&nbsp;';
echo '</div>';


echo '<div class="row">';


echo '<div class="col-md-2">';
echo __('Between:');
echo '</div>';


echo '<div class="col-md-4">';

echo '<div class="form-group">';
echo '<div class="input-group date" id="datetimepicker1">';

echo Form::input("ts", "date_start", array("class" => "form-control"));

echo '<span class="input-group-addon">';
echo '<span class="glyphicon glyphicon-calendar"></span>';
echo '</span>';
echo '</div>';
echo '</div>';

echo '</div>';

echo '<div class="col-md-2">';
echo __('And');
echo '</div>';

echo '<div class="col-md-4">';

echo '<div class="form-group">';
echo '<div class="input-group date" id="datetimepicker2">';
echo Form::input("ts", "date_end", array("class" => "form-control"));

echo '<span class="input-group-addon">';
echo '<span class="glyphicon glyphicon-calendar"></span>';
echo '</span>';
echo '</div>';
echo '</div>';

echo '</div>';

echo '</div>';


echo '<button role="submit" class="btn btn-primary">Search</button>';


echo '</form>';


//ipunt-group date

echo '</div>';








if (!empty($data['log'])) {

    $variables = $data['filters']['ts_variables'] ?? array();

    $titles = array();
    foreach ($variables as $variable) {
        $elem     = explode('::', $variable);
        $titles[] = $elem[1] ?? $elem[0];
    }

    if (count($data['log']) == 1) {

        echo '<table class="table table-condensed table-bordered table-striped" id="table">';

        echo '<tr>';

        echo '<th>'.__("Date").'</th>';



        foreach ($titles as $title) {
            echo '<th>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</th>';
        }

        echo '</tr>';

        foreach ($data['log'] as $servers) {
            foreach ($servers as $connection) {


                foreach ($connection as $date => $values) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars((string) $date, ENT_QUOTES, 'UTF-8').'</td>';


                    foreach ($titles as $title) {
                        echo '<td>';

                        if (isset($values[$title])) {
                            echo htmlspecialchars((string) $values[$title], ENT_QUOTES, 'UTF-8');
                        } else {
                            echo 'N/A';
                        }

                        echo '</td>';
                    }
                    echo '</tr>';
                }
            }
        }




        echo '</table>';
    }

    //debug($data['log']);
}


//Debug::$debug = true;
//Debug::debugShowQueries($data['db']);


//2018-07-26 03:59:49
//2018-07-26 03:59:49
