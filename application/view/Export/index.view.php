<?php

use Glial\Html\Form\Form;
?>

<div class="row">
    <div class="col-md-6">
        <form class="form1" action="<?= LINK ?>export/export_conf" method="post">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Export configuration') ?></h3>
                </div>
                <div class="well">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="checkbox checbox-switch switch-success">
                                    <label>
                                        <?php
                                        //var_dump($_GET['ldap']['check']);

                                        /*
                                          if ($_GET['ldap']['check'] === true || $_GET['ldap']['check'] === "on") {
                                          $params['checked'] = "checked";
                                          } */
                                        ?>
                                        <?= Form::input("export_all", "all", array("class" => "form-control", "type" => "checkbox")) ?>
                                        <span></span>
                                        <?= __("All") ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        foreach ($data['options'] as $options):
                            ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox checbox-switch switch-success">
                                        <label>
                                            <?php
                                            //var_dump($_GET['ldap']['check']);

                                            $params = array("class" => "form-control", "type" => "checkbox");
                                            /*
                                              if ($_GET['ldap']['check'] === true || $_GET['ldap']['check'] === "on") {
                                              $params['checked'] = "checked";
                                              } */
                                            ?>
                                            <?= Form::input("export_option", $options['id'], $params) ?>
                                            <span></span>
                                            <?= $options['libelle'] ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endforeach;
                        ?>
                    </div>


                    <div class="row">
                        <div class="col-md-6">File name
                            <?=
                            Form::input("export", "name_file", array("class" => "form-control", "placeholder" => "Name of the file for the export"))
                            ?>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">Password
                            <?=
                            Form::input("export", "password", array("class" => "form-control", "type" => "password", "placeholder" => "Password used to crypt the export file"))
                            ?>
                        </div>

                        <div class="col-md-6">Repeat
                            <?= Form::input("export", "password2", array("class" => "form-control", "type" => "password", "placeholder" => "Repeat again the password")) ?>
                        </div>
                    </div>

                    <br />
                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-export"></span> Export</button>


                </div>

            </div>
        </form>
    </div>



    <div class="col-md-6">

        <form class="form2" action="<?= LINK ?>export/import_conf" enctype="multipart/form-data" method="post">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Import configuration') ?></h3>
                </div>

                <div class="well">


                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="checkbox checbox-switch switch-success">
                                    <label>
                                        <?= Form::input("export_all", "all2", array("class" => "form-control", "type" => "checkbox")) ?>
                                        <span></span>
                                        <?= __("All") ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php
                        foreach ($data['options'] as $options):
                            ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox checbox-switch switch-success">
                                        <label>
                                            <?php
                                            //var_dump($_GET['ldap']['check']);

                                            $params = array("class" => "form-control", "type" => "checkbox");
                                            /*
                                              if ($_GET['ldap']['check'] === true || $_GET['ldap']['check'] === "on") {
                                              $params['checked'] = "checked";
                                              } */
                                            ?>
                                            <?= Form::input("export_option", $options['id'], $params) ?>
                                            <span></span>
                                            <?= $options['libelle'] ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endforeach;
                        ?>
                    </div>

                    <div class="row">&nbsp;
                    </div>

                    <div class="row">
                        <div class="col-md-6">File to import
                            <?= Form::input("export", "file", array("type" => "file")) ?>
                        </div>

                        <div class="col-md-6">Password
                            <?=
                            Form::input("export", "password", array("class" => "form-control", "type" => "password", "placeholder" => "Password to decrypt the export file"))
                            ?>
                        </div>


                    </div>

                    <br />
                    <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-import"></span> Import</button> (Be carrefull the identical value will be overwriten)


                </div>

            </div>
        </form>
    </div>

</div>
