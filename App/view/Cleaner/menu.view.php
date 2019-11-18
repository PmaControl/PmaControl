<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo ' <div class="btn-group" role="group" aria-label="Default button group">';



foreach ($data['menu'] as $elem) {
    $color = "btn-default";

    if (!strpos(strtolower($elem['url']), strtolower($_GET['glial_path'])) === false) {
        $color = "btn-primary";
    }

    echo '<a href="'.$elem['url'].'" type="button" class="btn '.$color.'" style="font-size:12px">'.' '.$elem['title'].'</a>';
}
echo '</div>';

