<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>

<form action="<?= LINK ?>database/data" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Compare database') ?></h3>
        </div>
        <div class="well">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4"> <b><?= __("Orginal") ?></b></div>
                <div class="col-md-4"> <b><?= __("Compare") ?></b></div>
            </div>
            <div class="row">
                <div class="col-md-4"><?= __("Server") ?></div>
                <div class="col-md-4"><?php
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("compare_main", "id_mysql_server__original", array("data-width" => "100%")));
?></div>
                <div class="col-md-4"><?php
                    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("compare_main", "id_mysql_server__compare", array("data-width" => "100%")));
?></div>
            </div>
            <div class="row" style="height:5px">
            </div>
            <div class="row">
                <div class="col-md-4"><?= __("Database") ?></div>
                <div class="col-md-4">
                    <?php
                    echo Form::select("compare_main", "database__original", $data['listdb1'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%"))
                    ?>
                </div>
                <div class="col-md-4">
                    <?php
                    echo Form::select("compare_main", "database__compare", $data['listdb2'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%"))
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-transfer" style="font-size:12px"></span> <?= __("Compare") ?></button>
                    <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __("Reset") ?></button>
                </div>
            </div>
        </div>
    </div>
</form>


<?php
if (!empty($data['resultat'])) {
    ?>

    <!-- Nav tabs -->
    <div class="panel with-nav-tabs panel-primary">
        <div class="panel-heading">
            <ul class="nav nav-tabs" role="tablist">
    <?php
    $i = 0;

    foreach ($data['resultat'] as $typeobject => $elems) {
        $i++;
        $active = '';
        if ($i === 1) {
            $active = "active";
        }

        echo '<li role="presentation" class="'.$active.'"><a href="#'.strtolower($typeobject).'" aria-controls="'.strtolower($typeobject).'" role="tab" data-toggle="tab">'.ucwords(strtolower($typeobject)).' ('.count($elems).')</a></li>'."\n";
    }
    ?>
            </ul>

        </div>

        <!-- Tab panes -->
        <div class="panel-body">
            <div class="tab-content">

    <?php
    $active = "in active";

    foreach ($data['resultat'] as $typeobject => $elems) {
        echo '<div role="tabpanel" class="tab-pane fade '.$active.'" id="'.strtolower($typeobject).'">';
        foreach ($elems as $elem => $diffs) {

            //$data[$type_object][$elem][1]
            // echo '<div class="title">'.$elem.'</div>'."\n";
            echo '<div class="panel panel-primary">
<div class="panel-heading">

<h3 class="panel-title">'.$elem.'</h3>
</div>';

            echo '</div>';
        }
    }
} // ✓]