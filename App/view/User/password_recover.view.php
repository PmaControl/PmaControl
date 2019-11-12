<?php

echo '<div style="width:1000px; margin-left:auto; margin-right:auto; padding:0" class="well">';




echo "<form action=\"\" method=\"post\" class=\"form-horizontal\" width=\"100%\">";


echo '<div style="padding:10px">';

echo "<h3 class=\"item\">".__("Set your new password")."</h3>";

echo "<table class=\"form\" width=\"100%\">";
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
echo "<td class=\"first\">(".__("repeat").") :</td>";
echo "<td>".password("user_main","password2","form-control")."</td>";
echo "</tr>";

echo "</table>";

echo '</div>';

echo "<div class=\"form-actions\" style=\"margin:0\">"
. "<input class=\"btn\" type=\"reset\" value=\"".__("Delete")."\" /> "
. "<input class=\"btn btn-primary\" type=\"submit\" value=\"".__("Update")."\" />"
        . "</div>";
echo "</div>";

echo "</form>";


echo '</div>';