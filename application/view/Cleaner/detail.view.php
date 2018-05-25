<?php



echo '<div class="row">';



echo '<div class="col-md-6">';
echo '<table class="table">';
echo '<tr>';
echo '<th>' . __('Top') . '</th>';
echo '<th>' . __('Table') . '</th>';
echo '<th>' . __('Rows') . '</th>';

echo '</tr>';
$i = 0;
foreach ($data['detail'] as $detail) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td>' . $detail['table'] . '</td>';
    echo '<td>' . $detail['row'] . '</td>';

    echo '</tr>';


    //var_dump($details);
}
echo '</table>';
echo '</div>';

echo '<div class="col-md-6">';
echo '<table class="table">';
echo '<tr>';
echo '<th>' . __('Top') . '</th>';
echo '<th>' . __('Table') . '</th>';
echo '<th>' . __('Rows') . '</th>';

echo '</tr>';
$i = 0;
foreach ($data['avg'] as $detail) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td>' . $detail['table'] . '</td>';
    echo '<td>' . round($detail['row'],2) . '</td>';
    echo '</tr>';

    //var_dump($details);
}
echo '</table>';
echo '</div>';
echo '</div>';