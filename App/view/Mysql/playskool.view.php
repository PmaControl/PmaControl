<?php

//debug($_POST);

echo '<form action="" method="POST">';

echo 'Login : <input type="text" name="login" />';
echo ' - Password : <input type="password" name="password"/>';
echo '<br />';
echo '<br />';

$nb_elem_by_col = ceil(count($data['dbs'])/6);

$i =0;
foreach ($data['dbs'] as $db_name) {
    
    if ($i == 0)
    {
        echo '<ul class="col">';
    }
    elseif ($i % $nb_elem_by_col == 0)
    {
        echo '</ul><ul  class="col">';
    }
    
    $i++;
    
    
    
    
    echo '<li><input type="checkbox" name="db[' . str_replace('_','-',$db_name) . ']" /> ' . str_replace('_','-',$db_name) . "</li>";

    //debug($db);
}

echo '</ul>';
echo '<div style="clear:both"></div>';

echo '<br />';
echo 'SQL : <br/><textarea name="sql" style="width:100%; height:300px"></textarea>';


echo '<input type="submit" name="" class="button btBlueTest overlayW btMedium" value="Execute" />';

echo '</form>';
echo '<br />';echo '<br />';
if (!empty($data['ret'])) {
    echo "<b>Script to play : </b><br />";
    echo $data['ret'];
}