<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>

<table class="table table-condensed table-bordered table-striped">
        
        <tr>
        <th><?= __('ID') ?> </th>
        <th><?= __('key') ?> </th>
        <th><?= __('data') ?> </th>

    </tr>

    <?php
    
    $i = 0;
    foreach ($data['arr'] as $key => $import) {
        
        $i++;
        
        
        echo '<tr>';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $key . '</td>';
        echo '<td>' . debug($import) . '</td>';
       
        echo '</tr>';
    }
    ?>
        
</table>