<?php


if (empty($_GET['ajax'])) {

    echo '
    <div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">'.__('List of files waiting to be processed').'</h3>
    </div>';

    echo '<div id="tmp_file">';
}




?>
<pre id="data_log" style="background-color: black; overflow: auto; height:300px; max-height:300px; display:block; color:#cccccc; width:100%; margin:0; padding: 10px 15px; font-family: monospace; margin-bottom:0px; box-sizing:border-box;">
<?php 
echo "ls -lh ".TMP."tmp_file [ Number of Files : ".$data['nb_files']." ]";
echo "\n";
echo $data['ls'] ?>
</pre>


<?php
if (empty($_GET['ajax'])) {
    echo '</div>';
    echo '</div>';
}