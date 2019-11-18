<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Cleaner", "menu", array($data['id_cleaner']));
echo '</div>';

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

$converter = new AnsiToHtmlConverter();
$html = $converter->convert($data['log']);
?>

File log : <code><?=$data['log_file'] ?></code><br /><br />
<pre id="data_log" style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;"><?php echo $html ?></pre>
