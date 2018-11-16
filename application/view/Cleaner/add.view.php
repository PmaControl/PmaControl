<?php

use Glial\Html\Form\Form;
?>


<form action="" method="post">
    <div class="well">
        <div class="row">
            <div class="col-md-12">
                <h3><?= __("General's infos") ?></h3>
            </div>
        </div>

        <div class="row">


            <div class="col-md-4"><?= __("Cleaner's name") ?></div>
            <div class="col-md-4">

                <?= Form::input("cleaner_main", "id", array("type" => "hidden")) ?>
                <?= Form::input("cleaner_main", "libelle", array("class" => "form-control", "placeholder" => "Name of the cleaner you want to add")) ?>
            </div>
            <div class="col-md-4"></div>
        </div>


        <div class="row">
            <div class="col-md-4"><?= __("Server") ?></div>
            <div class="col-md-4">
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("cleaner_main", "id_mysql_server", array("data-width" => "100%"))); ?>

            </div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Database") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "database", $data['databases'], "", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>


        <?php
        // select count(1) FROM PRODUCTION.PROD_COMMANDES WHERE (ETAT = 'PO' OR ETAT = 'QO') and DATE_PASSAGE <= DATE_ADD(now(), INTERVAL - 14 DAY)
        //$_GET['cleaner_main']['query'] = "(ETAT = 'PO' OR ETAT = 'QO') and DATE_PASSAGE <= DATE_ADD(now(), INTERVAL - 14 DAY) LIMIT 100";
        ?>

        <div class="row">
            <div class="col-md-4"><?= __("Main table") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "main_table", $data['table'], "", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Query") ?></div>
            <div class="col-md-7"><?=
                Form::input("cleaner_main", "query", array("class" => "form-control", "placeholder" => "(ETAT = 'PO' OR ETAT = 'QO') and DATE_PASSAGE <= DATE_ADD(now(), INTERVAL - 14 DAY)"))
                ?></div>
            <div class="col-md-1"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Number of line deleted in one time") ?></div>
            <div class="col-md-4"><?=
                Form::input("cleaner_main", "limit", array("class" => "form-control", "placeholder" => "1000"))
                ?></div>
            <div class="col-md-4"></div>
        </div>


        <div class="row">
            <div class="col-md-4"><?= __("Wait time between run") ?></div>
            <div class="col-md-4"><?= Form::select("cleaner_main", "wait_time_in_sec", $data['wait_time'], "10", array("class" => "form-control")) ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Database use for cleaning") ?></div>
            <div class="col-md-4"><?=
                Form::input("cleaner_main", "cleaner_db", array("class" => "form-control", "placeholder" => "This database will store tmp tables used for cleaning, take care with replication."))
                ?></div>
            <div class="col-md-4"></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?= __("Prefix for tables used for clean") ?></div>
            <div class="col-md-4"><?=
                Form::input("cleaner_main", "prefix", array("class" => "form-control", "placeholder" => "The prefix is the solution if you want store tmp table in same database."))
                ?></div>
            <div class="col-md-4"></div>
        </div>

    </div>



    <div class="well">

        <div class="row">
            <h3><?= __("Archiving") ?></h3>
        </div>

        <div class="well" style="border-left-color: #5cb85c;   border-left-width: 5px;">
            <p><b><?= __("Informations") ?> :</b></p>
            <ul>
                <li><?= __("If the following select is focus on a storage area, the cleaner will automatically archive the rows deleted") ?></li>
                <li><?= __("If there is no storage area, please add one there :") ?> <a href="<?= LINK ?>StorageArea/index/add"><?= __("Add a storage area") ?></a></li>

            </ul>

        </div>

        <div class="row">

            <div class="col-md-4"><?= __("Storage Area") ?></div>
            <div class="col-md-4">
                <?= Form::select("cleaner_main", "id_backup_storage_area", $data['backup_storage_area'], "", array("class" => "form-control")) ?>

            </div>
            <div class="col-md-4"></div>
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
