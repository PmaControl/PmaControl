<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Form\Form;

echo '<form enctype="multipart/form-data" action="" method="post">';




echo "GenCppFile : ";
echo Form::file('file_csv');


echo '<br />';
echo '<br />';

echo _('Date of entry into force:');
echo Form::input('crontab', 'date');

echo '<br />';
echo '<br />';
echo '<input type="submit" name="" class="button btBlueTest overlayW btMedium" value="Execute" />';
echo '</form>';