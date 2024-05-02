<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Glial\Html\Form\Form;

$converter = new AnsiToHtmlConverter();
$html = $converter->convert($data['log']);
?>

File log : <code><?= $data['log_file'] ?></code><br /><br />
<pre id="data_log" style="background-color: black; overflow: auto; padding: 10px 15px; font-family: monospace;"><?php echo $html ?></pre>