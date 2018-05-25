<?php
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Synapse\FactoryController;

use \Glial\Security\Crypt\Crypt;

$converter = new AnsiToHtmlConverter();



echo '<table class="table table-condensed table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("IP").'</th>';
echo '<th>'.__("Port").'</th>';
echo '<th>'.__("Base n°").'</th>';
echo '<th>'.__("Database").'</th>';
echo '<th>'.__("Tables").'</th>';
echo '<th>'.__("Rows").'</th>';
echo '<th>'.__("Data").'</th>';
echo '<th>'.__("Index").'</th>';
echo '<th>'.__("Free").'</th>';
echo '<th>'.__("Collation").'</th>';
echo '<th>'.__("Charset").'</th>';
echo '</tr>';


function _split($elems)
{
	$tmp = explode(',', $elems);
	return $tmp;

}
function byte($bytes) {

    if (empty($bytes))
	{
		return "0";
	}
    $symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $exp = floor(log($bytes)/log(1024));

    return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

$i = 0;
$j = 0;


$space = 0;
$lignes = 0;

foreach ($data['servers'] as $server) {
    
	$databases = _split($server['dbs']);   
	$tables = _split($server['tables']);   
	$rows = _split($server['rows']);   
	$id = _split($server['id_db']);   
	$data = _split($server['data_length']);   
	$free = _split($server['data_free']);   
	$index = _split($server['index_length']);   
	$collation = _split($server['collation_name']);   
	$charset = _split($server['character_set_name']);   
	$do_db = _split($server['binlog_do_db']);   


    $i++;


	$nb_line = count($databases);

    		$style="";
    echo '<tr>';
    echo '<td rowspan="'.$nb_line.'" style="'.$style.'">'.$i.'</td>';
    echo '<td rowspan="'.$nb_line.'" style="'.$style.'">'.$server['id'].'</td>';
    echo '<td rowspan="'.$nb_line.'" style="'.$style.'">'.str_replace('_', '-', $server['name']).'</td>';
    echo '<td rowspan="'.$nb_line.'" style="'.$style.'">'.$server['ip'].'</td>';
    echo '<td rowspan="'.$nb_line.'" style="'.$style.'">'.$server['port'].'</td>';
    

        
    
	for($k = 0; $k < $nb_line; $k++)
	{
    		$style="";

		$styleFree = "";
		if($free[$k] > 1*1024*1024*1024)
		{
			$styleFree = 'background-color:#d9534f; color:#FFFFFF';
		}


		$styleData = "";
		if($data[$k] > 1*1024*1024*1024)
		{
			$styleData = 'background-color:#5cb85c;';
		}



		$styleIndex = "";
		if($index[$k] > 1*1024*1024*1024)
		{
			$styleIndex = 'background-color:#5cb85c;';
		}

		if (! empty($k))
		{
			echo '<tr>';
		}
		$j++;
		echo '<td style="'.$style.'">'.$j.'</td>';
		echo '<td style="'.$style.'">';

		if (empty($server['error']))
		{
			echo '<a href="'.LINK.'Mysql/mpd/'.str_replace('_', '-', $server['name']).'/'.$databases[$k].'/"><img src="'.IMG.'main/dot.gif" title="Designer" alt="Designer" class="icon ic_b_relations"></a> ';
		}
		echo '<a href="'.LINK.'">'.$databases[$k].'</a></td>';
		echo '<td style="'.$style.'">'.$tables[$k].'</td>';
		echo '<td style="text-align: right; '.$style.'">'.number_format ( $rows[$k], 0 , "." , " " ).'</td>';
		echo '<td style="'.$styleData.'">'.byte($data[$k]).'</td>';
		echo '<td style="'.$styleIndex.'">'.byte($index[$k]).'</td>';
		echo '<td style="'.$styleFree.'">'.byte($free[$k]).'</td>';
		echo '<td style="'.$style.'">'.$collation[$k].'</td>';
		echo '<td style="'.$style.'">'.$charset[$k].'</td>';
//		echo '<td style="'.$style.'">'.$collation[$k].'</td>';
		echo '</tr>';

                $space += $data[$k];
                $space += $index[$k];
                $space += $free[$k];
                
                $lignes += $rows[$k];
	}
        
        
        
}

echo '</table>';

echo byte($space);
echo " lignes : ".number_format ( $lignes, 0 , "." , " " );