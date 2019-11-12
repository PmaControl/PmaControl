<?php


use \Glial\Html\Form\Form;

Form::setAjax(true);
echo Form::select("mysql_database", "id", $data['databases']);