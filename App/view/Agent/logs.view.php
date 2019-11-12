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
<pre id="data_log" style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;"><?php echo $html ?></pre>

<br />
<form action="" method="post">

    <?php
    
    echo '<div class="form-group">';
    echo __("Refresh time (in seconds) : "). Form::select("daemon_main", "refresh_time", $data['refresh_time'], "", array());
    echo ' - ';
    echo __("Thread concurency : ").Form::select("daemon_main", "thread_concurency", $data['thread_concurency'], "", array());
    echo ' - ';
    echo __("Delay before to consider MySQL server as down (in seconds) : ").Form::select("daemon_main", "max_delay", $data['max_delay'], "", array());

    echo ' <button type="submit" class="btn btn-primary">Submit</button>';

    echo '</div>';
    ?>
</form>