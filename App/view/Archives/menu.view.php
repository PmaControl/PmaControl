<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo ' <div class="btn-group" role="group" aria-label="Default button group">';

foreach ($data['menu'] as $key => $elem) {
    if ($_GET['path'] == $key) {
        $color = "btn-primary";
    } else {
        $color = "btn-default";
    }

    echo '<a href="'.$elem['path'].'" type="button" class="btn '.$color.'" style="font-size:12px">'
    .' '.$elem['icone'].' '.$elem['name'].'</a>';
}
echo '</div>';
