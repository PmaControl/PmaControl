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
<pre id="data_log" style="background-color: black; overflow: auto; display: flex; color:#cccccc; height:100%; width:100%; padding: 10px 15px; font-family: monospace; margin-bottom:0px">
<?php 
echo "ls -lh ".TMP."tmp_file";
echo "\n";
echo $data['ls'] ?>
</pre>


<?php
if (empty($_GET['ajax'])) {
    echo '</div>';
    echo '</div>';
}