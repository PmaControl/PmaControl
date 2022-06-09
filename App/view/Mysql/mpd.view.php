<?php


if (!empty($data['NO_FK']))
{
	?>
	<div class="well" style="border-left-color: #d9534f;   border-left-width: 5px;">
	<p><b>IMPORTANT !!!</b></p>
	Your server doesn't support the foreign keys, you need to upgrade to MySQL 5.1 to get it.
	</div>	
	<?php
}



$filename = $data['file'];

$path_parts = pathinfo($filename)['basename'];




//$file = LINK."mysql/png/".$data['id_mysql_server']."/".$data['database']."/";


//echo '<img src="'.$file.'" />';

echo '<div id="svg">';

$filename = str_replace("png","svg", $filename);

$handle = fopen($filename, "r");
$remove = true;

if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        
        if ("<svg" != substr($buffer, 0,4) && $remove)
        {
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
