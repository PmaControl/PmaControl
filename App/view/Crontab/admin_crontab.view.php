<?php
use Glial\Html\Form\Form;

$csrf = isset($data['__csrf']) && is_array($data['__csrf']) ? $data['__csrf'] : array();
unset($data['__csrf']);

$csrf_field = htmlspecialchars((string)($csrf['field'] ?? '_csrf_token'), ENT_QUOTES, 'UTF-8');
$add_csrf_token = htmlspecialchars((string)($csrf['add_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$delete_csrf_token = htmlspecialchars((string)($csrf['delete_token'] ?? ''), ENT_QUOTES, 'UTF-8');
$add_csrf_input = "<input type=\"hidden\" name=\"".$csrf_field."\" value=\"".$add_csrf_token."\" />";
$delete_csrf_input = "<input type=\"hidden\" name=\"".$csrf_field."\" value=\"".$delete_csrf_token."\" />";

echo "<div id=\"crontab\">";




echo "<table>";


echo "<tr>";
echo "<th>".__("Minutes")."</th>";
echo "<th>".__("Hours")."</th>";
echo "<th>".__("Day of month")."</th>";
echo "<th>".__("Month")."</th>";
echo "<th>".__("Day of week")."</th>";
echo "<th>".__("Command")."</th>";
echo "<th>".__("Action")."</th>";
echo "</tr>";


foreach($data as $key => $line)
{

	$elem = explode(" ", $line);

	echo "<form action=\"\" method=\"post\">";
	echo "<tr>";

	echo "<td>".$elem[0]."</td>";
	echo "<td>".$elem[1]."</td>";
	echo "<td>".$elem[2]."</td>";
	echo "<td>".$elem[3]."</td>";
	echo "<td>".$elem[4]."</td>";

	unset($elem[0]);
	unset($elem[1]);
	unset($elem[2]);
	unset($elem[3]);
	unset($elem[4]);
	$cmd = implode(" ", $elem);

	echo "<td>".$cmd."</td>";


	echo "<td>".$delete_csrf_input.hidden("crontab","delete",$key)."<input class=\"button btBlueTest overlayW btMedium\" type=\"submit\" value=\"".__("Delete")."\" /></td>";

	echo "</tr>";
	echo "</form>";

}


echo "<form action=\"\" method=\"post\">";
echo "<tr>";
echo "<td>".input("crontab","minute","crontab")."</td>";
echo "<td>".input("crontab","hour","crontab")."</td>";
echo "<td>".input("crontab","dayofmonth","crontab")."</td>";
echo "<td>".input("crontab","month","crontab")."</td>";
echo "<td>".input("crontab","dayofweek","crontab")."</td>";
echo "<td>".input("crontab","command","cmd")."</td>";
echo "<td>".$add_csrf_input."<input class=\"button btBlueTest overlayW btMedium\" type=\"submit\" value=\"".__("Add")."\" /></td>";
echo "<tr>";
echo "</form>";
echo "</table>";



echo "</div>";

?>
