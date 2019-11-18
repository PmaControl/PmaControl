<?php

use Glial\Html\Form\Form;
?>

<form action="" method="post">
    <div class="well">

        <div class="row">
            <h3><?= __("General's infos") ?></h3>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Cleaner's name") ?></div>
            <div class="col-md-4"><?= Form::input("cleaner_main", "libelle", array("class" => "form-control", "placeholder" => "Name of the cleaner you want to add")) ?></div>
            <div class="col-md-4"></div>
        </div>


        <div class="row">
            <div class="col-md-4"><?= __("Server") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "id_mysql_server", $data['server'], "", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Database") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "id_mysql_database", array(), "", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>


        <?php
        // select count(1) FROM PRODUCTION.PROD_COMMANDES WHERE (ETAT = 'PO' OR ETAT = 'QO') and DATE_PASSAGE <= DATE_ADD(now(), INTERVAL - 14 DAY)
        //$_GET['cleaner_main']['query'] = "(ETAT = 'PO' OR ETAT = 'QO') and DATE_PASSAGE <= DATE_ADD(now(), INTERVAL - 14 DAY) LIMIT 100";
        ?>

        <div class="row">
            <div class="col-md-4"><?= __("Main table") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "main_table", array(), "", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Query") ?></div>
            <div class="col-md-7"><?= Form::input("cleaner_main", "query", array("class" => "form-control", "placeholder" => "(ETAT = 'PO' OR ETAT = 'QO') and DATE_PASSAGE <= DATE_ADD(now(), INTERVAL - 14 DAY) LIMIT 100")) ?></div>
            <div class="col-md-1"></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?= __("Wait time between run") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "wait_time_in_sec", $data['wait_time'], "10", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Database use for cleaning") ?></div>
            <div class="col-md-4"><?= Form::input("cleaner_main", "cleaner_db", array("class" => "form-control", "placeholder" => "This database will store tmp tables used for cleaning, take care with replication.")) ?></div>
            <div class="col-md-4"></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?= __("Prefix for tables used for clean") ?></div>
            <div class="col-md-4"><?= Form::input("cleaner_main", "prefix", array("class" => "form-control", "placeholder" => "The prefix is the solution if you want store tmp table in same database.")) ?></div>
            <div class="col-md-4"></div>
        </div>

    </div>


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

                    debug($data['cn_selected']);
                    Form::$ajax = false;
                    ?>


                    <div class="col-md-11">
                        <div class="row">


                            <div class="col-md-2"><?= Form::select("cleaner_foreign_key", "constraint_schema", $data, "", array("class" => "form-control schema constraint"), 1) ?></div>
                            <div class="col-md-2"><?= Form::select("cleaner_foreign_key", "constraint_table", $data, "", array("class" => "form-control tables constraint"), 1) ?></div>
                            <div class="col-md-2"><?= Form::select("cleaner_foreign_key", "constraint_column", $data, "", array("class" => "form-control column constraint"), 1) ?></div>
                            <div class="col-md-2"><?= Form::select("cleaner_foreign_key", "referenced_schema", $data, "", array("class" => "form-control schema referenced"), 1) ?></div>
                            <div class="col-md-2"><?= Form::select("cleaner_foreign_key", "referenced_table", $data, "", array("class" => "form-control tables referenced"), 1) ?></div>
                            <div class="col-md-2"><?= Form::select("cleaner_foreign_key", "referenced_column", $data, "", array("class" => "form-control column referenced"), 1) ?></div>

                        </div>
                    </div>
                    <div class="col-md-1"><button type="reset" class="btn btn-default delete-row" disabled="disabled"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __('Delete') ?></button></div>

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


    <div class="well">
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> <?= __('Save') ?></button>
                <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __('Reset') ?></button>
            </div>
        </div>
    </div>


</form>
