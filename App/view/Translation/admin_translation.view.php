<?php

use \glial\I18n\I18n;

echo '<div id="translation">';

if (!empty($data['translate_auto'])) {
    foreach ($data['translate_auto'] as $key => $value) {
        if (empty($key)) { //TODO to remove when system will be ok
            continue;
        }

        $tmp3452453[] = '<img src="'.IMG.'language/'.$key.'.gif" width="18" height="12" border="0"> '.I18n::$languages[$key].' ('.$value.')';
    }

    echo __("Translations that require validation")." : ";
    echo implode(", ", $tmp3452453);
    echo '.<br /><br />';
}

$type = array(__("All"), __("Translation made by Google"), __("Translation made by users"), __("Translation not done yet"));

echo "<div>";

$i = 0;
foreach ($type as $tmp3) {
    $i++;
    if ($data['type'] == $i) {
        $class = "primary";
    } else {
        $class = "default";
    }

    echo ' <a class="btn btn-'.$class.'" href="'.LINK.'translation/admin_translation/type:'.$i.'/alpha:'.$data['alpha'].'/from:'.$data['from'].'/to:'.$data['to'].'">'.$tmp3.'</a>';
}

/*
  echo '<a href="" class="button btGreyLite overlayW btalpha">Translation made by Google</a> ';
  echo '<a href="" class="button btGreyLite overlayW btalpha">Translation made by users</a> ';
  echo '<a href="" class="button btGreyLite overlayW btalpha">Translation not done yet</a> ';
 */



echo "</div>";
echo "<br />";

$var = "#ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$nb  = strlen($var);

echo "<div>";

for ($i = 0; $i < $nb; $i++) {

    if ($data['alpha'] == $var{$i} || ($data['alpha'] == "1" && $i == 0)) {
        $class = "primary";
    } else {
        $class = "default";
    }

    $tmp = $var{$i};

    if ($tmp == "#") {
        $tmp = 1;
    }
    echo ' <a class="btn btn-'.$class.'" href="'.LINK.'translation/admin_translation/type:'.$data['type'].'/alpha:'.$tmp.'/from:'.$data['from'].'/to:'.$data['to'].'">'.$var{$i}.'</a>';

//  btBlueTest
}


echo "</div>";

echo "<br />";

if (!empty($data['pagination']) && $data['count'][0]['cpt'] > TRANSLATION_ELEM_PER_PAGE) {
    echo $data['pagination'];

    echo "<br />";
}


echo '<form action="" method="post">';

echo "<table>";

echo '<tr><th class="tcs">'.__("Top").'</th><th>'.__("From").'</th>
<th>
<img class="flag '.$data['from'].'" src="'.IMG.'main/1x1.png" width="18" border="0" height="12">';
echo select("none", "id_from", $data['geolocalisation_country'], $data['from'], "textform lg translation");
echo '</th>


<th class="tce">
<img class="flag '.$data['to'].'" src="'.IMG.'main/1x1.png" width="18" border="0" height="12">';
echo select("none", "id_to", $data['geolocalisation_country'], $data['to'], "textform lg translation");
echo "</th>";
//echo "<th class=\"tce\">".__("Delete")."</th>";
echo "</tr>";

if (!empty($data['text'])) {

    $i = $data['i'];
    foreach ($data['text'] as $text) {
        echo "<tr>";

        echo "<td>#$i</td>";
        echo '<td><img title="'.$text['file_found']." at line ".$text['line_found'].'" id="flag" class="'.$text['source'].'" src="'.IMG.'main/1x1.png" width="18" border="0" height="12"></td>';
        echo "<td class=\"val4\"><textarea readonly=\"readonly\" class=\"translation val1\">".$text['atext']."</textarea></td>
		<td class=\"val3\"><textarea  class=\"translation val2\" name=\"id-".$text['bid']."\">".$text['btext']."</textarea></td>";
        //echo '<td><a href="'.LINK.'translation/admin_translation/delete:'.$data['default_lg2'].'-'.$text['bid'].'"><img src="'.IMG.'main/b_drop.png" width="16" border="0" height="16"></a></td>';
        echo "</tr>";
        $i++;
    }
}
echo "</table>";

echo '<input id="none-field-to-update" type="hidden" value="" name="field-to-update" readonly="readonly" />';

echo '<br /><input class="button btBlueTest overlayW btMedium" type="submit" value="'.__("Update").'" /> ';
echo '<a href="'.LINK.'translation/delete_tmp_files/" class="button btBlueTest overlayW bthref">'.__("Delete the temporary files").'</a> ';
echo '<a href="'.LINK.'translation/delete_table_cach/" class="button btBlueTest overlayW bthref">'.__("Delete the temporary tables").'</a> ';

echo '</form>';

echo '
<div class="well">'.__("Informations:").'<ul>
<li>'.__("After any update, you have to delete the temporary files to see the modifications on the website. These files will be regenerate on the next request.").'</li>
<li>'.__("Delete the temporary tables will remove all words and / or sentences who aren't used anymore on the webiste to optimize the database.").'</li>
<li>'.__("The source of the translation cannot be updated here ! You have to update it on the source code or from the database.", 'fr').'</li>


</ul>


</div>';

echo '</div>';
