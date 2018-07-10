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

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("User").'</th>';
echo '<th>'.__("Host").'</th>';
echo '<th>'.__("Password").'</th>';
echo '<th>'.__("Grant").'</th>';

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
    echo '<td>'.'</td>';

    foreach ($data['user'] as $server) {

        if (!empty($server[$user['user']][$user['host']])) {
            echo '<td>'.implode("<br />", $server[$user['user']][$user['host']]).'</td>';
        } else {
            echo '<td></td>';
        }
    }


    echo '</tr>';

    $i++;
}


echo '</table>';
