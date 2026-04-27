<?php

//debug($_POST);

$data = $data ?? [];
$form = $data['form'] ?? [];
$mysqlPlayskoolCsrfField = htmlspecialchars((string)($data['mysql_playskool_csrf_field'] ?? '_csrf_token'), ENT_QUOTES, 'UTF-8');
$mysqlPlayskoolCsrfToken = htmlspecialchars((string)($data['mysql_playskool_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$login = htmlspecialchars((string)($form['login'] ?? ''), ENT_QUOTES, 'UTF-8');
$sql = htmlspecialchars((string)($form['sql'] ?? ''), ENT_QUOTES, 'UTF-8');
$selectedDbs = array_fill_keys($form['dbs'] ?? [], true);

echo '<form action="" method="post">';
echo '<input type="hidden" name="'.$mysqlPlayskoolCsrfField.'" value="'.$mysqlPlayskoolCsrfToken.'" />';

echo 'Login : <input type="text" name="login" value="'.$login.'" />';
echo ' - Password : <input type="password" name="password"/>';
echo '<br />';
echo '<br />';

$nb_elem_by_col = max(1, (int)ceil(count($data['dbs'])/6));

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
    
    
    
    
    $dbFormName = \App\Controller\Mysql::formatPlayskoolDbName((string)$db_name);
    $dbFormNameEscaped = htmlspecialchars($dbFormName, ENT_QUOTES, 'UTF-8');
    $checked = isset($selectedDbs[$dbFormName]) ? ' checked' : '';

    echo '<li><input type="checkbox" name="db[' . $dbFormNameEscaped . ']"'.$checked.' /> ' . $dbFormNameEscaped . "</li>";

    //debug($db);
}

echo '</ul>';
echo '<div style="clear:both"></div>';

echo '<br />';
echo 'SQL : <br/><textarea name="sql" style="width:100%; height:300px">'.$sql.'</textarea>';


echo '<input type="submit" name="" class="button btBlueTest overlayW btMedium" value="Execute" />';

echo '</form>';
echo '<br />';echo '<br />';
if (!empty($data['commands'])) {
    echo "<b>Script to play : </b><br />";
    foreach ($data['commands'] as $command) {
        echo htmlspecialchars((string)$command, ENT_QUOTES, 'UTF-8') . '<br />';
    }
}
