<?php

use Glial\Html\Form\Form;
?>

<form action="" method="post">



    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add a backup') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-12">
                    <b>Name</b><br><br>
                </div>

                <div class="col-md-4">
                    <?= __("Backup name") ?>
                    <?=
                    Form::input("backup_main", "name", array("class" => "form-control", "placeholder" => __("Type a name for the backup")))
                    ?>
                </div>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <br><br><b>Server and database to backup</b><br><br>
                </div>


                <div class="col-md-4">
                    <?= __("Server") ?>
                    <?php
                    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable",
                        array("backup_main", "id_mysql_server",
                        array("data-live-search" => "true", "data-width" => "100%", "data-link" => "db", "data-target" => "backup_main-database")));
                    ?>
                </div>

                <div class="col-md-4">
                    <?= __("Database") ?>
                    <?php
                    $data['database'] = array();

                    \Glial\Synapse\FactoryController::addNode("Common", "getDatabaseByServer",
                        array("backup_main", "database", "1", array("data-width" => "100%", "multiple" => "multiple", "data-actions-box" => "true")));
                    ?>
                </div>
            </div>

            <div class="row">

                <div class="col-md-4">
                    <?= __("Backup directly on MySQL server") ?>

                </div>

                <div class="col-md-6">
                    <?= __("Local path") ?>
                    <?php
                    echo Form::input("Backup_main", "local_path", array("class"=>"form-control", "placeholder"=> __("It's the place where will be stocked locally the backup, before to send it to the storage area (example : /data/backup)")));
                    ?>
                </div>
            </div>

            <div class="row"><br><br></div>



            <div class="row">


                <div class="col-md-12">
                    <b>Parameters</b><br><br>
                </div>

                <div class="col-md-4">
                    <?= __("Storage area") ?>
                    <?=
                    Form::select("backup_main", "id_backup_storage_area", $data['storage_area'], "", array("class" => "selectpicker", "data-width" => "100%"))
                    //Form::input("mysql_server", "ip", array("class" => "form-control", "placeholder" => "IP of mysql server"))
                    ?>
                </div>
                <div class="col-md-4"><?= __("Tools") ?>
                    <?=
                    Form::select("backup_main", "id_backup_type", $data['type_backup'], "", array("class" => "selectpicker", "data-width" => "100%"))
                    ?></div>
            </div>







            <div class="row">

                <div class="col-md-12">
                    <br><br><b><?= __('Schedule') ?></b><br><br>
                </div>


                <div class="col-md-2"><?= __("Minute") ?>
                    <?= Form::input("crontab", "minute", array("class" => "form-control", "placeholder" => "minute (0 - 59)"))
                    ?></div>
                <div class="col-md-2"><?= __("Hour") ?>
                    <?= Form::input("crontab", "hour", array("class" => "form-control", "placeholder" => "hour (0 - 23)"))
                    ?></div>
                <div class="col-md-2"><?= __("Day of month") ?>
                    <?= Form::input("crontab", "day_of_month", array("class" => "form-control", "placeholder" => "day of month (1 - 31)"))
                    ?></div>
                <div class="col-md-2"><?= __("Month") ?>
<?= Form::input("crontab", "month", array("class" => "form-control", "placeholder" => "month (1 - 12)"))
?></div>

                <div class="col-md-2"><?= __("Day of week") ?>
<?= Form::input("crontab", "day_of_week", array("class" => "form-control", "placeholder" => "day of week (0 - 6) (Sunday to Saturday)"))
?></div>

            </div>





            <div class="row">
                <br >
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> Save</button>
                    <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> Reset</button>
                </div>
            </div>

            <br /><br />
            Documentation there : https://en.wikipedia.org/wiki/Cron
        </div>
    </div>


</form>



