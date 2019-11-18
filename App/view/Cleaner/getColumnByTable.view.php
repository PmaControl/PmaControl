<?php


use \Glial\Html\Form\Form;

Form::setAjax(true);
echo Form::select("cleaner_foreign_key", "constraint_column", $data['column']);