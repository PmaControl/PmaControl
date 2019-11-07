<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Date\Date;


//echo "<h1>Work in progress ...</h1><br />";

echo '<div class="well" style="text-align: center;">';

$filename = TMP . 'gg.svg';
if (file_exists($filename)) {

    echo "Last refresh : " . date("F d Y H:i:s", filemtime($filename)) . " <b>UTC</b><br />";
}

if ((time() - filemtime($filename)) > 120) {
    echo '<span class="danger">Not refreshed since : <b>' . Date::secToTime(time() - filemtime($filename)) . '</b></span><br /><br />';
}

$svg = 'tmp/replication.svg';
//echo '<div style="background: url('.IMG.$svg.')"></div>';
//echo '<embed src="'.IMG.$svg.'" type="image/svg+xml" />';

echo '<div id="svg">';

$handle = fopen($filename, "r");

$remove = true;

if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {

        if ("<svg" != substr($buffer, 0, 4) && $remove) {
            $remove = false;
            continue;
        }

        echo $buffer;
    }
    if (!feof($handle)) {
        echo "Erreur: fgets() a échoué\n";
    }
    fclose($handle);
}

echo '</div>';
echo '</div>';

if (!empty($data['error'])) {
    echo '<div class="well" style="text-align: center;">';
    echo "<b>ERROR : </b>" . $data['error'];
    echo '</div>';
}





echo $data['cmd'];

echo '<pre style="background-color: black; color:#ddd; overflow: auto; padding: 10px 15px; font-family: monospace;">';
echo htmlentities($data['dot']);
echo '</pre>';



