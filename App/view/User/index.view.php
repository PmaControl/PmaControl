<?php

use \Glial\Html\Form\Form;

//http://www.estrildidae.net/fr/user/confirmation/claude.festor@free.fr/498c2742abf4e0f396188cdfe779ca91b4531782

echo '<form action="'.LINK.'user/update_idgroup" method="post">';
echo "<table class=\"table table-bordered table-striped\" width=\"100%\">";
    echo "<tr>";
    echo "<th>".__("Top")."</th>";
    echo "<th>".__("Location")."</th>";
    echo "<th>".__("Name")."</th>";
    echo "<th>".__("Email")."</th>";
    echo "<th>".__("Rank")."</th>";

    //echo "<th>".__("Last online")."</th>";
    echo "<tr>";

$i=0;
foreach($data['user'] as $line)
{
	$i++;
	if ($i %2 ==0)
	{
		echo "<tr class=\"couleur2\">";
	}
	else
	{
		echo "<tr class=\"couleur1\">";
	}
	echo "<td>#$i</td>";
	echo "<td><img class=\"country\" src=\"".IMG."country/type1/".strtolower($line['id_country']).".gif\" width=\"18\" height=\"12\" /> ".$line['libelle']."</td>";
	echo "<td>".$line['firstname']." ".$line['name']."</td>";
	echo "<td>".$line['email']."</td>";
	echo "<td>";
	echo Form::select("user_main", "id_group", $data['group'], $line['id_group'], array("class" => "form-control"),$line['id'] );
	echo "</td>";
	//echo "<td>".$line['date_last_connected']."</td>";
	echo "<tr>";
}

echo "</table>";
echo '<button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-refresh" style="font-size:12px"></span> '. __("Update").'</button>';
echo '</form>';
