<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

if (!empty($data['select_bench'])) {
    echo '<div class="well">';

    echo '<form style="display:inline" action="" method="post">';
    echo Form::select("benchmark_main", "id", $data['select_bench'], "",
        array("class" => "selectpicker", "data-live-search" => "true", "multiple" => "multiple", "data-actions-box" => "true", "style" => "width:500px"));


    echo '<input type="hidden" name="benchmark" value="1" />';

    echo ' <button type="submit" class="btn btn-primary">'.__("Display").'</button>';
    echo '</form>';
    echo '</div>';
} else {
    ?>
    <div class="well">
        <h1>Information</h1>
        <p style="font-size: 15px">You need to make a benchmark before to view graphs !</p>
        <p><a class="btn btn-primary btn-lg" href="<?=LINK ?>Benchmark/index/bench" role="button">Make a new benchmark</a></p>
    </div>

    <?php
}

if (!empty($data['select_bench'])) {
    ?>
    <div class="row">
        <div class="col-md-6">
            <canvas id="graph1" class="graph" width="550" heigth="400"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="graph2" class="graph" width="550" heigth="400"></canvas>
        </div>
    </div>
    <div id="charts">
        <div class="row">
            <div class="col-md-6">
                <canvas id="graph4" class="graph" width="550" heigth="400"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="graph3" class="graph" width="550" heigth="400"></canvas>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <canvas id="graph5" class="graph" width="550" heigth="400"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="graph6" class="graph" width="550" heigth="400"></canvas>
            </div>
        </div>
    </div>
    <?php
}
?>