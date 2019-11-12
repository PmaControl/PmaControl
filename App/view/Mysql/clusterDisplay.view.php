<?php

sort($data['servers']);

echo '<table class="table">';

echo "<tr>";
echo '<th>WSREP%</th>';


foreach ($data['servers'] as $server) {
    echo '<th>' . $server . '</td>';
}



echo "</tr>";

foreach ($data['WSREP'] as $libelle => $line) {

    echo "<tr>";

    echo '<td>' . $libelle . '</td>';



    $tmp = '';
    foreach ($data['servers'] as $server) {

        if (empty($line[$server])) {
            echo '<td></td>';
        } else {

            $var = substr($line[$server], 0, 30);

            if ($tmp != '' && $tmp != $line[$server]) {
                $var = "<b>" . $var . "</b>";
            }


            echo '<td title="' . $server . " : " . $line[$server] . '">' . $var . '</td>';

            $tmp = $line[$server];
        }
    }

    echo "</tr>";
}
echo '</table>';
