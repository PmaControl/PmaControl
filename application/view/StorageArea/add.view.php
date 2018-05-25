<?php
use Glial\Html\Form\Form;



\Glial\Synapse\FactoryController::addNode("StorageArea", "menu");


echo "<form action=\"\" method=\"post\" class=\"form-horizontal\" width=\"100%\">";
echo "<table style=\"margin-bottom: 0px;\" class=\"table\" width=\"100%\">";

echo "<tr>";
echo "<td colspan=\"2\"><h3 class=\"item\">".__("Main")." :</h3></td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("Name")." :</td>";
echo "<td>".Form::input("backup_storage_area","libelle", array("class" => "form-control"))."</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("IP")." :</td>";
echo "<td>".Form::input("backup_storage_area","ip", array("class" => "form-control"))."</td>";
echo "</tr>";



echo "<tr>";
echo "<td class=\"first\">".__("Port")." :</td>";
echo "<td>".Form::input("backup_storage_area","port", array("class" => "form-control"))."</td>";
echo "</tr>";



echo "<tr>";
echo "<td class=\"first\">".__("Path")." :</td>";
echo "<td>".Form::input("backup_storage_area","path", array("class" => "form-control"))."</td>";
echo "</tr>";


echo "<tr>";
echo "<td colspan=\"2\"><h3 class=\"item\">".__("Location")." :</h3></td>";
echo "</tr>";


echo "<tr>";
echo "<td class=\"first\">".__("Country")." :</td>";
echo "<td>".Form::select("backup_storage_area","id_geolocalisation_country",$data['geolocalisation_country'],"", array("class" => "form-control ac_input"))."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"first\">".__("City")." :</td>";
//todo remove basic.php and add autocomplete to Form
echo "<td>".autocomplete("backup_storage_area","id_geolocalisation_city", "form-control")."</td>";
echo "</tr>";


echo "<tr>";
echo "<td colspan=\"2\"><h3 class=\"item\">".__("Account SSH")." :</h3></td>";
echo "</tr>";

/*
echo "<tr>";
echo "<td>Identifiant </td>";
echo "<td>: <input class=\"text\" type=\"text\" name=\"identifiant\" value=\"".$_GET['identifiant']."\" /></td>";
echo "</tr>";
*/

echo "<tr>";
echo "<td class=\"first\">".__("Login")." :</td>";
echo "<td>".Form::input("backup_storage_area","ssh_login", array("class" => "form-control","autocomplete"=>"false", "autocomplete"=>"off", "autocomplete"=>"new-password"))."</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("Password")." :</td>";
echo "<td>".Form::input("backup_storage_area","ssh_password", array("class" => "form-control", "type" => "password","autocomplete"=>"false", "autocomplete"=>"off", "autocomplete"=>"new-password"))."</td>";
echo "</tr>";


echo "<tr>";
echo "<td class=\"first\">".__("Key ssh")." :</td>";
echo '<td><textarea name="backup_storage_area[ssh_key]" rows="10" class="form-control"></textarea></td>';
echo "</tr>";


echo "</table>";

echo "<div class=\"form-actions\" style=\"margin:0\"><input class=\"btn btn-primary\" type=\"submit\" value=\"".__("Validate")."\" /> "
        . "<input class=\"btn\" type=\"reset\" value=\"".__("Delete")."\" /></div>";
echo "</div>";

echo "</form>";
