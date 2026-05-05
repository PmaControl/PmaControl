<?php

use App\Library\Security\CsrfRender;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div>';


$mysqlsysInstallCsrfField = CsrfRender::field($data, 'mysqlsys_install');
$mysqlsysInstallCsrfToken = CsrfRender::token($data, 'mysqlsys_install');
$fileName = htmlspecialchars((string)($data['file_name'] ?? ''), ENT_QUOTES, 'UTF-8');

echo 'path of script to execute : '.$fileName;
echo '<br />';


if (!empty($_GET['error_msg'])) {


    echo '<div class="well" style="border-left-color: #'.'b85c5c'.';   border-left-width: 10px;"><p><b>'.'Error'.'</b></p>';
    echo nl2br(htmlspecialchars((string)base64_decode($_GET['error_msg']), ENT_QUOTES, 'UTF-8'));
    echo '</div>';
}


if (!empty($data['file'])) {
    echo '<div style="background:#ccc; height:600px; overflow:scroll; border:#000 1px solid" ><pre>'.htmlspecialchars((string)$data['file'], ENT_QUOTES, 'UTF-8').'</pre></textarea>';
    echo '</div>';

    echo '<br />';
}

echo '<form action="" method="post">';
echo '<input type="hidden" name="'.$mysqlsysInstallCsrfField.'" value="'.$mysqlsysInstallCsrfToken.'" />';
echo '<input type="hidden" name="install" value="1" />';
echo '<button type="submit" class="btn btn-primary">'.__("Install it !").'</button>';
echo '</form>';
