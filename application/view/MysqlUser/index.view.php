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


echo '<th colspan="'.$colspan.'">'.__("Database").'</th>';


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

foreach ($data['user'] as $key => $server) {
    echo '<th>'.$key.'</th>';
}

echo '</tr>';


$i = 1;




foreach ($data['all_user'] as $user_name => $user) {

    foreach ($user as $host => $res) {

        //debug($res);

        $nb_lines = count(end($res)['grant']);


        echo '<tr>';
        echo '<td rowspan="'.$nb_lines.'">'.$i.'</td>';
        echo '<td rowspan="'.$nb_lines.'">'.$user_name.'</td>';
        echo '<td rowspan="'.$nb_lines.'">'.$host.'</td>';


        foreach ($data['user'] as $key => $server) {
            echo '<td rowspan="'.$nb_lines.'">';

            if (! empty($res[$key]['password']))
            {

                echo '<span data-clipboard-text="'.$res[$key]['password'].'" onclick="return false;" class="copy-button clipboard badge badge-info" style="font-variant: small-caps; font-size: 14px; vertical-align: middle; background-color: #4384c7; cursor:pointer;"><i class="fa fa-files-o" aria-hidden="true"></i></span>';

            }


            echo '</td>';
        }
        /*         * ***** */

        for ($k = 0; $k < $nb_lines; $k++) {



            if ($k !== 0) {
                echo '<tr>';
            }





            $elems = array("database", "grant","create");

            foreach ($elems as $elem) {



                foreach ($data['user'] as $key => $server) {
                    echo '<td>';

                    if (!empty($res[$key][$elem])) {

                        if (is_array($res[$key][$elem][$k])) {

                            echo '<ul>';
                            foreach ($res[$key][$elem][$k] as $right) {
                                echo '<li>'.$right.'</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo $res[$key][$elem][$k];
                        }
                    }

                    echo '</td>';
                }
            }

            echo '</tr>';

            /*             * ******* */
        }

        $i++;
    }
}


echo '</table>';
//debug($data['all_user']);


//debug($data['user']);