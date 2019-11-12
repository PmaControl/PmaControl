<?php
echo "<div id=\"menu_admin_crop\">";
echo "<div class=\"title_box\"><a href=\"\">" . __('Photo') . "</a></div>";
echo "<div>";
echo '<img src="' . IMG . 'user/acspike_male_user_icon.png" alt="" />';
echo "</div>";
echo "<div class=\"title_box\"><a href=\"\">" . __('Shoutbox') . "</a></div>";
echo "<div>";
$i = 1;
foreach ($data['shoutbox'] as $line)
{
	($i % 2 == 0) ? $class = "couleur1" : $class = "couleur2";
	echo '<div class="line ' . $class . '">';
	echo '<div><span class="right">' . $line['date'];
	/*
	  if ($data['id'] == $GLOBALS['_SITE']['IdUser'])
	  {
	  echo '<img src="'.IMG.'type_query/deleteGrey.gif" height="15" width="15" />';
	  } */
	echo '</span> ';
	echo '<img src="' . IMG . 'country/type1/' . mb_strtolower($line['iso'], 'utf-8') . '.gif" width="18" height="12" alt="" /> ';
	echo '<a href="' . LINK . 'user/profil/' . $line['id'] . '">' . $line['firstname'] . ' ' . $line['name'] . '</a></div>';
	echo $line['text'];
	echo "</div>";
	$i++;
}
echo '<form action="" method="post">';
echo input("shoutbox", "text", "textform shoutbox");
echo "<input class=\"button btBlueTest overlayW btMedium\" type=\"submit\" value=\"" . __("Send") . "\" />";
echo '</form>';
echo "</div>";
echo "<div class = \"title_box\"><a href=\"\">" . __('My friends') . "</a></div>";
echo "<div class = \"title_box\"><a href=\"\">" . __('Visitors') . "</a></div>";
echo "</div>";
//select author, count(1) from species_picture_in_wait group by author having count(1) > 1 order by count(1) desc
echo "<div id=\"profil\">";
echo "<div class=\"menu\">";
echo "<ul id=\"onglet\" class=\"menu_tab\" style=\"padding-left: 3px;\">
<li id=\"general\" class=\"selected\"><a href=\"\">" . __("Main") . "</a></li>
<li><a href=\"\">" . __("Actions") . "</a></li>
<li><a href=\"\">" . __("My gallery") . "</a></li>
<li><a href=\"\">" . __("My videos") . "</a></li>
<li><a href=\"\">" . __("My birds") . "</a></li>
<li><a href=\"\">" . __("Forums") . "</a></li>
<li><a href=\"\">" . __("Ads") . "</a></li>
</ul>";
echo "</div>";
if ($data['id'] != $GLOBALS['_SITE']['IdUser'])
{
	echo "<div class=\"tab\">";
	echo "<h3>" . __("Tools") . "</h3>";
	echo "<div>";
	//echo '<button class="button btBlueTest overlayW btMedium"><img src="' . IMG . 'action/add.gif" height="16" width="16" /> ' . __("Add to my friends") . '</button> ';
	echo '<a href="'.LINK.'user/mailbox/compose/'.$data['id'].'/'.$data['name'].'/" class="button btBlueTest overlayW btMedium"><img src="' . IMG . 'action/send_message.png" height="16" width="16" /> ' . __("Send a private message") . '</a> ';
	//echo '<button class="button btBlueTest overlayW btMedium"><img src="' . IMG . 'action/group_16x16.png" height="16" width="16" /> ' . __("Invite him to a group") . '</button> ';
	echo "</div>";
	echo "</div>";
}
echo "<div class=\"tab\">";
echo "<h3>" . __("Personal") . "</h3>";
echo "<ul>";
echo '<li class="couleur2"><span>' . __('Name') . " </span>" . $data['name'] . "</li>";
echo '<li class="couleur1"><span>' . __('Country') . ' </span><img src="' . IMG . 'country/type1/' . mb_strtolower($data['user']['iso'], 'utf-8') . '.gif" width="18" height="12" alt="" /> ' . __($data['user']['name_fr'], "fr") . "</span></li>";
echo '<li class="couleur2"><span>' . __('City') . " </span>" . $data['user']['libelle'] . "</li>";
echo '<li class="couleur1"><span>' . __('Gender') . " </span>" . __("Male") . "</li>";
echo '<li class="couleur2"><span>' . __('Last online') . " </span>" . $data['user']['date_last_connected'] . "</li>";
echo '<li class="couleur1"><span>' . __('Registered on') . " </span>" . $data['user']['date_created'] . "</li>";
echo '</ul>';
echo "</div>";
//echo "<div style=\"clear:both\"></div>";
echo "<div class=\"tab\">";
echo "<h3>" . __("Actions performed") . "</h3>";
echo "<ul>";
$i = 1;
foreach ($data['actions'] as $action)
{
	($i % 2 == 0) ? $class = "couleur1" : $class = "couleur2";
	echo '<li class="' . $class . '"><span>' . __($action['title']) . " </span>";
	echo ( empty($data['points'][$action['id']])) ? "0" : $data['points'][$action['id']];
	echo "</li>";
	$i++;
}
echo "</ul>";
echo "</div>";
echo "</div>";
echo "<div style=\"clear:both\"></div>";