
    <div class="well">

        <div class="row">
            <h3><?= __("Define virtual foreign keys") ?></h3>
        </div>

        <div class="row">
            <div class="col-md-11">
                <div class="row">
                    <div class="col-md-6"> <b>Constraint table</b></div>
                    <div class="col-md-6"> <b>Referenced table</b></div>
                </div>
            </div>
            <div class="col-md-1"></div>

        </div>

        <div class="row">
            <div class="col-md-11">

                <div class="row">
                    <div class="col-md-2">Schema</div>
                    <div class="col-md-2">Table</div>
                    <div class="col-md-2">Column</div>
                    <div class="col-md-2">Schema</div>
                    <div class="col-md-2">Table</div>
                    <div class="col-md-2">Column</div>
                </div>

            </div>
            <div class="col-md-1">
                <?= __('Tools'); ?>
            </div>

        </div>

        <div class="table" style="margin-bottom: -15px">
            <div id="cleaner-line-1" class="cleaner-line" style="margin-bottom: 5px">
                <div class="row">

                    <?php
                    $data = array();

                    Form::$ajax   = false;
                    Form::$indice = true;
                    ?>


                    <div class="col-md-11">
                        <div class="row">


                            <div class="col-md-2">
                                <?= Form::select("cleaner_foreign_key", "constraint_schema", $data, "", array("class" => "form-control schema constraint"), 1) ?>
                            </div>
                            <div class="col-md-2">
                                <?= Form::select("cleaner_foreign_key", "constraint_table", $data, "", array("class" => "form-control tables constraint"), 1) ?>
                            </div>
                            <div class="col-md-2">
                                <?= Form::select("cleaner_foreign_key", "constraint_column", $data, "", array("class" => "form-control column constraint"), 1) ?>
                            </div>
                            <div class="col-md-2">
                                <?= Form::select("cleaner_foreign_key", "referenced_schema", $data, "", array("class" => "form-control schema referenced"), 1) ?>
                            </div>
                            <div class="col-md-2">
                                <?= Form::select("cleaner_foreign_key", "referenced_table", $data, "", array("class" => "form-control tables referenced"), 1) ?>
                            </div>
                            <div class="col-md-2">
                                <?= Form::select("cleaner_foreign_key", "referenced_column", $data, "", array("class" => "form-control column referenced"), 1) ?>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-1"><button type="reset" class="btn btn-default delete-row" disabled="disabled"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __('Delete') ?></button></div>

                    <?php
                    Form::$indice = true;
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <br />
                <a href='<?= LINK ?>Cleaner/add/' id="add" class="btn btn-primary"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a row</a>
            </div>
        </div>
    </div>
