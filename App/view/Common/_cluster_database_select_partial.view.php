<?php

use Glial\Html\Form\Form;

echo Form::select(
    "mysql_cluster",
    "database",
    $data['databases'],
    "",
    array("data-live-search" => "true", "class" => "selectpicker form-control")
);
