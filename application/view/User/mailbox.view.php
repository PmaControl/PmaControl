<?php
$this->data['options'] = array("all_mails", "inbox", "sent_mail", "trash", "compose");
($data['request'] === "compose") ? $class1 = 'btBlueTest' : $class1 = 'btGreyLite';
($data['request'] === "all_mails") ? $class2 = 'btBlueTest' : $class2 = 'btGreyLite';
($data['request'] === "inbox") ? $class3 = 'btBlueTest' : $class3 = 'btGreyLite';
($data['request'] === "sent_mail") ? $class4 = 'btBlueTest' : $class4 = 'btGreyLite';
($data['request'] === "trash") ? $class5 = 'btBlueTest' : $class5 = 'btGreyLite';
echo ' <a href="' . LINK . 'user/mailbox/compose/" class="right button ' . $class1 . ' overlayW btMedium"><img src="' . IMG . 'mailbox/compose.png" height="16" width="16"> ' . __("Compose") . ' </a>';
echo '<a href="' . LINK . 'user/mailbox/all_mails/" class="button ' . $class2 . ' overlayW btMedium"><img src="' . IMG . 'mailbox/mails-stack.gif" height="16" width="16"> ' . __("All mails") . '</a></button> ';
echo '<a href="' . LINK . 'user/mailbox/inbox/" class="button ' . $class3 . ' overlayW btMedium"> <img src="' . IMG . 'mailbox/emailButton.png" height="16" width="16"> ' . __("Inbox") . '</a></button> ';
echo '<a href="' . LINK . 'user/mailbox/sent_mail/" class="button ' . $class4 . ' overlayW btMedium"><img src="' . IMG . 'mailbox/sent_items.gif" height="16" width="16"> ' . __("Sent Mail") . '</a></button> ';
echo '<a href="' . LINK . 'user/mailbox/trash/" class="button ' . $class5 . ' overlayW btMedium"><img src="' . IMG . 'mailbox/trash.gif" height="16" width="16"> ' . __("Trash") . '</a></button> ';
echo '<div>';
switch ($data['request'])
{
	case "compose":
		echo '<div class="post">';
		echo '<form action="" method="post">';
		echo "<h3>" . __("Compose") . "</h3>";
		if (empty($data['send_to'][1]) || empty($data['send_to'][1]))
		{
			echo '<label class="required"><strong>' . __("To") . ' <span>(' . __("Required") . ')</span></strong><br>';
			echo autocomplete("mailbox_main", "id_user_main__to", "textform");
			echo '<br></label>';
		}
		else
		{
			echo '<label class="required">' . __("To") . '<br>';
			echo '<input class="text textform" type="text" name="mailbox_main[id_user_main__to-auto]" value="'.$data['send_to'][2].'" readonly="readonly" />';
			echo '<input class="text textform" type="hidden" name="mailbox_main[id_user_main__to]" value="'.$data['send_to'][1].'" readonly="readonly" />';
			echo '<br></label>';
		}
		
		echo '<label class="required"><strong>' . __("Subject") . ' <span>(' . __("Required") . ')</span></strong><br>';
		echo input("mailbox_main", "title", "textform");
		echo '<br></label>';
		echo '<label class="required"><strong>' . __("Message") . ' <span>(' . __("Required") . ')</span></strong><br>';
		echo textarea("mailbox_main", "text", "textform");
		echo '</label><br />';
		echo '<input class="button btBlueTest overlayW btMedium" type="submit" value="' . __("Send") . '" /> ';
		echo '</form>';
		echo '</div>';
		break;
	case "msg":
		echo "<div class=\"tab\">";
		echo "<h3>" . __("Message") . "</h3>";
		echo "<ul>";
		echo '<li class="couleur2"><span>' . __('From') . " </span>";
		echo '<img src="' . IMG . 'country/type1/' . mb_strtolower($data['mail'][0]['from_iso'], 'utf-8') . '.gif" width="18" height="12" alt="" /> ';
		echo '<a href="' . LINK . 'user/profil/' . $data['mail'][0]['from_id'] . '">' . $data['mail'][0]['from_firstname'] . ' ' . $data['mail'][0]['from_name'] . '</a>';
		echo "</li>";
		echo '<li class="couleur1"><span>' . __('To') . ' </span>';
		echo '<img src="' . IMG . 'country/type1/' . mb_strtolower($data['mail'][0]['to_iso'], 'utf-8') . '.gif" width="18" height="12" alt="" /> ';
		echo '<a href="' . LINK . 'user/profil/' . $data['mail'][0]['to_id'] . '">' . $data['mail'][0]['to_firstname'] . ' ' . $data['mail'][0]['to_name'] . '</a>';
		echo '</span></li>';
		echo '<li class="couleur2"><span>' . __('Posted the') . " </span>" . $data['mail'][0]['date'] . "</li>";
		echo '<li class="couleur1"><span>' . __('Subjet') . " </span>" . $data['mail'][0]['title'] . "</li>";
		echo '<li class="couleur2">' . nl2br($data['mail'][0]['msg']) . "</li>";
		echo '</ul>';
		echo '<br />';
		echo '<a href="'.LINK.'user/mailbox/compose/'.$data['mail'][0]['from_id'].'/'.$data['mail'][0]['from_firstname'] . ' ' . $data['mail'][0]['from_name'].'/" class="button btBlueTest overlayW btMedium"><img src="' . IMG . 'mailbox/reply.png" height="16" width="16" /> ' . __("Reply") . '</a> ';
		
		
		echo "</div>";
		
		
		
		
		break;
	default:
		echo '<div class="forum">';
		echo '<table width="100%">';
		echo "<tr>";
		echo '<th class="tcl"><input type="checkbox" name="all" /></th>';
		echo "<th>" . __('From') . "</th>";
		echo "<th>" . __('To') . "</th>";
		echo "<th>" . __('Subject') . "</th>";
		echo '<th>' . __('Date') . '</th>';
		echo '<th class="tcr">' . __('Opened') . '</th>';
		echo "</tr>";
		foreach ($data['mail'] as $value)
		{
			if ($value['id_mailbox_etat'] == 1)
			{
				echo '<tr class="couleur1">';
			}
			else
			{
				echo '<tr class="couleur2">';
			}
			echo '<td>';
			echo '<input type="checkbox" name="all" />';
			echo '</td>';
			echo '<td>';
			echo '<img src="' . IMG . 'country/type1/' . mb_strtolower($value['from_iso'], 'utf-8') . '.gif" width="18" height="12" alt="" /> ';
			echo '<a href="' . LINK . 'user/profil/' . $value['from_id'] . '">' . $value['from_firstname'] . ' ' . $value['from_name'] . '</a></div>';
			echo '</td>';
			echo '<td>';
			echo '<img src="' . IMG . 'country/type1/' . mb_strtolower($value['to_iso'], 'utf-8') . '.gif" width="18" height="12" alt="" /> ';
			echo '<a href="' . LINK . 'user/profil/' . $value['to_id'] . '">' . $value['to_firstname'] . ' ' . $value['to_name'] . '</a></div>';
			echo '</td>';
			echo '<td><a href="' . LINK . 'user/mailbox/msg/' . $value['id'] . '">' . __('Message') . ' : ' . $value['title'] . '</a></td>';
			echo '<td>' . $value['date'] . '</td>';
			echo '<td>';
			if ($value['id_mailbox_etat'] == 1)
			{
				echo '<img src="' . IMG . 'mailbox/open.png" height="16" width="16" />';
			}
			else
			{
				echo '<img src="' . IMG . 'mailbox/close.png" height="16" width="16" />';
			}
			echo '</td>';
			echo "</tr>";
		}
		echo "</table>";
		echo '</div>';
		break;
}
echo '</div>';