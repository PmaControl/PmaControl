<?php
if (!empty($data)) {



    debug($data);

    foreach ($data['link'] as $key => $name) {
        echo "<div><h3>".__($key)."</h3>";

        echo "<ul>";
        foreach ($name as $val) {
            echo "<li><a href=\"".LINK.$val['url']."\"><img src=\"".IMG.$val['picture']."\" width=\"32\" height=\"32\" /><b>".$val['name'];


            if (!empty($val['count'])) echo " (".$val['count'].")";
            echo "</b><br />".$val['description'];


            echo "</a></li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    echo '<div style="clear:both"></div>';
}
echo "</div>";
?>