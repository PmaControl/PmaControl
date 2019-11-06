<?php

use Glial\Html\Form\Form;

//debug($_SESSION);


echo '<div style="width:1000px; margin-left:auto; margin-right:auto; padding:0" class="well">';




echo "<form action=\"\" method=\"post\" class=\"form-horizontal\" width=\"100%\">";


echo "<table style=\"margin-bottom: 0px;\" class=\"form table\" width=\"100%\">";


echo "<tr>";
echo "<td colspan=\"2\"><h3 class=\"item\">".__("Identity","fr")." :</h3></td>";
echo "</tr>";



echo "<tr>";
echo "<td class=\"first\">".__("Email")." (".__("Username").") :</td>";
echo "<td>".Form::input("user_main","email",array("class" => "form-control"))."</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("Firstname")." :</td>";
echo "<td>".Form::input("user_main","firstname",array("class" => "form-control"))."</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("Lastname")." :</td>";
echo "<td>".Form::input("user_main","name",array("class" => "form-control"))."</td>";
echo "</tr>";



echo "<tr>";
echo "<td colspan=\"2\"><h3 class=\"item\">".__("Location")." :</h3></td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("Country")." :</td>";
echo "<td>".Form::select("user_main","id_geolocalisation_country",$data['geolocalisation_country'],"",array("class" => "form-control ac_input"))."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"first\">".__("City")." :</td>";

//TODO replace with one from Form
echo "<td>".autocomplete("user_main","id_geolocalisation_city","form-control")."</td>";
echo "</tr>";



echo "<tr>";
echo "<td colspan=\"2\"><h3 class=\"item\">".__("Password")." :</h3></td>";
echo "</tr>";

/*
echo "<tr>";
echo "<td>Identifiant </td>";
echo "<td>: <input class=\"text\" type=\"text\" name=\"identifiant\" value=\"".$_GET['identifiant']."\" /></td>";
echo "</tr>";
*/

echo "<tr>";
echo "<td class=\"first\">".__("Password")." :</td>";
echo "<td>".password("user_main","password","form-control")."</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"first\">".__("Password")." (".__("repeat").") :</td>";
echo "<td>".password("user_main","password2","form-control")."</td>";
echo "</tr>";

echo "</table>";

echo "<div class=\"form-actions\" style=\"margin:0\"><input class=\"btn btn-primary\" type=\"submit\" value=\"".__("Validate")."\" /> "
        . "<input class=\"btn\" type=\"reset\" value=\"".__("Delete")."\" /></div>";
echo "</div>";

echo "</form>";