<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




echo '<form method="post" action="">';
echo '<div class="well">';
echo '<div class="row">';
echo '<div class="col-md-10">';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("multiple" => "multiple", "data-width" => "100%")));


echo '</div>';
echo '<div class="col-md-2">';
echo '<input class="btn btn-primary" type="submit" value="'.__("Submit").'">';
echo '</div>';

echo '</div>';
echo '</div>';
echo '</form>';


//debug($data['user']);


echo '<table class="table table-condensed table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th rowspan="2">'.__("Top").'</th>';
echo '<th rowspan="2">'.__("User").'</th>';
echo '<th rowspan="2">'.__("Host").'</th>';


$colspan = count($data['user']);

echo '<th colspan="'.$colspan.'">'.__("Password").'</th>';


echo '<th colspan="'.$colspan.'">'.__("Grant").'</th>';



echo '<th colspan="'.$colspan.'">'.__("Create").'</th>';
echo '</tr>';

echo '<tr>';
foreach ($data['user'] as $key => $server) {
    echo '<th>'.$key.'</th>';
}

foreach ($data['user'] as $key => $server) {
    echo '<th>'.$key.'</th>';
}

foreach ($data['user'] as $key => $server) {
    echo '<th>'.$key.'</th>';
}

echo '</tr>';


$i = 1;

foreach ($data['all_user'] as $user) {
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$user['user'].'</td>';
    echo '<td>'.$user['host'].'</td>';
    echo '<td>'.'</td>';
    echo '<td>';

    echo '<ul>';

    //debug($user['grant']);

    sort($user['grant'][0]);

    foreach ($user['grant'][0] as $grant) {
        echo '<li>'.$grant.'</li>';
    }
    echo '</ul>';


    echo '</td>';

    foreach ($data['user'] as $server) {

        if (!empty($server[$user['user']][$user['host']])) {
            echo '<td>'.'XFDFGD'.'</td>';
        } else {
            echo '<td></td>';
        }
    }


    echo '</tr>';

    $i++;
}


echo '</table>';
