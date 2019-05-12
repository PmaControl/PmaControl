<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div>';


echo 'path of script to execute : '.$data['file_name'];
echo '<br />';


if (!empty($_GET['error_msg'])) {


    echo '<div class="well" style="border-left-color: #'.'b85c5c'.';   border-left-width: 10px;"><p><b>'.'Error'.'</b></p>';
    echo base64_decode($_GET['error_msg']);
    echo '</div>';
}


if (!empty($data['file'])) {
    echo '<div style="background:#ccc; height:600px; overflow:scroll; border:#000 1px solid" ><pre>'.$data['file'].'</pre></textarea>';
    echo '</div>';

    echo '<br />';
}

echo '<form action="" method="post">';
echo '<input type="hidden" name="install" value="1" />';
echo '<button type="submit" class="btn btn-primary">'.__("Install it !").'</button>';
echo '</form>';
