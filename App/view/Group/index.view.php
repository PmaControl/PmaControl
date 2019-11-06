<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<h3>Roles</h3>';
echo '<table class="table table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>' . __("Alias") . '</th>';
echo '<th>' . __("Roles") . '</th>';
echo '<th>' . __("Allow") . '</th>';
echo '<th>' . __("Deny") . '</th>';
echo '<th>' . __("Settings") . '</th>';
echo '</tr>';

foreach ($data['alias'] as $id_group => $group) {
    echo '<tr>';

    echo '<td>' . $id_group . '</td>';
    echo '<td>' . $group . '</td>';
    echo '<td>';

    if (!empty($data['allow'][$group])) {
        echo implode('<br>', $data['allow'][$group]);
    }
    echo '</td>';
    echo '<td>';

    if (!empty($data['deny'][$group])) {
        echo implode('<br>', $data['deny'][$group]);
    }
    echo '</td>';


    echo '<td>';
    echo '<button type="button" class="btn btn-primary"><span class="glyphicon glyphicon glyphicon-edit"></span> ' . __("Edit") . '</button>';

    if ($id_group > 4) {
        echo ' <a href="' . LINK . '/group/delete/' . $id_group . '"role="button" class="btn btn-danger"><span class="glyphicon glyphicon glyphicon-remove"></span> ' . __("Delete") . '</a>';
    }
    echo '</td>';
    echo '</tr>';
}
echo '</table>';

echo ' <button type="button" class="btn btn-primary"><span class="glyphicon glyphicon glyphicon-plus"></span> ' . __("Add a group") . '</button>';
echo '<h3>Inherit</h3>';

debug($data['export']);
